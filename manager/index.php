<?php
require_once '../includes/session.php';
require_once '../includes/session_timeout.php';
require_once '../config/database.php';

check_admin();
check_session_timeout();
// Fetch dashboard statistics
$stats = [
    'total_rooms' => $conn->query("SELECT COUNT(*) FROM rooms")->fetch_row()[0],
    'booked_rooms' => $conn->query("SELECT COUNT(*) FROM rooms WHERE status = 'booked'")->fetch_row()[0],
    'available_rooms' => $conn->query("SELECT COUNT(*) FROM rooms WHERE status = 'available'")->fetch_row()[0],
    'total_bookings' => $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0]
];

// Include header
include_once 'includes/header.php';

// Include sidebar
include_once 'includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 overflow-auto">
    <div class="p-6">
        <div class="p-8">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-teal-950">Dashboard Overview</h2>
                <p class="text-gray-600">Welcome to your hotel management dashboard</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Rooms -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-teal-950">
                    <div class="flex items-center">
                        <div class="p-3 bg-teal-950 rounded-full">
                            <i class="fas fa-hotel text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Total Rooms</h3>
                            <p class="text-2xl font-bold text-teal-950"><?php echo $stats['total_rooms']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Booked Rooms -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-amber-500">
                    <div class="flex items-center">
                        <div class="p-3 bg-amber-500 rounded-full">
                            <i class="fas fa-key text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Booked Rooms</h3>
                            <p class="text-2xl font-bold text-amber-500"><?php echo $stats['booked_rooms']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Available Rooms -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-500 rounded-full">
                            <i class="fas fa-door-open text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Available Rooms</h3>
                            <p class="text-2xl font-bold text-green-500"><?php echo $stats['available_rooms']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Total Bookings -->
                <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-500 rounded-full">
                            <i class="fas fa-calendar-check text-white text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm">Total Bookings</h3>
                            <p class="text-2xl font-bold text-blue-500"><?php echo $stats['total_bookings']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-teal-950">Recent Bookings</h3>
                </div>
                <div class="p-4">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $recent_bookings = $conn->query("
                                SELECT b.id, u.name, r.room_number, b.check_in, b.booking_status 
                                FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                JOIN rooms r ON b.room_id = r.id 
                                ORDER BY b.created_at DESC LIMIT 5
                            ");
                            
                            while ($booking = $recent_bookings->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">#<?php echo $booking['id']; ?></td>
                                <td class="px-6 py-4"><?php echo $booking['name']; ?></td>
                                <td class="px-6 py-4"><?php echo $booking['room_number']; ?></td>
                                <td class="px-6 py-4"><?php echo date('d M Y', strtotime($booking['check_in'])); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?php echo $booking['booking_status'] == 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="booking_details.php?id=<?php echo $booking['id']; ?>" 
                                       class="text-amber-500 hover:text-amber-600">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Rooms Section -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-teal-950">Recent Rooms</h2>
                    <a href="room-management.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                        View All Rooms
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    $recent_rooms = $conn->query("SELECT * FROM rooms ORDER BY created_at DESC LIMIT 5");
                    while($room = $recent_rooms->fetch_assoc()): ?>
                        <div class="bg-white p-4 rounded-lg shadow-lg">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-teal-950"><?php echo $room['room_type']; ?></h3>
                                    <p class="text-gray-600">Room #: <?php echo $room['room_number']; ?></p>
                                    <p class="text-amber-500 font-bold">$<?php echo number_format($room['price'], 2); ?>/night</p>
                                    <p class="text-gray-600">Status: 
                                        <span class="<?php 
                                            echo $room['status'] === 'available' ? 'text-green-500' : 
                                                ($room['status'] === 'booked' ? 'text-amber-500' : 'text-red-500'); 
                                            ?>">
                                            <?php echo ucfirst($room['status']); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="viewRoom(<?php echo $room['id']; ?>)" 
                                            class="text-teal-950 hover:text-teal-800">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editRoom(<?php echo $room['id']; ?>)" 
                                            class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteRoom(<?php echo $room['id']; ?>)" 
                                            class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Staff Management Section -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-teal-950">Staff Management</h2>
                    <a href="staff.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                        View All Staff
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    $recent_staff = $conn->query("
                    SELECT s.id, u.name, s.role as position, u.id as employee_id 
                    FROM staff s
                    JOIN users u ON s.user_id = u.id
                    WHERE u.role = 'staff'
                    ORDER BY u.created_at DESC LIMIT 3
                ");
                
                    if ($recent_staff && $recent_staff->num_rows > 0) {
                        while($staff = $recent_staff->fetch_assoc()): ?>
                            <div class="bg-white p-4 rounded-lg shadow-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-2xl text-gray-500"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-semibold text-teal-950"><?php echo $staff['name']; ?></h3>
                                        <p class="text-gray-600"><?php echo $staff['position']; ?></p>
                                        <p class="text-gray-600">ID: <?php echo $staff['employee_id']; ?></p>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button onclick="editStaff(<?php echo $staff['id']; ?>)" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteStaff(<?php echo $staff['id']; ?>)" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endwhile;
                    } else { ?>
                        <div class="col-span-3 bg-white p-4 rounded-lg shadow-lg text-center">
                            <p class="text-gray-600">No staff records found. <a href="staff.php" class="text-amber-500 hover:underline">Add staff members</a>.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewRoom(roomId) {
    window.location.href = `room-view.php?id=${roomId}`;
}

function editRoom(roomId) {
    window.location.href = `room-edit.php?id=${roomId}`;
}

function deleteRoom(roomId) {
    if (confirm('Are you sure you want to delete this room?')) {
        fetch(`handlers/room_handler.php?action=delete&id=${roomId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Room deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the room');
        });
    }
}

function editStaff(staffId) {
    window.location.href = `staff-edit.php?id=${staffId}`;
}

function deleteStaff(staffId) {
    if (confirm('Are you sure you want to delete this staff member?')) {
        fetch(`handlers/staff_handler.php?action=delete&id=${staffId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Staff member deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the staff member');
        });
    }
}
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
