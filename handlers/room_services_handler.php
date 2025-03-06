<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'add_service':
            $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            $stmt = $conn->prepare("INSERT INTO room_services (room_id, service_name, service_description, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $room_id, $name, $description, $price);
            
            echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
            break;
            
        case 'add_maintenance':
            $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
            $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            $scheduled_date = filter_var($_POST['scheduled_date'], FILTER_SANITIZE_STRING);
            
            $stmt = $conn->prepare("INSERT INTO room_maintenance (room_id, maintenance_type, description, scheduled_date, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("isss", $room_id, $type, $description, $scheduled_date);
            
            echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
            break;
            
        case 'add_cleaning':
            $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
            $type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
            $next_scheduled = filter_var($_POST['next_scheduled'], FILTER_SANITIZE_STRING);
            
            $stmt = $conn->prepare("INSERT INTO room_cleaning (room_id, cleaning_type, next_scheduled) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $room_id, $type, $next_scheduled);
            
            echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
            break;
            case 'add_images':
                $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
                $upload_status = ['status' => 'success', 'message' => 'Images uploaded successfully'];
                
                if (!empty($_FILES['images'])) {
                    foreach($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_name = $_FILES['images']['name'][$key];
                        $file_size = $_FILES['images']['size'][$key];
                        $file_error = $_FILES['images']['error'][$key];
                        
                        if ($file_error === 0) {
                            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                                $image_path = 'uploads/rooms/' . uniqid() . '.' . $ext;
                                
                                if (move_uploaded_file($tmp_name, '../' . $image_path)) {
                                    $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path) VALUES (?, ?)");
                                    $stmt->bind_param("is", $room_id, $image_path);
                                    
                                    if (!$stmt->execute()) {
                                        $upload_status = ['status' => 'error', 'message' => 'Database error'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                
                echo json_encode($upload_status);
                break;
            
            case 'delete_image':
                $image_id = filter_var($_POST['image_id'], FILTER_SANITIZE_NUMBER_INT);
                
                $stmt = $conn->prepare("DELETE FROM room_images WHERE id = ?");
                $stmt->bind_param("i", $image_id);
                
                echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
                break;
            
            case 'set_primary':
                $image_id = filter_var($_POST['image_id'], FILTER_SANITIZE_NUMBER_INT);
                $room_id = filter_var($_POST['room_id'], FILTER_SANITIZE_NUMBER_INT);
                
                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("UPDATE room_images SET is_primary = 0 WHERE room_id = ?");
                    $stmt->bind_param("i", $room_id);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("UPDATE room_images SET is_primary = 1 WHERE id = ?");
                    $stmt->bind_param("i", $image_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['status' => 'success']);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
                break;
            
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $delete_vars);
    $id = filter_var($delete_vars['id'], FILTER_SANITIZE_NUMBER_INT);
    $type = $delete_vars['type'];
    
    switch($type) {
        case 'service':
            $table = 'room_services';
            break;
        case 'maintenance':
            $table = 'room_maintenance';
            break;
        case 'cleaning':
            $table = 'room_cleaning';
            break;
    }
    
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
}
?>
