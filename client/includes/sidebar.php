<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-teal-950 text-white w-64 flex-shrink-0 transition-all duration-300" id="sidebar">
            <div class="p-4 border-b border-amber-500">
                <img src="../images/logo.png" alt="Logo" class="w-20 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-center">Hotel Dashboard</h1>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
                            <i class="fas fa-calendar-check"></i>
                            <span>Bookings</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="rooms.php" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
                            <i class="fas fa-bed"></i>
                            <span>Rooms</span>
                        </a>
                    </li>
                    <li>
                <a href="reports.php" class="flex items-center space-x-3 p-2 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                    <li>
                        <a href="profile.php" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
                            <i class="fas fa-user-circle"></i>
                            <span>Profile</span>
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

       <!-- Main Content -->
<div class="flex-1 overflow-auto">
    <div class="p-6">