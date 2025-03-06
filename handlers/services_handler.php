<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'add':
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            $icon = filter_var($_POST['icon'], FILTER_SANITIZE_STRING);
            
            $image_path = '';
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $image_path = 'uploads/services/' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
               
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO hotel_services (name, description, icon, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $description, $icon, $image_path);
            $status = $stmt->execute();
                   $message = $status ? 'Service added successfully' : 'Error adding service';
               
            echo json_encode(['status' => $status ? 'success' : 'error', 'message' => $message]);
            break;
            
        case 'update':
            $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            $icon = filter_var($_POST['icon'], FILTER_SANITIZE_STRING);
            
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $image_path = 'uploads/services/' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
                    
                    $stmt = $conn->prepare("UPDATE hotel_services SET name=?, description=?, icon=?, image=? WHERE id=?");
                    $stmt->bind_param("ssssi", $name, $description, $icon, $image_path, $id);
                   $status = $stmt->execute();
                   $message = $status ? 'Service updated successfully' : 'Error updating service';
                }
            } else {
                $stmt = $conn->prepare("UPDATE hotel_services SET name=?, description=?, icon=? WHERE id=?");
                $stmt->bind_param("sssi", $name, $description, $icon, $id);
                $status = $stmt->execute();
                $message = $status ? 'Service updated successfully' : 'Error updating service';
            }
            
            echo json_encode(['status' => $status ? 'success' : 'error', 'message' => $message]);
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $delete_vars);
    $id = filter_var($delete_vars['id'], FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("DELETE FROM hotel_services WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("SELECT * FROM hotel_services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_assoc());
}
?>
