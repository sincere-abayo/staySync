<?php
require_once '../config/database.php';
require_once '../includes/functions.php';


// 1. Process Check-in/out Status Updates
$today = date('Y-m-d');
$conn->query("
    UPDATE bookings 
    SET check_in_status = 'checked_out'
    WHERE check_out < '$today' 
    AND check_in_status = 'checked_in'
");

// 2. Update Room Status
$conn->query("
    UPDATE rooms r
    JOIN bookings b ON r.id = b.room_id
    SET r.status = 
        CASE 
            WHEN b.check_in_status = 'checked_in' THEN 'occupied'
            WHEN b.check_in_status = 'checked_out' THEN 'available'
        END
");

// 3. Send Check-in Reminders
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$stmt = $conn->prepare("
    SELECT b.*, u.email, u.name, r.room_number 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    WHERE b.check_in = ? 
    AND b.reminder_sent = 0 
    AND b.booking_status = 'confirmed'
");

$stmt->bind_param('s', $tomorrow);
$stmt->execute();
$bookings = $stmt->get_result();

while($booking = $bookings->fetch_assoc()) {
    $conn->query("UPDATE bookings SET reminder_sent = 1 WHERE id = {$booking['id']}");
    sendCheckInReminder($booking);
}
