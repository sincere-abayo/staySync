<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
    $room_type = filter_var($_POST['room_type'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT);
    $size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
    $capacity = filter_var($_POST['capacity'], FILTER_SANITIZE_NUMBER_INT);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $amenities = filter_var($_POST['amenities'], FILTER_SANITIZE_STRING);

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image_path = 'uploads/rooms/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
        }
    }

    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price, size, capacity, description, amenities, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiisss", $room_number, $room_type, $price, $size, $capacity, $description, $amenities, $image_path);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Room added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add room']);
    }
    exit;
}

function get_available_rooms() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM rooms WHERE status = 'available'");
    return $result->fetch_all(MYSQLI_ASSOC);
}
