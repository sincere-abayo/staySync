<?php
require_once '../includes/session.php';
require_once '../config/database.php';
check_login();

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        
        // Check if email already exists for another user
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check->bind_param("si", $email, $user_id);
        $email_check->execute();
        $email_result = $email_check->get_result();
        
        if ($email_result->num_rows > 0) {
            $update_error = "Email address already in use by another account.";
        } else {
            // Update user profile
            $update_query = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $update_query->bind_param("sssi", $name, $email, $phone, $user_id);
            
            if ($update_query->execute()) {
                $_SESSION['user_name'] = $name;
                $update_success = "Profile updated successfully.";
                
                // Refresh user data
                $user_query->execute();
                $user = $user_query->get_result()->fetch_assoc();
            } else {
                $update_error = "Failed to update profile. Please try again.";
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $password_error = "Current password is incorrect.";
        } elseif (strlen($new_password) < 8) {
            $password_error = "New password must be at least 8 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $password_error = "New passwords do not match.";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $password_query->bind_param("si", $hashed_password, $user_id);
            
            if ($password_query->execute()) {
                $password_success = "Password changed successfully.";
            } else {
                $password_error = "Failed to change password. Please try again.";
            }
        }
    }
}

// Include header
include_once 'includes/header.php';

// Include sidebar
include_once 'includes/sidebar.php';
?>

<div class="flex-1 overflow-auto p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-teal-950">My Profile</h2>
        </div>
        
        <!-- Profile Information -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex items-center mb-6">
                    <div class="w-20 h-20 bg-teal-950 rounded-full flex items-center justify-center text-white text-3xl mr-6">
                        <?php 
                            $name_parts = explode(' ', $user['name']);
                            echo substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : '');
                        ?>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-teal-950"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p class="text-gray-600"><?php echo ucfirst($user['role']); ?></p>
                        <p class="text-sm text-gray-500">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                
                <!-- Success/Error Messages -->
                <?php if (isset($update_success)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                        <p><?php echo $update_success; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($update_error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <p><?php echo $update_error; ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Profile Form -->
                <form action="" method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                   class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                   class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled
                                   class="w-full border rounded-lg p-2 bg-gray-100">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-xl font-bold text-teal-950 mb-6">Change Password</h3>
                
                <!-- Success/Error Messages -->
                <?php if (isset($password_success)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                        <p><?php echo $password_success; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($password_error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <p><?php echo $password_error; ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Password Form -->
                <form action="" method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="current_password" required
                                   class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <div class="h-0.5 bg-gray-200 my-2"></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" required minlength="8"
                                   class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                            <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="8"
                                   class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-teal-950 text-white px-6 py-2 rounded-lg hover:bg-teal-900">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>