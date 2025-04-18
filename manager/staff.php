<?php
require_once '../includes/session.php';
require_once '../config/database.php';
check_login();

// Fetch staff members with user details
$staff_query = "
    SELECT s.id, s.role as staff_role, s.department, 
           u.id as user_id, u.name, u.email, u.phone, u.role as user_role
    FROM staff s
    JOIN users u ON s.user_id = u.id
    ORDER BY s.id DESC
";
$staff_result = $conn->query($staff_query);

// Get departments for dropdown
$departments = ['Front Desk', 'Housekeeping', 'Restaurant', 'Maintenance', 'Security', 'Administration', 'Kitchen', 'Spa'];

// Get staff roles for dropdown
$staff_roles = ['Receptionist', 'Manager', 'Housekeeper', 'Chef', 'Waiter', 'Security Officer', 'Maintenance Technician', 'Spa Therapist'];

// Include header
include_once 'includes/header.php';

// Include sidebar
include_once 'includes/sidebar.php';
?>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>

<div class="flex-1 overflow-auto">
    <div class="p-6">
    <!-- Staff Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-teal-950">
            <h3 class="text-gray-500 text-sm">Total Staff</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-users text-teal-950 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-teal-950">
                    <?php echo $staff_result->num_rows; ?>
                </span>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-amber-500">
            <h3 class="text-gray-500 text-sm">On Duty Today</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-user-clock text-amber-500 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-amber-500">
                    <?php 
                        // This would ideally come from a shifts or attendance table
                        // For now, just show a placeholder or percentage of total
                        echo round($staff_result->num_rows * 0.7); 
                    ?>
                </span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm">Departments</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-building text-green-500 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-green-500">
                    <?php echo count($departments); ?>
                </span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm">New Hires</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-user-plus text-blue-500 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-blue-500">
                    <?php 
                        // Get staff members added in the last 30 days
                        $new_hires_query = "
                            SELECT COUNT(*) as count FROM users 
                            WHERE role = 'staff' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        ";
                        $new_hires = $conn->query($new_hires_query)->fetch_assoc()['count'];
                        echo $new_hires;
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Staff List Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-teal-950">Staff Management</h2>
        <button onclick="openStaffModal()" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
            <i class="fas fa-plus mr-2"></i>Add New Staff
        </button>
    </div>

    <!-- Display messages if any -->
    <?php if (isset($_SESSION['staff_message'])): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $_SESSION['staff_message']['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo $_SESSION['staff_message']['text']; ?>
        </div>
        <?php unset($_SESSION['staff_message']); ?>
    <?php endif; ?>

    <!-- Staff Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-teal-950 text-white">
                <tr>
                    <th class="px-6 py-3 text-left">Employee ID</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Department</th>
                    <th class="px-6 py-3 text-left">Position</th>
                    <th class="px-6 py-3 text-left">Contact</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if ($staff_result->num_rows > 0): ?>
                    <?php while ($staff = $staff_result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">EMP<?php echo str_pad($staff['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td class="px-6 py-4 flex items-center">
                                <div class="w-8 h-8 bg-teal-950 rounded-full flex items-center justify-center text-white text-sm mr-3">
                                    <?php 
                                        $name_parts = explode(' ', $staff['name']);
                                        echo substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : '');
                                    ?>
                                </div>
                                <?php echo htmlspecialchars($staff['name']); ?>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($staff['department']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($staff['staff_role']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($staff['phone'] ?: $staff['email']); ?></td>
                            <td class="px-6 py-4">
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Active</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="openEditModal(<?php echo $staff['id']; ?>, '<?php echo addslashes($staff['name']); ?>', '<?php echo addslashes($staff['email']); ?>', '<?php echo addslashes($staff['phone']); ?>', '<?php echo addslashes($staff['department']); ?>', '<?php echo addslashes($staff['staff_role']); ?>')" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $staff['id']; ?>, '<?php echo addslashes($staff['name']); ?>')" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No staff members found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-teal-950 mb-4">Add New Staff Member</h3>
                <form class="space-y-4" action="../handlers/staff_handler.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department; ?>"><?php echo $department; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Position</label>
                        <select name="staff_role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                            <?php foreach ($staff_roles as $role): ?>
                                <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeStaffModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">Add Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div id="editStaffModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-teal-950 mb-4">Edit Staff Member</h3>
                <form class="space-y-4" action="../handlers/staff_handler.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="staff_id" id="edit_staff_id">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="edit_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="edit_email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" name="phone" id="edit_phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department" id="edit_department" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department; ?>"><?php echo $department; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Position</label>
                        <select name="staff_role" id="edit_staff_role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                            <?php foreach ($staff_roles as $role): ?>
                                <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">Update Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openStaffModal() {
    document.getElementById('addStaffModal').classList.remove('hidden');
}

function closeStaffModal() {
    document.getElementById('addStaffModal').classList.add('hidden');
}

function openEditModal(id, name, email, phone, department, role) {
    document.getElementById('edit_staff_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone || '';
    
    // Set select dropdowns
    const deptSelect = document.getElementById('edit_department');
    for (let i = 0; i < deptSelect.options.length; i++) {
        if (deptSelect.options[i].value === department) {
            deptSelect.selectedIndex = i;
            break;
        }
    }
    
    const roleSelect = document.getElementById('edit_staff_role');
    for (let i = 0; i < roleSelect.options.length; i++) {
        if (roleSelect.options[i].value === role) {
            roleSelect.selectedIndex = i;
            break;
        }
    }
    
    document.getElementById('editStaffModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editStaffModal').classList.add('hidden');
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to remove ${name} from staff?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `../handlers/staff_handler.php?action=delete&id=${id}`;
        }
    });
}

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addStaffModal');
    const editModal = document.getElementById('editStaffModal');
    
    if (event.target == addModal) {
        closeStaffModal();
    }
    
    if (event.target == editModal) {
        closeEditModal();
    }
}
</script>
