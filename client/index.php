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
                <img src="images/logo.png" alt="Logo" class="w-20 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-center">Hotel Dashboard</h1>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="active.html" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
                            <i class="fas fa-calendar-check"></i>
                            <span>Active Bookings</span>
                        </a>
                    </li>
                    <li>
                        <a href="past-booking.html" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
                            <i class="fas fa-history"></i>
                            <span>Past Bookings</span>
                        </a>
                    </li>
                  
                    <li>
                        <a href="profile.html" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-amber-500">
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
                        <p class="text-sm">John Doe</p>
                    </div>
                </div>
                <button class="w-full bg-amber-500 py-2 rounded-lg hover:bg-amber-600">Logout</button>
            </div>
        </div>

        <!-- Mobile Menu Button -->
        <button class="fixed top-4 right-4 z-50 lg:hidden bg-teal-950 text-white p-2 rounded-lg" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

       <!-- Main Content -->
<div class="flex-1 overflow-auto">
    <div class="p-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Total Bookings</h3>
                <p class="text-3xl font-bold text-amber-500">3</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Active Bookings</h3>
                <p class="text-3xl font-bold text-amber-500">3</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Loyalty Points</h3>
                <p class="text-3xl font-bold text-amber-500">450</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Total Spent</h3>
                <p class="text-3xl font-bold text-amber-500">$4,491</p>
            </div>
        </div>

        <!-- Active Bookings Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-teal-950">Your Active Bookings</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Deluxe Room Booking -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-teal-950">Deluxe Room</h3>
                            <p class="text-gray-600">Booking ID: #DEL123</p>
                            <p class="text-gray-600">Check-in: 25 Dec 2023</p>
                            <p class="text-gray-600">Check-out: 28 Dec 2023</p>
                            <p class="text-gray-600">Guests: 2 Adults</p>
                        </div>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Active</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="font-bold text-amber-500">$299/night</p>
                        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Cancel</button>
                    </div>
                </div>

                <!-- Suite Room Booking -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-teal-950">Suite Room</h3>
                            <p class="text-gray-600">Booking ID: #SUI456</p>
                            <p class="text-gray-600">Check-in: 25 Dec 2023</p>
                            <p class="text-gray-600">Check-out: 28 Dec 2023</p>
                            <p class="text-gray-600">Guests: 4 Adults</p>
                        </div>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Active</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="font-bold text-amber-500">$499/night</p>
                        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Past Bookings Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4 text-teal-950">Past Bookings</h2>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-teal-950">Family Room</h3>
                        <p class="text-gray-600">Booking ID: #FAM789</p>
                        <p class="text-gray-600">Stay: 15-18 Nov 2023</p>
                        <p class="text-gray-600">Total Paid: $699</p>
                    </div>
                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">Completed</span>
                </div>
                
                <!-- Rating Section -->
                <div class="mt-4">
                    <h4 class="font-semibold mb-2 text-teal-950">Rate Your Stay</h4>
                    <div class="flex space-x-2 mb-2">
                        <i class="fas fa-star text-amber-500 cursor-pointer"></i>
                        <i class="fas fa-star text-amber-500 cursor-pointer"></i>
                        <i class="fas fa-star text-amber-500 cursor-pointer"></i>
                        <i class="fas fa-star text-amber-500 cursor-pointer"></i>
                        <i class="far fa-star text-amber-500 cursor-pointer"></i>
                    </div>
                    <textarea 
                        placeholder="Share your experience..." 
                        class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                        rows="3"
                    ></textarea>
                    <button class="mt-2 bg-teal-950 text-white px-4 py-2 rounded hover:bg-amber-500 transition-colors">
                        Submit Review
                    </button>
                </div>
            </div>
        </div>

        <!-- Booking Summary -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h3 class="text-xl font-semibold text-teal-950 mb-4">Booking Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">Total Rooms Booked</p>
                    <p class="text-2xl font-bold text-teal-950">3</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">Total Nights</p>
                    <p class="text-2xl font-bold text-teal-950">9</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">Average Rating</p>
                    <p class="text-2xl font-bold text-teal-950">4.5/5</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">Total Amount</p>
                    <p class="text-2xl font-bold text-amber-500">$4,491</p>
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
