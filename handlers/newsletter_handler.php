<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'subscribe') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please enter a valid email address'
        ]);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Successfully subscribed to newsletter'
            ]);
        } else {
            if ($stmt->errno == 1062) { // Duplicate entry
                echo json_encode([
                    'status' => 'error',
                    'message' => 'You are already subscribed to our newsletter'
                ]);
            } else {
                throw new Exception($stmt->error);
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'An error occurred. Please try again later.'
        ]);
    }
}
