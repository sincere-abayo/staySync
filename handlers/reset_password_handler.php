<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if reset email exists in session
    if (!isset($_SESSION['reset_email'])) {
        $response = [
            'status' => 'error',
            'message' => 'Invalid session. Please start the password reset process again.'
        ];
        echo json_encode($response);
        exit;
    }
    
    $email = $_SESSION['reset_email'];
    $password = $_POST['password'];
    
    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the user's password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if ($stmt->execute()) {
        // Clear the session variable
        unset($_SESSION['reset_email']);
        
        $response = [
            'status' => 'success',
            'message' => 'Your password has been reset successfully. You can now login with your new password.'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to update password. Please try again.'
        ];
    }
    
    echo json_encode($response);
    exit;
}
