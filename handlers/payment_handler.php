<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once '../config/database.php';
require_once '../includes/session.php';


try {
    // Check if action is set
    if (!isset($_POST['action'])) {
        throw new Exception('Action is required');
    }

    $action = $_POST['action'];
    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : '../manager/booking.php';

    // Handle different actions
    switch ($action) {
        case 'add':
            // Extract and sanitize
            $booking_id = filter_var($_POST['booking_id'], FILTER_SANITIZE_NUMBER_INT);
            $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $payment_date = filter_var($_POST['payment_date'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $notes = filter_var($_POST['notes'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $reference_number = filter_var($_POST['reference_number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if (!$booking_id || !$amount || !$payment_method || !$payment_date) {
                throw new Exception('Required payment fields are missing');
            }

            // Create simple payment_details (you can expand this later based on method)
            $payment_details = json_encode([
                'method' => $payment_method,
                'reference' => $reference_number,
                'notes' => $notes
            ]);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error encoding payment details');
            }

            $transaction_id = 'TXN' . time() . rand(100, 999);

            // Insert payment
            $stmt = $conn->prepare("
                INSERT INTO payments (
                    booking_id, 
                    amount, 
                    payment_method,
                    payment_details,
                    payment_date,
                    transaction_id,
                    payment_status
                ) VALUES (?, ?, ?, ?, ?, ?, 'completed')
            ");

            if (!$stmt) {
                throw new Exception('Database preparation failed: ' . $conn->error);
            }

            $stmt->bind_param("idssss", 
                $booking_id, 
                $amount, 
                $payment_method, 
                $payment_details, 
                $payment_date,
                $transaction_id
            );

            if (!$stmt->execute()) {
                throw new Exception('Failed to record payment: ' . $stmt->error);
            }

            // Optionally update booking
            $update_result = $conn->query("
                UPDATE bookings 
                SET booking_status = 'confirmed', 
                    payment_status = 'partial' 
                WHERE id = $booking_id
            ");

            if (!$update_result) {
                throw new Exception('Failed to update booking status: ' . $conn->error);
            }

            // Set success message in session
            session_start();
            $_SESSION['payment_message'] = [
                'type' => 'success',
                'text' => 'Payment recorded successfully. Transaction ID: ' . $transaction_id
            ];
            
            // Redirect back to the booking details page
            header("Location: $redirect_url");
            exit;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Set error message in session
    session_start();
    $_SESSION['payment_message'] = [
        'type' => 'error',
        'text' => $e->getMessage()
    ];
    
    // Redirect back to the booking details page
    header("Location: $redirect_url");
    exit;
}
