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

if ($_POST['action'] === 'add') {
    $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
    $floor_number = filter_var($_POST['floor_number'], FILTER_SANITIZE_NUMBER_INT);
    $room_type = filter_var($_POST['room_type'], FILTER_SANITIZE_STRING);
    $view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT);
    $size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
    $capacity = filter_var($_POST['capacity'], FILTER_SANITIZE_NUMBER_INT);
    $bed_config = filter_var($_POST['bed_config'], FILTER_SANITIZE_STRING);
    $is_accessible = filter_var($_POST['is_accessible'] ?? 0, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $amenities = filter_var($_POST['amenities'], FILTER_SANITIZE_STRING);
// Check if room number already exists
$check_stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = ?");
$check_stmt->bind_param("s", $room_number);
$check_stmt->execute();
$check_stmt->bind_result($count);
$check_stmt->fetch();
$check_stmt->close();

if ($count > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Room number already exists. Please use a different number.'
    ]);
    exit;
}
 $image_path = '';
$upload_dir = '../uploads/rooms/'; // Absolute path

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $filename = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $image_filename = uniqid() . '.' . $ext;
        $image_path = 'uploads/rooms/' . $image_filename; // Relative path for DB
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_filename);
    }
}


    $stmt = $conn->prepare("INSERT INTO rooms (
        room_number, room_type, price, description, 
        size, capacity, amenities, image,
        status, floor_number, bed_config, 
        view_type, is_accessible
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available', ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "ssdsiiissssi", 
        $room_number, $room_type, $price, $description,
        $size, $capacity, $amenities, $image_path,
        $floor_number, $bed_config, $view_type, $is_accessible
    );
    

    try {
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Room added successfully'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database operation failed: ' . $e->getMessage()
        ]);
    } finally {
        $stmt->close();
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
    $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
    $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
    $room_type = filter_var($_POST['room_type'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $size = filter_var($_POST['size'], FILTER_SANITIZE_NUMBER_INT);
    $capacity = filter_var($_POST['capacity'], FILTER_SANITIZE_NUMBER_INT);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
    $amenities = filter_var($_POST['amenities'], FILTER_SANITIZE_STRING);
    $floor_number = filter_var($_POST['floor_number'], FILTER_SANITIZE_NUMBER_INT);
    $bed_config = filter_var($_POST['bed_config'], FILTER_SANITIZE_STRING);
    $view_type = filter_var($_POST['view_type'], FILTER_SANITIZE_STRING);
    $is_accessible = isset($_POST['is_accessible']) ? 1 : 0;

    try {
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $image_filename = uniqid() . '.' . $ext;
                $image_path = 'uploads/rooms/' . $image_filename;
               if ( move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path))

               {
                try {
                    $stmt = $conn->prepare("UPDATE rooms SET 
                room_number=?, room_type=?, price=?, size=?, capacity=?,
                description=?, amenities=?, floor_number=?, bed_config=?,
                view_type=?, is_accessible=? , image=?
                WHERE id=?");
            
            $stmt->bind_param("ssdiississisi", 
                $room_number, $room_type, $price, $size, $capacity,
                $description, $amenities, $floor_number, $bed_config,
                $view_type, $is_accessible, $image_path, $room_id);
            $stmt->execute();
              
                echo json_encode(['status' => 'success', 'message' => 'Room updated successfully with image']);
            } 
                catch (\Throwable $th) {
                    //throw $th;
                    echo json_encode(['status' => 'error', 'message' => 'Error updating room with image']);
                }


               }

                
 
                
            }
        }
         else {

           try {
            $stmt = $conn->prepare("UPDATE rooms SET 
            room_number=?, room_type=?, price=?, size=?, capacity=?,
            description=?, amenities=?, floor_number=?, bed_config=?,
            view_type=?, is_accessible=?
            WHERE id=?");
        
        $stmt->bind_param("ssdiississii", 
            $room_number, $room_type, $price, $size, $capacity,
            $description, $amenities, $floor_number, $bed_config,
            $view_type, $is_accessible, $room_id);
            $stmt->execute();
            echo json_encode(['status' => 'success', 'message' => 'Room updated successfully withou image']);

           } catch (\Throwable $th) {
            //throw $th;
            echo json_encode(['status' => 'error', 'message' => 'Error updating room without image']);
           }
        }

        // if ($stmt->execute()) {
        //     echo json_encode(['status' => 'success', 'message' => 'Room updated successfully']);
        // } else {
        //     throw new Exception($stmt->error);
        // }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update room: ' . $e->getMessage()]);
    }
    exit;
}



