<?php
require_once '../includes/session.php';
// require_once '../includes/session_timeout.php';
require_once '../config/database.php';

// check_admin();
// check_session_timeout();

// Get today's date in Y-m-d format
$today = date('Y-m-d');

// Count today's check-ins
$checkins_query = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE check_in = ? AND booking_status = 'confirmed'");
$checkins_query->bind_param("s", $today);
$checkins_query->execute();
$checkins_result = $checkins_query->get_result();
$today_checkins = $checkins_result->fetch_assoc()['count'];

// Count today's check-outs
$checkouts_query = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE check_out = ? AND booking_status = 'confirmed'");
$checkouts_query->bind_param("s", $today);
$checkouts_query->execute();
$checkouts_result = $checkouts_query->get_result();
$today_checkouts = $checkouts_result->fetch_assoc()['count'];

// Count pending confirmations
$pending_query = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'pending'");
$pending_confirmations = $pending_query->fetch_assoc()['count'];

// Include header
include_once 'includes/header.php';

// Include sidebar
include_once 'includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 overflow-auto">
    <div class="p-6">
        <!-- Booking Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Today's Check-ins</h3>
                <div class="flex items-center">
                    <i class="fas fa-sign-in-alt text-teal-950 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-teal-950"><?php echo $today_checkins; ?></span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Today's Check-outs</h3>
                <div class="flex items-center">
                    <i class="fas fa-sign-out-alt text-amber-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-amber-500"><?php echo $today_checkouts; ?></span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Pending Confirmations</h3>
                <div class="flex items-center">
                    <i class="fas fa-clock text-orange-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-orange-500"><?php echo $pending_confirmations; ?></span>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
            <form id="searchForm" method="GET" action="" class="flex flex-wrap gap-4">
                <input type="text" name="search" placeholder="Search bookings..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       class="border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-950">
                
                <select name="status" class="border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-950">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
                
                <input type="date" name="date" 
                       value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>"
                       class="border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-950">
                
                <button type="submit" class="bg-teal-950 text-white px-4 py-2 rounded-lg hover:bg-teal-900">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                
                <a href="booking.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-semibold text-teal-950">All Bookings</h3>
                <button onclick="openNewBookingModal()" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                    <i class="fas fa-plus mr-2"></i>New Booking
                </button>
            </div> -->
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        // Build the query based on search parameters
                        $query = "SELECT b.id, u.name, r.room_number, r.room_type, b.check_in, b.check_out, 
                                  b.payment_status, b.booking_status, b.created_at 
                                  FROM bookings b 
                                  JOIN users u ON b.user_id = u.id 
                                  JOIN rooms r ON b.room_id = r.id";
                        
                        $conditions = [];
                        $params = [];
                        $types = "";
                        
                        if (isset($_GET['search']) && !empty($_GET['search'])) {
                            $search = "%" . $_GET['search'] . "%";
                            $conditions[] = "(u.name LIKE ? OR r.room_number LIKE ?)";
                            $params[] = $search;
                            $params[] = $search;
                            $types .= "ss";
                        }
                        
                        if (isset($_GET['status']) && !empty($_GET['status'])) {
                            $conditions[] = "b.booking_status = ?";
                            $params[] = $_GET['status'];
                            $types .= "s";
                        }
                        
                        if (isset($_GET['date']) && !empty($_GET['date'])) {
                            $conditions[] = "(b.check_in = ? OR b.check_out = ?)";
                            $params[] = $_GET['date'];
                            $params[] = $_GET['date'];
                            $types .= "ss";
                        }
                        
                        if (!empty($conditions)) {
                            $query .= " WHERE " . implode(" AND ", $conditions);
                        }
                        
                        $query .= " ORDER BY b.created_at DESC LIMIT 50";
                        
                        $stmt = $conn->prepare($query);
                        
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            while ($booking = $result->fetch_assoc()) {
                                // Determine status class
                                $statusClass = '';
                                switch ($booking['booking_status']) {
                                    case 'confirmed':
                                        $statusClass = 'bg-green-100 text-green-800';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-orange-100 text-orange-800';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'bg-red-100 text-red-800';
                                        break;
                                    case 'completed':
                                        $statusClass = 'bg-blue-100 text-blue-800';
                                        break;
                                }
                                
                                // Determine payment status class
                                $paymentClass = '';
                                switch ($booking['payment_status']) {
                                    case 'complete':
                                        $paymentClass = 'bg-green-100 text-green-800';
                                        break;
                                    case 'partial':
                                        $paymentClass = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'pending':
                                        $paymentClass = 'bg-red-100 text-red-800';
                                        break;
                                }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">#<?php echo $booking['id']; ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($booking['name']); ?></td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($booking['room_number']); ?> 
                                <span class="text-xs text-gray-500">(<?php echo htmlspecialchars($booking['room_type']); ?>)</span>
                            </td>
                            <td class="px-6 py-4"><?php echo date('d M Y', strtotime($booking['check_in'])); ?></td>
                            <td class="px-6 py-4"><?php echo date('d M Y', strtotime($booking['check_out'])); ?></td>
                            <td class="px-6 py-4">
                                <span class="<?php echo $paymentClass; ?> px-3 py-1 rounded-full text-xs">
                                    <?php echo ucfirst($booking['payment_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="<?php echo $statusClass; ?> px-3 py-1 rounded-full text-xs">
                                    <?php echo ucfirst($booking['booking_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewBooking(<?php echo $booking['id']; ?>)" 
                                            class="text-teal-950 hover:text-teal-800" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                  
                                </div>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">No bookings found</td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openNewBookingModal() {
    // Fetch available rooms first
    fetch('../handlers/room_handler.php?action=get_available')
        .then(response => response.json())
        .then(rooms => {
            let roomOptions = '';
            rooms.forEach(room => {
                roomOptions += `<option value="${room.id}">${room.room_number} - ${room.room_type} ($${room.price}/night)</option>`;
            });

            // Fetch users for guest selection
            fetch('../handlers/user_handler.php?action=get_clients')
                .then(response => response.json())
                .then(users => {
                    let userOptions = '';
                    users.forEach(user => {
                        userOptions += `<option value="${user.id}">${user.name} (${user.email})</option>`;
                    });

                    Swal.fire({
                        title: 'Create New Booking',
                        html: `
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Guest</label>
                                                               <select id="user_id" class="swal2-input w-full">
                                    <option value="">Select a guest</option>
                                    ${userOptions}
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                                <select id="room_id" class="swal2-input w-full">
                                    <option value="">Select a room</option>
                                    ${roomOptions}
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date</label>
                                    <input type="date" id="check_in" class="swal2-input w-full" min="${new Date().toISOString().split('T')[0]}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Date</label>
                                    <input type="date" id="check_out" class="swal2-input w-full" min="${new Date().toISOString().split('T')[0]}">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                                <select id="payment_status" class="swal2-input w-full">
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                    <option value="complete">Complete</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Booking Status</label>
                                <select id="booking_status" class="swal2-input w-full">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                </select>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Create Booking',
                        confirmButtonColor: '#f97316',
                        customClass: {
                            confirmButton: 'swal2-confirm',
                            cancelButton: 'swal2-cancel'
                        },
                        preConfirm: () => {
                            return {
                                user_id: document.getElementById('user_id').value,
                                room_id: document.getElementById('room_id').value,
                                check_in: document.getElementById('check_in').value,
                                check_out: document.getElementById('check_out').value,
                                payment_status: document.getElementById('payment_status').value,
                                booking_status: document.getElementById('booking_status').value
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            createBooking(result.value);
                        }
                    });
                });
        });
}

function createBooking(bookingData) {
    // Validate required fields
    if (!bookingData.user_id || !bookingData.room_id || !bookingData.check_in || !bookingData.check_out) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#f97316'
        });
        return;
    }

    // Validate dates
    const checkIn = new Date(bookingData.check_in);
    const checkOut = new Date(bookingData.check_out);
    if (checkOut <= checkIn) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Dates',
            text: 'Check-out date must be after check-in date',
            confirmButtonColor: '#f97316'
        });
        return;
    }

    // Show loading state
    Swal.fire({
        title: 'Creating Booking',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Send booking data to server
    fetch('../handlers/booking_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'create',
            ...bookingData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Booking Created',
                text: data.message,
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#f97316'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred. Please try again.',
            confirmButtonColor: '#f97316'
        });
    });
}

function viewBooking(id) {
    window.location.href = `booking_details.php?id=${id}`;
}

function editBooking(id) {
    // Fetch booking details
    fetch(`../handlers/booking_handler.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(booking => {
            // Fetch available rooms
            fetch('../handlers/room_handler.php?action=get_all')
                .then(response => response.json())
                .then(rooms => {
                    let roomOptions = '';
                    rooms.forEach(room => {
                        const selected = room.id == booking.room_id ? 'selected' : '';
                        roomOptions += `<option value="${room.id}" ${selected}>${room.room_number} - ${room.room_type} ($${room.price}/night)</option>`;
                    });

                    // Fetch all users
                    fetch('../handlers/user_handler.php?action=get_clients')
                        .then(response => response.json())
                        .then(users => {
                            let userOptions = '';
                            users.forEach(user => {
                                const selected = user.id == booking.user_id ? 'selected' : '';
                                userOptions += `<option value="${user.id}" ${selected}>${user.name} (${user.email})</option>`;
                            });

                            Swal.fire({
                                title: 'Edit Booking',
                                html: `
                                    <input type="hidden" id="booking_id" value="${booking.id}">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Guest</label>
                                        <select id="user_id" class="swal2-input w-full">
                                            <option value="">Select a guest</option>
                                            ${userOptions}
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                                        <select id="room_id" class="swal2-input w-full">
                                            <option value="">Select a room</option>
                                            ${roomOptions}
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date</label>
                                            <input type="date" id="check_in" class="swal2-input w-full" value="${booking.check_in}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Date</label>
                                            <input type="date" id="check_out" class="swal2-input w-full" value="${booking.check_out}">
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                                        <select id="payment_status" class="swal2-input w-full">
                                            <option value="pending" ${booking.payment_status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="partial" ${booking.payment_status === 'partial' ? 'selected' : ''}>Partial</option>
                                            <option value="complete" ${booking.payment_status === 'complete' ? 'selected' : ''}>Complete</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Booking Status</label>
                                        <select id="booking_status" class="swal2-input w-full">
                                            <option value="pending" ${booking.booking_status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="confirmed" ${booking.booking_status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                            <option value="cancelled" ${booking.booking_status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                            <option value="completed" ${booking.booking_status === 'completed' ? 'selected' : ''}>Completed</option>
                                        </select>
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Update Booking',
                                confirmButtonColor: '#f97316',
                                preConfirm: () => {
                                    return {
                                        id: document.getElementById('booking_id').value,
                                        user_id: document.getElementById('user_id').value,
                                        room_id: document.getElementById('room_id').value,
                                        check_in: document.getElementById('check_in').value,
                                        check_out: document.getElementById('check_out').value,
                                        payment_status: document.getElementById('payment_status').value,
                                        booking_status: document.getElementById('booking_status').value
                                    };
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    updateBooking(result.value);
                                }
                            });
                        });
                });
        });
}

function updateBooking(bookingData) {
    // Show loading state
    Swal.fire({
        title: 'Updating Booking',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Send booking data to server
    fetch('../handlers/booking_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'update',
            ...bookingData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Booking Updated',
                text: data.message,
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#f97316'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred. Please try again.',
            confirmButtonColor: '#f97316'
        });
    });
}

function deleteBooking(id) {
    Swal.fire({
        title: 'Delete Booking',
        text: 'Are you sure you want to delete this booking? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Deleting Booking',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send delete request to server
            fetch('../handlers/booking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete',
                    id: id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Deleted',
                        text: data.message,
                        confirmButtonColor: '#f97316'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#f97316'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    confirmButtonColor: '#f97316'
                });
            });
        }
    });
}
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
