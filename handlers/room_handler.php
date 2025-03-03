<?php

session_start();
require_once '../config/database.php';
require_once '../includes/session.php';

// check_admin();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $room_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    // Check if room exists and get image path
    $stmt = $conn->prepare("SELECT image FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();

    // Delete room
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);

    if ($stmt->execute()) {
        // Delete image file if exists
        if ($room && $room['image'] && file_exists('../' . $room['image'])) {
            unlink('../' . $room['image']);
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete room']);
    }
    exit;
}


header('Content-Type: application/json');

if ($_REQUEST['action'] === 'add') {
  
    
    $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
    $room_type = filter_var($_POST['room_type'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT);
    $size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
    $capacity = filter_var($_POST['capacity'], FILTER_SANITIZE_NUMBER_INT);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $amenities = filter_var($_POST['amenities'], FILTER_SANITIZE_STRING);

    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Create uploads/rooms directory if it doesn't exist
            if (!file_exists('../uploads/rooms')) {
                mkdir('../uploads/rooms', 0777, true);
            }
            
            $image_path = 'uploads/rooms/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
        }
    }

    $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, price, size, capacity, description, amenities, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiisss", $room_number, $room_type, $price, $size, $capacity, $description, $amenities, $image_path);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Room added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add room: ' . $conn->error]);
    }
    exit;



    
   
}

if ($_GET['action'] === 'get') {
    $room_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($room);
    exit;
}


if ($_REQUEST['action'] === 'update') {

    // Check if this is an update action
        $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
        $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
        $room_type = filter_var($_POST['room_type'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
        $capacity = filter_var($_POST['capacity'], FILTER_SANITIZE_NUMBER_INT);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
        $amenities = filter_var($_POST['amenities'], FILTER_SANITIZE_STRING);

        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0 && $_FILES['image']['size'] > 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $image_path = 'uploads/rooms/' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
                
                $stmt = $conn->prepare("UPDATE rooms SET room_number=?, room_type=?, price=?, size=?, capacity=?, description=?, amenities=?, image=? WHERE id=?");
                $stmt->bind_param("ssdiisssi", $room_number, $room_type, $price, $size, $capacity, $description, $amenities, $image_path, $room_id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE rooms SET room_number=?, room_type=?, price=?, size=?, capacity=?, description=?, amenities=? WHERE id=?");
            $stmt->bind_param("ssdiissi", $room_number, $room_type, $price, $size, $capacity, $description, $amenities, $room_id);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Room updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update room: ' . $conn->error]);
        }
        exit;
}

