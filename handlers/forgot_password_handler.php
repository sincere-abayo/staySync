<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // If email exists, store it in session for the reset password page
        $_SESSION['reset_email'] = $email;
        
        $response = [
            'status' => 'success',
            'message' => 'Email found. You can now reset your password.',
            'redirect' => 'reset_password.html'
        ];
    } else {
        // For security reasons, don't reveal that the email doesn't exist
        $response = [
            'status' => 'error',
            'message' => 'Email not found in our system. Please check and try again.'
        ];
    }
    
    echo json_encode($response);
    exit;
}
