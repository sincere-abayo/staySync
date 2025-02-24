<?php
function create_booking($user_id, $room_id, $check_in, $check_out) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $room_id, $check_in, $check_out);
    
    if ($stmt->execute()) {
        $conn->query("UPDATE rooms SET status = 'booked' WHERE id = " . $room_id);
        return true;
    }
    return false;
}
