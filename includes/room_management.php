<?php
function add_room($room_number, $room_type, $price, $description) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $room_number, $room_type, $price, $description);
    
    return $stmt->execute();
}

function get_available_rooms() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM rooms WHERE status = 'available'");
    return $result->fetch_all(MYSQLI_ASSOC);
}
