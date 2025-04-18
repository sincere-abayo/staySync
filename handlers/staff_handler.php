<?php
require_once '../includes/session.php';
require_once '../config/database.php';
check_login();

// Set default redirect
$redirect_url = '../manager/staff.php';

try {
    // Handle GET requests (like delete)
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        $action = $_GET['action'];
        
        if ($action === 'delete' && isset($_GET['id'])) {
            $staff_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            
            // First get the user_id from staff record
            $stmt = $conn->prepare("SELECT user_id FROM staff WHERE id = ?");
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Staff member not found");
            }
            
            $user_id = $result->fetch_assoc()['user_id'];
            
            // Start transaction
            $conn->begin_transaction();
            
            // Delete staff record first (due to foreign key constraint)
            $delete_staff = $conn->prepare("DELETE FROM staff WHERE id = ?");
            $delete_staff->bind_param("i", $staff_id);
            
            if (!$delete_staff->execute()) {
                throw new Exception("Failed to delete staff record: " . $conn->error);
            }
            
            // Then delete user record
            $delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delete_user->bind_param("i", $user_id);
            
            if (!$delete_user->execute()) {
                $conn->rollback();
                throw new Exception("Failed to delete user record: " . $conn->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['staff_message'] = [
                'type' => 'success',
                'text' => 'Staff member deleted successfully'
            ];
            
            header("Location: $redirect_url");
            exit;
        }
    }
    
    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add':
                // Validate and sanitize input
                $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
                $department = filter_var($_POST['department'], FILTER_SANITIZE_STRING);
                $staff_role = filter_var($_POST['staff_role'], FILTER_SANITIZE_STRING);
                $password = $_POST['password'];
                
                if (empty($name) || empty($email) || empty($password) || empty($department) || empty($staff_role)) {
                    throw new Exception("All required fields must be filled");
                }
                
                if (strlen($password) < 8) {
                    throw new Exception("Password must be at least 8 characters long");
                }
                
                // Check if email already exists
                $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $check_email->bind_param("s", $email);
                $check_email->execute();
                
                if ($check_email->get_result()->num_rows > 0) {
                    throw new Exception("Email address already in use");
                }
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Start transaction
                $conn->begin_transaction();
                
                // Insert user record
                $insert_user = $conn->prepare("
                    INSERT INTO users (name, email, phone, password, role) 
                    VALUES (?, ?, ?, ?, 'staff')
                ");
                $insert_user->bind_param("ssss", $name, $email, $phone, $hashed_password);
                
                if (!$insert_user->execute()) {
                    throw new Exception("Failed to create user account: " . $conn->error);
                }
                
                $user_id = $conn->insert_id;
                
                // Insert staff record
                $insert_staff = $conn->prepare("
                    INSERT INTO staff (user_id, role, department) 
                    VALUES (?, ?, ?)
                ");
                $insert_staff->bind_param("iss", $user_id, $staff_role, $department);
                
                if (!$insert_staff->execute()) {
                    $conn->rollback();
                    throw new Exception("Failed to create staff record: " . $conn->error);
                }
                
                // Commit transaction
                $conn->commit();
                
                $_SESSION['staff_message'] = [
                    'type' => 'success',
                    'text' => 'Staff member added successfully'
                ];
                
                header("Location: $redirect_url");
                exit;
                
            case 'update':
                // Validate and sanitize input
                $staff_id = filter_var($_POST['staff_id'], FILTER_SANITIZE_NUMBER_INT);
                $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
                $department = filter_var($_POST['department'], FILTER_SANITIZE_STRING);
                $staff_role = filter_var($_POST['staff_role'], FILTER_SANITIZE_STRING);
                $password = $_POST['password']; // Optional
                
                if (empty($name) || empty($email) || empty($department) || empty($staff_role)) {
                    throw new Exception("All required fields must be filled");
                }
                
                // Get user_id from staff record
                $stmt = $conn->prepare("SELECT user_id FROM staff WHERE id = ?");
                $stmt->bind_param("i", $staff_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Staff member not found");
                }
                
                $user_id = $result->fetch_assoc()['user_id'];
                
                // Check if email already exists for another user
                $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $check_email->bind_param("si", $email, $user_id);
                $check_email->execute();
                
                if ($check_email->get_result()->num_rows > 0) {
                    throw new Exception("Email address already in use by another user");
                }
                
                // Start transaction
                $conn->begin_transaction();
                
                // Update user record
                if (!empty($password)) {
                    // If password is provided, update it too
                    if (strlen($password) < 8) {
                        throw new Exception("Password must be at least 8 characters long");
                    }
                    
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $update_user = $conn->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, phone = ?, password = ? 
                        WHERE id = ?
                    ");
                    $update_user->bind_param("ssssi", $name, $email, $phone, $hashed_password, $user_id);
                } else {
                    // Otherwise just update other fields
                    $update_user = $conn->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, phone = ? 
                        WHERE id = ?
                    ");
                    $update_user->bind_param("sssi", $name, $email, $phone, $user_id);
                }
                
                if (!$update_user->execute()) {
                    throw new Exception("Failed to update user account: " . $conn->error);
                }
                
                // Update staff record
                $update_staff = $conn->prepare("
                    UPDATE staff 
                    SET role = ?, department = ? 
                    WHERE id = ?
                ");
                $update_staff->bind_param("ssi", $staff_role, $department, $staff_id);
                
                if (!$update_staff->execute()) {
                    $conn->rollback();
                    throw new Exception("Failed to update staff record: " . $conn->error);
                }
                
                // Commit transaction
                $conn->commit();
                
                $_SESSION['staff_message'] = [
                    'type' => 'success',
                    'text' => 'Staff member updated successfully'
                ];
                
                header("Location: $redirect_url");
                exit;
                
            default:
                throw new Exception("Invalid action");
        }
    }
    
    // If we get here, something went wrong
    throw new Exception("Invalid request");
    
} catch (Exception $e) {
    // Set error message and redirect
    $_SESSION['staff_message'] = [
        'type' => 'error',
        'text' => $e->getMessage()
    ];
    
    header("Location: $redirect_url");
    exit;
}