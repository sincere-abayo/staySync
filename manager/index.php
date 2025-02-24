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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StaySync Manager Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-teal-950 text-white w-64 flex-shrink-0 transition-all duration-300" id="sidebar">
            <div class="p-4 border-b border-amber-500">
                <img src="images/logo.png" alt="Logo" class="w-20 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-center">StaySync </h1>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="flex items-center space-x-3 p-2 bg-amber-500 rounded-lg">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php" class="flex items-center space-x-3 p-2 hover:bg-amber-500 rounded-lg">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Bookings</span>
                        </a>
                    </li>
                    <li>
                        <a href="rooms.php" class="flex items-center space-x-3 p-2 hover:bg-amber-500 rounded-lg">
                            <i class="fas fa-door-open"></i>
                            <span>Rooms</span>
                        </a>
                    </li>
                    <li>
                        <a href="staff.php" class="flex items-center space-x-3 p-2 hover:bg-amber-500 rounded-lg">
                            <i class="fas fa-users"></i>
                            <span>Staff</span>
                        </a>
                    </li>
                    <li>
                        <a href="reports.php" class="flex items-center space-x-3 p-2 hover:bg-amber-500 rounded-lg">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="absolute bottom-0 w-64 p-4 border-t border-amber-500">
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-user-circle text-2xl"></i>
                    <div>
                        <p class="font-medium">Welcome,</p>
                        <p class="text-sm"><?php echo $_SESSION['user_name']; ?></p>
                    </div>
                </div>
                <a href="../handlers/logout_handler.php" 
                   class="block w-full bg-amber-500 text-center py-2 rounded-lg hover:bg-amber-600">
                    Logout
                </a>
            </div>
        </div>

        <!-- Mobile Menu Button -->
        <button class="fixed top-4 right-4 z-50 lg:hidden bg-teal-950 text-white p-2 rounded-lg" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

<!-- Add these sections in your main content area -->
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

        <!-- Rooms Management Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-teal-950">Rooms Management</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Sample Room Card -->
                <div class="bg-white p-4 rounded-lg shadow-lg">
                   
                
                <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-teal-950">Deluxe Room</h3>
                            <p class="text-gray-600">Room #: 101</p>
                            <p class="text-amber-500 font-bold">$299/night</p>
                            <p class="text-gray-600">Status: Available</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Management Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-teal-950">Staff Management</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Sample Staff Card -->
                <div class="bg-white p-4 rounded-lg shadow-lg">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-2xl text-gray-500"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-teal-950">Jane Smith</h3>
                            <p class="text-gray-600">Receptionist</p>
                            <p class="text-gray-600">ID: EMP001</p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button class="text-blue-500 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Handle responsive sidebar
        function handleResize() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 1024) {
                sidebar.classList.add('fixed', 'h-full', 'z-40');
                sidebar.classList.remove('relative');
            } else {
                sidebar.classList.remove('fixed', 'h-full', 'z-40', '-translate-x-full');
                sidebar.classList.add('relative');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();
    </script>
</body>
</html>
