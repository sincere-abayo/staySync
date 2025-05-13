<!-- Sidebar -->
<div class="bg-teal-950 text-white w-64 flex-shrink-0 transition-all duration-300 hidden lg:block" id="sidebar">
    <div class="p-4 border-b border-amber-500">
        <img src="../images/logo.png" alt="Logo" class="w-20 mx-auto mb-4">
        <h1 class="text-2xl font-bold text-center">StaySync </h1>
    </div>
    
    <nav class="p-4">
        <ul class="space-y-2">
            <li>
                <a href="index.php" class="flex items-center space-x-3 p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?> rounded-lg">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="services.php" class="flex items-center space-x-3 p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?> rounded-lg">
                    <i class="fas fa-concierge-bell"></i>
                    <span>Services</span>
                </a>
            </li>
            <li>
                <a href="galley.php" class="flex items-center space-x-3 p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'galley.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?> rounded-lg">
                    <i class="fas fa-images"></i>
                    <span>Galley</span>
                </a>
            </li>
            <li>
                <a href="booking.php" class="flex items-center space-x-3 p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?> rounded-lg">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li>
                <a href="room-management.php" class="flex items-center space-x-3 p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'room-management.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?> rounded-lg">
                    <i class="fas fa-door-open"></i>
                    <span>Rooms</span>
                </a>
            </li>
            <li>
                <a href="staff.php" class="flex items-center space-x-3 p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?> rounded-lg">
                    <i class="fas fa-users"></i>
                    <span>Staff</span>
                </a>
            </li>
            <li>
                <a href="reports.php" class="flex items-center space-x-3 p-2 <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-amber-500' : 'hover:bg-amber-500'; ?> rounded-lg">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="absolute bottom-0 w-64 p-4 border-t border-amber-500">
    <div class="flex items-center space-x-3 mb-4">
            <a href="profile.php" class="flex items-center space-x-3 hover:text-amber-500 transition-colors">
                <i class="fas fa-user-circle text-2xl"></i>
                <div>
                    <p class="font-medium">Welcome,</p>
                    <p class="text-sm"><?php echo $_SESSION['user_name']; ?></p>
                </div>
            </a>
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


