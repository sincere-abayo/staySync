<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            $response = [
                'status' => 'success',
                'redirect' => $user['role'] === 'manager' ? 'manager/index.php' : 'client/index.php'
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Invalid password'];
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Email not found'];
    }

    echo json_encode($response);
    exit;
}
