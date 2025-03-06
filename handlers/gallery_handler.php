<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'add':
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            $image_path = '';
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $image_path = 'uploads/gallery/' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO gallery_images (title, category, description, image_path, is_featured) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $title, $category, $description, $image_path, $is_featured);
            
            echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
            break;
            
        case 'update':
            $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
            $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
            $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $image_path = 'uploads/gallery/' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
                    
                    $stmt = $conn->prepare("UPDATE gallery_images SET title=?, category=?, description=?, image_path=?, is_featured=? WHERE id=?");
                    $stmt->bind_param("ssssii", $title, $category, $description, $image_path, $is_featured, $id);
                }
            } else {
                $stmt = $conn->prepare("UPDATE gallery_images SET title=?, category=?, description=?, is_featured=? WHERE id=?");
                $stmt->bind_param("sssii", $title, $category, $description, $is_featured, $id);
            }
            
            echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $delete_vars);
    $id = filter_var($delete_vars['id'], FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    echo json_encode(['status' => $stmt->execute() ? 'success' : 'error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode($result->fetch_assoc());
}
?>
