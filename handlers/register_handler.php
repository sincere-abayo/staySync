<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate phone number format
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid phone number format']);
        exit;
    }

    // Check for existing email or phone
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email or phone number already registered']);
        exit;
    }

    // Insert new user with phone
    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'client')");
    $stmt->bind_param("ssss", $name, $email, $phone, $password);

    if ($stmt->execute()) {
        // Get the new user's ID
        $user_id = $conn->insert_id;
        
        // Initialize session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'client';
        $_SESSION['last_activity'] = time();

        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful!',
            'redirect' => 'client/index.php'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Registration failed. Please try again.'
        ]);
    }
    exit;
}
