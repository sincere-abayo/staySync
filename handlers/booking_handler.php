<?php
require_once '../config/database.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to make a booking'
    ]);
    exit;
}

switch($_POST['action'] ?? $_GET['action'] ?? '') {
    case 'book':
        $user_id = get_user_id();
        $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
        $check_in = filter_var($_POST['check_in'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $check_out = filter_var($_POST['check_out'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $adults = filter_var($_POST['adults'], FILTER_SANITIZE_NUMBER_INT);
        $kids = filter_var($_POST['kids'], FILTER_SANITIZE_NUMBER_INT);
        $requests = filter_var($_POST['requests'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Check room availability
        $availability = $conn->prepare("
            SELECT COUNT(*) as booked 
            FROM bookings 
            WHERE room_id = ? 
            AND booking_status IN ('pending', 'confirmed')
            AND (
                (check_in BETWEEN ? AND ?) 
                OR (check_out BETWEEN ? AND ?)
                OR (check_in <= ? AND check_out >= ?)
            )
        ");
        // check if check_out is less than check_in
        if ($check_out < $check_in) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Check-out date cannot be before check-in date'
            ]);
            exit;
            }
        
        $availability->bind_param("issssss", 
            $room_id, $check_in, $check_out, 
            $check_in, $check_out, $check_in, $check_out
        );
        
        $availability->execute();
        $result = $availability->get_result()->fetch_assoc();

        if ($result['booked'] > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Room is not available for selected dates'
            ]);
            exit;
        }

     // Create booking
$stmt = $conn->prepare("
INSERT INTO bookings (
    user_id, room_id, check_in, check_out, 
    booking_status, payment_status, adults, kids
) VALUES (?, ?, ?, ?, 'pending', 'pending', ?,?)
");

$stmt->bind_param("iissii", 
$user_id, $room_id, 
$check_in, $check_out,
$adults, $kids
);

        if ($stmt->execute()) {
            $booking_id = $stmt->insert_id;
            
            // Add special requests if any
            if ($requests) {
                $conn->query("
                    INSERT INTO booking_requests (booking_id, request_text) 
                    VALUES ($booking_id, '$requests')
                ");
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'booking_id' => $booking_id
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to create booking'
            ]);
        }
        break;

    case 'check_availability':
        $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
        $check_in = filter_var($_POST['check_in'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $check_out = filter_var($_POST['check_out'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
        $availability = $conn->prepare("
            SELECT COUNT(*) as booked 
            FROM bookings 
            WHERE room_id = ? 
            AND booking_status IN ('pending', 'confirmed')
            AND (
                (check_in BETWEEN ? AND ?) 
                OR (check_out BETWEEN ? AND ?)
                OR (check_in <= ? AND check_out >= ?)
            )
        ");
        
        $availability->bind_param("issssss", 
            $room_id, $check_in, $check_out, 
            $check_in, $check_out, $check_in, $check_out
        );
        
        $availability->execute();
        $result = $availability->get_result()->fetch_assoc();
    
        echo json_encode([
            'status' => 'success',
            'available' => ($result['booked'] == 0),
            'message' => ($result['booked'] == 0) ? 'Room is available' : 'Room is not available for selected dates'
        ]);
        break;
        
    case 'cancel':
        $booking_id = filter_var($_POST['booking_id'], FILTER_SANITIZE_NUMBER_INT);
        $user_id = get_user_id();
    
        // Verify booking belongs to user and is cancellable
        $verify = $conn->prepare("
            SELECT id FROM bookings 
            WHERE id = ? AND user_id = ? 
            AND booking_status IN ('pending', 'confirmed')
        ");
        $verify->bind_param("ii", $booking_id, $user_id);
        $verify->execute();
        
        if ($verify->get_result()->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid booking or cannot be cancelled'
            ]);
            exit;
        }
    
        // Update booking status
        $stmt = $conn->prepare("
            UPDATE bookings 
            SET booking_status = 'cancelled' 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $booking_id);
    
        echo json_encode([
            'status' => $stmt->execute() ? 'success' : 'error',
            'message' => $stmt->execute() ? 'Booking cancelled successfully' : 'Failed to cancel booking'
        ]);
        break;

    // process payment
    case 'process_payment':
        try {
            $booking_id = filter_var($_POST['booking_id'], FILTER_SANITIZE_NUMBER_INT);
            $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            // For card payments
            if ($payment_method === 'card') {
                // Validate card details
                if (empty($_POST['card_number']) || empty($_POST['expiry_date']) || empty($_POST['cvv'])) {
                    throw new Exception('All card details are required');
                }
                
                $card_number = filter_var($_POST['card_number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $expiry_date = filter_var($_POST['expiry_date'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $cvv = filter_var($_POST['cvv'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                // validate cvv
                if (strlen($cvv) !== 3) {
                    throw new Exception('Invalid CVV, must be 3 digits');
                }
                
                // Basic card validation
                if (strlen($card_number) !== 16) {
                    throw new Exception('Invalid card number, must be 16 digits');
                }

              // Validate and format expiry date
                $expiry_date = preg_replace('/[^0-9]/', '', $_POST['expiry_date']); // Remove non-digits

                if (strlen($expiry_date) === 4) {
                    // Add slash if not present
                    $expiry_date = substr($expiry_date, 0, 2) . '/' . substr($expiry_date, 2);
                } 

                // Validate format and length
                if (strlen($expiry_date) !== 5 || !preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry_date)) {
                    throw new Exception('Invalid expiry date format. Use MM/YY');
                }

                // Validate expiry date is not in the past
                $expiry = explode('/', $expiry_date);
                $expiry_month = $expiry[0];
                $expiry_year = '20' . $expiry[1];
                $current_year = date('Y');
                $current_month = date('m');

                if ($expiry_year < $current_year || 
                    ($expiry_year == $current_year && $expiry_month < $current_month)) {
                    throw new Exception('Card has expired');
                }

                // Process payment
                $payment_details = json_encode([
                    'card' => substr($card_number, -4),
                    'expiry' => $expiry_date,
                    'cvv' => $cvv
                ]);
        
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Error processing card details');
                }
            } 
            // For mobile money
            else if ($payment_method === 'mobile_money') {
                $phone_number = filter_var($_POST['phone_number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $network = filter_var($_POST['network'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $payment_details = json_encode([
                    'phone' => $phone_number,
                    'network' => $network
                ]);
            }
            else {
                throw new Exception('Invalid payment method');
            }
        
            $transaction_id = 'TXN' . time() . rand(100, 999);
        
            // Create payment record
            $stmt = $conn->prepare("
                INSERT INTO payments (
                    booking_id, 
                    amount, 
                    payment_method,
                    payment_details,
                    transaction_id,
                    payment_status
                ) VALUES (?, ?, ?, ?, ?, 'completed')
            ");
        
            if (!$stmt) {
                throw new Exception('Database preparation failed');
            }
        
            $stmt->bind_param("idsss", 
                $booking_id, 
                $amount, 
                $payment_method, 
                $payment_details, 
                $transaction_id
            );
        
            if (!$stmt->execute()) {
                throw new Exception('Payment record creation failed');
            }
        
            // Update booking status
            $update_result = $conn->query("
                UPDATE bookings 
                SET booking_status = 'confirmed', 
                    payment_status = 'partial' 
                WHERE id = $booking_id
            ");
        
            if (!$update_result) {
                throw new Exception('Booking status update failed');
            }
        
            echo json_encode([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'transaction_id' => $transaction_id
            ]);
        
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        break;
    
    case 'get_booking':
        $booking_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        
        // Get booking details
        $stmt = $conn->prepare("
            SELECT b.*, br.request_text 
            FROM bookings b
            LEFT JOIN booking_requests br ON b.id = br.booking_id
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            echo json_encode([
                'status' => 'success',
                'booking' => $booking
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Booking not found'
            ]);
        }
        break;

    case 'edit_booking':
        try {
            // Get and sanitize input
            $booking_id = filter_var($_POST['booking_id'], FILTER_SANITIZE_NUMBER_INT);
            $check_in = filter_var($_POST['check_in'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $check_out = filter_var($_POST['check_out'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $adults = filter_var($_POST['adults'], FILTER_SANITIZE_NUMBER_INT);
            $kids = filter_var($_POST['kids'], FILTER_SANITIZE_NUMBER_INT);
            $requests = filter_var($_POST['requests'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            // Validate dates
            if (strtotime($check_out) <= strtotime($check_in)) {
                throw new Exception('Check-out date must be after check-in date');
            }
            
            // Get room_id for availability check
            $stmt = $conn->prepare("SELECT room_id FROM bookings WHERE id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Booking not found');
            }
            
            $booking = $result->fetch_assoc();
            $room_id = $booking['room_id'];
            
            // Check room availability (excluding current booking)
            $availability = $conn->prepare("
                SELECT COUNT(*) as booked 
                FROM bookings 
                WHERE room_id = ? 
                AND id != ?
                AND booking_status IN ('pending', 'confirmed')
                AND (
                    (check_in BETWEEN ? AND ?) 
                    OR (check_out BETWEEN ? AND ?)
                    OR (check_in <= ? AND check_out >= ?)
                )
            ");
            
            $availability->bind_param("iissssss", 
                $room_id, $booking_id,
                $check_in, $check_out, 
                $check_in, $check_out, 
                $check_in, $check_out
            );
            
            $availability->execute();
            $result = $availability->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['booked'] > 0) {
                throw new Exception('Room is not available for the selected dates');
            }
            
            // Begin transaction
            $conn->begin_transaction();
            
            // Update booking
            $update = $conn->prepare("
                UPDATE bookings 
                SET check_in = ?, check_out = ?, adults = ?, kids = ?
                WHERE id = ?
            ");
            $update->bind_param("ssiii", $check_in, $check_out, $adults, $kids, $booking_id);
            
            if (!$update->execute()) {
                throw new Exception('Failed to update booking');
            }
            
            // Update or insert booking request
            if (!empty($requests)) {
                                // Check if request exists
                                $check = $conn->prepare("SELECT id FROM booking_requests WHERE booking_id = ?");
                                $check->bind_param("i", $booking_id);
                                $check->execute();
                                $result = $check->get_result();
                                
                                if ($result->num_rows > 0) {
                                    // Update existing request
                                    $request_id = $result->fetch_assoc()['id'];
                                    $update_request = $conn->prepare("
                                        UPDATE booking_requests 
                                        SET request_text = ? 
                                        WHERE id = ?
                                    ");
                                    $update_request->bind_param("si", $requests, $request_id);
                                    
                                    if (!$update_request->execute()) {
                                        throw new Exception('Failed to update booking request');
                                    }
                                } else {
                                    // Insert new request
                                    $insert_request = $conn->prepare("
                                        INSERT INTO booking_requests (booking_id, request_text)
                                        VALUES (?, ?)
                                    ");
                                    $insert_request->bind_param("is", $booking_id, $requests);
                                    
                                    if (!$insert_request->execute()) {
                                        throw new Exception('Failed to add booking request');
                                    }
                                }
                            }
                            
                            // Commit transaction
                            $conn->commit();
                            
                            echo json_encode([
                                'status' => 'success',
                                'message' => 'Booking updated successfully'
                            ]);
                            
                        } catch (Exception $e) {
                            // Rollback transaction on error
                            $conn->rollback();
                            
                            echo json_encode([
                                'status' => 'error',
                                'message' => $e->getMessage()
                            ]);
                        }
                        break;
                
                    default:
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'Invalid action specified'
                        ]);
                        break;
                }
                