<?php
require_once '../config/database.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to submit a review'
    ]);
    exit;
}

switch($_POST['action']) {
    case 'add_review':
        $booking_id = filter_var($_POST['booking_id'], FILTER_SANITIZE_NUMBER_INT);
        $rating = filter_var($_POST['rating'], FILTER_SANITIZE_NUMBER_INT);
        $review_text = filter_var($_POST['review_text'], FILTER_SANITIZE_STRING);
        $user_id = get_user_id();

        // Verify booking belongs to user
        $verify = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ? AND booking_status = 'completed'");
        $verify->bind_param("ii", $booking_id, $user_id);
        $verify->execute();
        
        if ($verify->get_result()->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid booking or not eligible for review'
            ]);
            exit;
        }

        // Add review
        $stmt = $conn->prepare("
            INSERT INTO reviews (booking_id, user_id, rating, review_text) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->bind_param("iiis", $booking_id, $user_id, $rating, $review_text);

        if ($stmt->execute()) {
            // Update room rating
            updateRoomRating($conn, $booking_id);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Review submitted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to submit review'
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action specified'
        ]);
}

function updateRoomRating($conn, $booking_id) {
    // Get room_id from booking
    $room_query = $conn->query("
        SELECT room_id FROM bookings WHERE id = $booking_id
    ");
    $room = $room_query->fetch_assoc();
    
    // Calculate average rating
    $rating_query = $conn->query("
        SELECT AVG(rating) as avg_rating 
        FROM reviews r 
        JOIN bookings b ON r.booking_id = b.id 
        WHERE b.room_id = {$room['room_id']}
    ");
    $avg_rating = $rating_query->fetch_assoc()['avg_rating'];
    
    // Update room rating
    $conn->query("
        UPDATE rooms 
        SET rating = $avg_rating 
        WHERE id = {$room['room_id']}
    ");
}
