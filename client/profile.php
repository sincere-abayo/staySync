<?php
require_once '../includes/session.php';
require_once '../includes/session_timeout.php';
require_once '../config/database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

check_login();

$client_id = $_SESSION['user_id'];

// Fetch user data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $client_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_SPECIAL_CHARS);
        
        // Check if email already exists for another user
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check->bind_param("si", $email, $client_id);
        $email_check->execute();
        $email_result = $email_check->get_result();
        
        if ($email_result->num_rows > 0) {
            $update_error = "Email address already in use by another account.";
        } else {
            // Update user profile
            $update_query = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $update_query->bind_param("sssi", $name, $email, $phone, $client_id);
            
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
            $password_query->bind_param("si", $hashed_password, $client_id);
            
            if ($password_query->execute()) {
                $password_success = "Password changed successfully.";
            } else {
                $password_error = "Failed to change password. Please try again.";
            }
        }
    } 
   
}



// Get membership status based on loyalty points
$loyalty_query = $conn->prepare("SELECT points FROM loyalty_points WHERE user_id = ?");
$loyalty_query->bind_param("i", $client_id);
$loyalty_query->execute();
$loyalty_result = $loyalty_query->get_result();

if ($loyalty_result->num_rows > 0) {
    $loyalty_data = $loyalty_result->fetch_assoc();
    $points = $loyalty_data['points'];
    
    // Determine membership tier
    if ($points >= 1000) {
        $membership_tier = "Platinum";
        $membership_color = "text-purple-600";
    } elseif ($points >= 500) {
        $membership_tier = "Gold";
        $membership_color = "text-amber-500";
    } elseif ($points >= 200) {
        $membership_tier = "Silver";
        $membership_color = "text-gray-500";
    } else {
        $membership_tier = "Bronze";
        $membership_color = "text-amber-700";
    }
} else {
    $points = 0;
    $membership_tier = "Bronze";
    $membership_color = "text-amber-700";
}


// Include sidebar
include_once 'includes/sidebar.php';
?>

        <h1 class="text-3xl font-bold text-teal-950 mb-6">Profile Settings</h1>

        <!-- Profile Overview -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <div class="w-24 h-24 bg-teal-950 rounded-full flex items-center justify-center text-white text-3xl">
                        <?php 
                            $name_parts = explode(' ', $user['name']);
                            echo substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : '');
                        ?>
                    </div>
                    <button class="absolute bottom-0 right-0 bg-amber-500 h-10 w-10 rounded-full text-white hover:bg-amber-600">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-teal-950"><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="text-gray-600">Member since: <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    <p class="<?php echo $membership_color; ?>"><?php echo $membership_tier; ?> Member</p>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form action="" method="POST">
                <input type="hidden" name="action" value="update_profile">
                
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
                
                <!-- Personal Information -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-teal-950 mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <button type="reset" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form action="" method="POST">
                <input type="hidden" name="action" value="change_password">
                
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
                
                <!-- Password Information -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-teal-950 mb-4">Change Password</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div class="md:col-span-2">
                            <div class="h-0.5 bg-gray-200 my-2"></div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">New Password</label>
                            <input type="password" name="new_password" required minlength="8"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="8"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <button type="reset" class="px-6 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-teal-950 text-white rounded-lg hover:bg-teal-900">
                        Change Password
                    </button>
                </div>
            </form>
        </div>

  

        <!-- Loyalty Status -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h3 class="text-xl font-semibold text-teal-950 mb-4">Loyalty Program Status</h3>
            
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
                <div>
                    <div class="flex items-center">
                        <i class="fas fa-award text-2xl <?php echo $membership_color; ?> mr-2"></i>
                        <h4 class="text-lg font-bold <?php echo $membership_color; ?>"><?php echo $membership_tier; ?> Member</h4>
                    </div>
                    <p class="text-gray-600 mt-1">You have <span class="font-bold"><?php echo $points; ?> points</span> in your account</p>
                </div>
                
                <a href="loyalty.php" class="mt-4 md:mt-0 bg-teal-950 text-white px-4 py-2 rounded-lg hover:bg-teal-900">
                    View Loyalty Benefits
                </a>
            </div>
            
            <!-- Progress to next tier -->
            <?php if ($membership_tier !== 'Platinum'): ?>
                <?php 
                    $next_tier = $membership_tier === 'Bronze' ? 'Silver' : ($membership_tier === 'Silver' ? 'Gold' : 'Platinum');
                    $points_needed = $membership_tier === 'Bronze' ? 200 : ($membership_tier === 'Silver' ? 500 : 1000);
                    $current_threshold = $membership_tier === 'Bronze' ? 0 : ($membership_tier === 'Silver' ? 200 : 500);
                    $next_threshold = $membership_tier === 'Bronze' ? 200 : ($membership_tier === 'Silver' ? 500 : 1000);
                    $progress = min(100, (($points - $current_threshold) / ($next_threshold - $current_threshold)) * 100);
                ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span><?php echo $membership_tier; ?></span>
                        <span><?php echo $next_tier; ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-amber-500 h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        You need <?php echo $points_needed - $points; ?> more points to reach <?php echo $next_tier; ?> status
                    </p>
                </div>
            <?php else: ?>
                <div class="bg-purple-100 text-purple-800 p-3 rounded-lg">
                    <p class="font-medium">Congratulations! You've reached our highest membership tier.</p>
                    <p>Enjoy exclusive Platinum benefits and thank you for your loyalty.</p>
                </div>
            <?php endif; ?>
            
        
        </div>

        <!-- Delete Account (Optional) -->
        <div class="mt-6 text-center">
            <button type="button" onclick="confirmDeleteAccount()" class="text-red-600 hover:text-red-800">
                Delete My Account
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteAccount() {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will permanently delete your account and all associated data. This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete my account',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show password confirmation
            Swal.fire({
                title: 'Confirm with Password',
                text: 'Please enter your password to confirm account deletion',
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Delete Account',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    if (!password) {
                        Swal.showValidationMessage('Password is required');
                        return false;
                    }
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('action', 'delete_account');
                    formData.append('password', password);
                    
                    return fetch('../handlers/user_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'error') {
                            throw new Error(data.message || 'Failed to delete account');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Account Deleted',
                        text: 'Your account has been successfully deleted.',
                        icon: 'success',
                        confirmButtonColor: '#0f766e'
                    }).then(() => {
                        window.location.href = '../index.php';
                    });
                }
            });
        }
    });
}

// Profile picture upload functionality could be added here
</script>

<?php include_once 'includes/footer.php'; ?>
