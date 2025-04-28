<?php
require_once '../includes/session.php';
// require_once '../includes/session_timeout.php';
require_once '../config/database.php';
check_login();
// check_admin();
// check_session_timeout();

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0;

if (!$booking_id) {
    // Redirect to bookings page if no valid ID provided
    header('Location: booking.php');
    exit;
}

// Fetch booking details with related information
$stmt = $conn->prepare("
    SELECT b.*, 
           u.name as guest_name, u.email as guest_email, u.phone as guest_phone,
           r.room_number, r.room_type, r.price as room_price, r.image as room_image
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Redirect if booking not found
    header('Location: booking.php');
    exit;
}

$booking = $result->fetch_assoc();

// Calculate booking duration and total cost
$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$duration = $check_in->diff($check_out)->days;
$total_cost = $duration * $booking['room_price'];

// Include header
include_once 'includes/header.php';

// Include sidebar
include_once 'includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 overflow-auto">
<div class="container mx-auto px-4 py-8">
        <?php
        // Display payment message if set
        if (isset($_SESSION['payment_message'])) {
            $message = $_SESSION['payment_message'];
            $alertClass = $message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            echo '<div class="' . $alertClass . ' p-4 rounded-lg mb-6">';
            echo '<p>' . htmlspecialchars($message['text']) . '</p>';
            echo '</div>';
            
            // Clear the message
            unset($_SESSION['payment_message']);
        }
        ?>
        <!-- Back Button and Booking ID -->
        <div class="flex justify-between items-center mb-6">
            <button onclick="history.back()" class="flex items-center text-teal-950 hover:text-amber-500">
                <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
            </button>
            <div class="flex items-center">
                <span class="text-gray-500 mr-2">Booking ID:</span>
                <span class="font-bold text-teal-950">#<?php echo $booking_id; ?></span>
            </div>
        </div>

        <!-- Booking Status Banner -->
        <div class="mb-6 p-4 rounded-lg <?php 
            echo $booking['booking_status'] === 'confirmed' ? 'bg-green-100' : 
                ($booking['booking_status'] === 'pending' ? 'bg-orange-100' : 
                ($booking['booking_status'] === 'cancelled' ? 'bg-red-100' : 'bg-blue-100')); 
            ?>">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas <?php 
                        echo $booking['booking_status'] === 'confirmed' ? 'fa-check-circle text-green-600' : 
                            ($booking['booking_status'] === 'pending' ? 'fa-clock text-orange-600' : 
                            ($booking['booking_status'] === 'cancelled' ? 'fa-times-circle text-red-600' : 'fa-check-double text-blue-600')); 
                        ?> text-2xl mr-3"></i>
                    <div>
                        <h2 class="text-xl font-bold <?php 
                            echo $booking['booking_status'] === 'confirmed' ? 'text-green-800' : 
                                ($booking['booking_status'] === 'pending' ? 'text-orange-800' : 
                                ($booking['booking_status'] === 'cancelled' ? 'text-red-800' : 'text-blue-800')); 
                            ?>">
                            Booking <?php echo ucfirst($booking['booking_status']); ?>
                        </h2>
                        <p class="text-gray-600">Created on <?php echo date('F j, Y, g:i a', strtotime($booking['created_at'])); ?></p>
                    </div>
                </div>
                <div>

                <!-- Add this inside your booking list loop, next to the cancel button -->
<?php if ($booking['booking_status'] === 'pending' || $booking['booking_status'] === 'confirmed'): ?>
    <button onclick="openEditBookingModal(<?php echo $booking['id']; ?>)" 
            class="text-blue-500 hover:text-blue-700 mr-2">
        <i class="fas fa-edit"></i> Edit
    </button>
<?php endif; ?>

                

                    <?php if ($booking['booking_status'] !== 'cancelled' && $booking['booking_status'] !== 'completed'): ?>
                        <button onclick="updateStatus(<?php echo $booking_id; ?>, 'cancelled')" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                    <?php endif; ?>
                    <?php if ($booking['booking_status'] === 'confirmed'): ?>
                        <button onclick="updateStatus(<?php echo $booking_id; ?>, 'completed')" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            <i class="fas fa-check-double mr-2"></i>Mark as Completed
                        </button>
                    <?php endif; ?>
                    <?php if ($booking['booking_status'] === 'pending'): ?>
                        <button onclick="updateStatus(<?php echo $booking_id; ?>, 'confirmed')" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                            <i class="fas fa-check mr-2"></i>Confirm
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Guest & Booking Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Guest Information -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 bg-teal-950 text-white">
                        <h3 class="text-lg font-semibold">Guest Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center text-teal-950">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-xl font-semibold text-teal-950"><?php echo htmlspecialchars($booking['guest_name']); ?></h4>
                                <p class="text-gray-600">Guest</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-500 text-sm">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($booking['guest_email']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Phone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($booking['guest_phone'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                              <!-- Booking Details -->
                              <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 bg-teal-950 text-white">
                        <h3 class="text-lg font-semibold">Booking Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <p class="text-gray-500 text-sm">Check-in Date</p>
                                    <p class="font-medium text-teal-950"><?php echo date('F j, Y', strtotime($booking['check_in'])); ?></p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-gray-500 text-sm">Duration</p>
                                    <p class="font-medium text-teal-950"><?php echo $duration; ?> night<?php echo $duration !== 1 ? 's' : ''; ?></p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-gray-500 text-sm">Number of Guests</p>
                                    <p class="font-medium text-teal-950">
                                        <?php echo $booking['adults']; ?> Adult<?php echo $booking['adults'] !== 1 ? 's' : ''; ?>, 
                                        <?php echo $booking['kids']; ?> Child<?php echo $booking['kids'] !== 1 ? 'ren' : ''; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Payment Status</p>
                                    <p class="font-medium">
                                        <span class="<?php 
                                            echo $booking['payment_status'] === 'complete' ? 'bg-green-100 text-green-800' : 
                                                ($booking['payment_status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                            ?> px-3 py-1 rounded-full text-xs">
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <p class="text-gray-500 text-sm">Check-out Date</p>
                                    <p class="font-medium text-teal-950"><?php echo date('F j, Y', strtotime($booking['check_out'])); ?></p>
                                </div>
                                <div class="mb-4">
                                    <p class="text-gray-500 text-sm">Room Rate</p>
                                    <p class="font-medium text-teal-950">$<?php echo number_format($booking['room_price'], 2); ?> per night</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Total Amount</p>
                                    <p class="font-bold text-xl text-amber-500">$<?php echo number_format($total_cost, 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


               <!-- Booking Requests -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-4 bg-teal-950 text-white">
        <h3 class="text-lg font-semibold">Booking Requests</h3>
    </div>
    <div class="p-6">
        <?php 
        // Fetch booking requests
        $request_query = $conn->prepare("SELECT request_text FROM booking_requests WHERE booking_id = ?");
        $request_query->bind_param("i", $booking_id);
        $request_query->execute();
        $request_result = $request_query->get_result();
        
        if ($request_result && $request_result->num_rows > 0): 
            $request_data = $request_result->fetch_assoc();
        ?>
            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($request_data['request_text'])); ?></p>
        <?php else: ?>
            <p class="text-gray-500 italic">No booking requests</p>
        <?php endif; ?>
    </div>
</div>


                <!-- Payment History -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 bg-teal-950 text-white flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Payment History</h3>
                        <button onclick="recordPayment(<?php echo $booking_id; ?>)" class="bg-amber-500 text-white px-3 py-1 rounded hover:bg-amber-600 text-sm">
                            <i class="fas fa-plus mr-1"></i> Record Payment
                        </button>
                    </div>
                    <div class="p-6">
                        <?php
                        // Fetch payment history
                        $payments_query = $conn->prepare("SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_date DESC");
                        $payments_query->bind_param("i", $booking_id);
                        $payments_query->execute();
                        $payments_result = $payments_query->get_result();
                        
                        if ($payments_result->num_rows > 0):
                        ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($payment = $payments_result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">$<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo ucfirst($payment['payment_method']); ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $payment['transaction_id']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 italic">No payment records found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Room Details -->
            <div class="space-y-6">
                <!-- Room Information -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="h-48 overflow-hidden">
                    <img src="../<?php echo $booking['room_image'] ?: 'assets/images/room-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($booking['room_type']); ?>" 
                             class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-teal-950 mb-2">Room <?php echo htmlspecialchars($booking['room_number']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($booking['room_type']); ?></p>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-gray-500 text-sm">Price per Night</p>
                                <p class="font-medium text-teal-950">$<?php echo number_format($booking['room_price'], 2); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Room Status</p>
                                <p class="font-medium">
                                    <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs">
                                        Booked
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <a href="room-view.php?id=<?php echo $booking['room_id']; ?>" class="block text-center bg-teal-950 text-white py-2 rounded-lg hover:bg-teal-900 transition-colors">
                            View Room Details
                        </a>
                    </div>
                </div>
                
                <!-- Check-in/Check-out Actions -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 bg-teal-950 text-white">
                        <h3 class="text-lg font-semibold">Actions</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <?php if ($booking['booking_status'] === 'confirmed' && date('Y-m-d') >= $booking['check_in'] && date('Y-m-d') < $booking['check_out']): ?>
                            <!-- Guest is currently staying -->
                            <div class="bg-blue-100 text-blue-800 p-3 rounded-lg">
                                <p class="font-medium">Guest is currently staying</p>
                                <p class="text-sm">Check-out on <?php echo date('F j, Y', strtotime($booking['check_out'])); ?></p>
                            </div>
                            
                            <button onclick="extendStay(<?php echo $booking_id; ?>)" class="w-full bg-amber-500 text-white py-2 rounded-lg hover:bg-amber-600 mb-2">
                                <i class="fas fa-calendar-plus mr-2"></i>Extend Stay
                            </button>
                            
                            <button onclick="earlyCheckout(<?php echo $booking_id; ?>)" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
                                <i class="fas fa-sign-out-alt mr-2"></i>Process Early Check-out
                            </button>
                        <?php elseif ($booking['booking_status'] === 'confirmed' && date('Y-m-d') === $booking['check_in']): ?>
                            <!-- Check-in day -->
                            <div class="bg-green-100 text-green-800 p-3 rounded-lg">
                                <p class="font-medium">Check-in day is today!</p>
                            </div>
                            
                            <button onclick="processCheckin(<?php echo $booking_id; ?>)" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600">
                                <i class="fas fa-sign-in-alt mr-2"></i>Process Check-in
                            </button>
                        <?php elseif ($booking['booking_status'] === 'confirmed' && date('Y-m-d') < $booking['check_in']): ?>
                            <!-- Future booking -->
                            <div class="bg-yellow-100 text-yellow-800 p-3 rounded-lg">
                                <p class="font-medium">Future booking</p>
                                <p class="text-sm">Check-in on <?php echo date('F j, Y', strtotime($booking['check_in'])); ?></p>
                            </div>
                            
                            <button onclick="modifyDates(<?php echo $booking_id; ?>)" class="w-full bg-amber-500 text-white py-2 rounded-lg hover:bg-amber-600">
                                <i class="fas fa-calendar-alt mr-2"></i>Modify Dates
                            </button>
                        <?php elseif ($booking['booking_status'] === 'completed'): ?>
                            <!-- Completed stay -->
                            <div class="bg-green-100 text-green-800 p-3 rounded-lg">
                                <p class="font-medium">Stay completed</p>
                                <p class="text-sm">Guest checked out on <?php echo date('F j, Y', strtotime($booking['check_out'])); ?></p>
                            </div>
                            
                            <button onclick="viewFeedback(<?php echo $booking_id; ?>)" class="w-full bg-teal-950 text-white py-2 rounded-lg hover:bg-teal-900">
                                <i class="fas fa-comment-alt mr-2"></i>View Feedback
                            </button>
                        <?php elseif ($booking['booking_status'] === 'cancelled'): ?>
                            <!-- Cancelled booking -->
                            <div class="bg-red-100 text-red-800 p-3 rounded-lg">
                                <p class="font-medium">Booking cancelled</p>
                                <p class="text-sm">Cancelled on <?php echo date('F j, Y', strtotime($booking['updated_at'] ?? $booking['created_at'])); ?></p>
                            </div>
                            
                            <button onclick="restoreBooking(<?php echo $booking_id; ?>)" class="w-full bg-amber-500 text-white py-2 rounded-lg hover:bg-amber-600">
                                <i class="fas fa-undo mr-2"></i>Restore Booking
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Additional Services -->
                 <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-4 bg-teal-950 text-white flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Additional Services</h3>
                        
                    </div>
                    <div class="p-6">
                        <?php
                        // Fetch additional services
                        $services_query = $conn->prepare("SELECT * FROM booking_services WHERE booking_id = ? ORDER BY created_at DESC");
                        $services_query->bind_param("i", $booking_id);
                        $services_query->execute();
                        $services_result = $services_query->get_result();
                        
                        if ($services_result->num_rows > 0):
                        ?>
                            <div class="space-y-4">
                                <?php while ($service = $services_result->fetch_assoc()): ?>
                                    <div class="flex justify-between items-center p-3 border rounded-lg">
                                        <div>
                                            <h4 class="font-medium text-teal-950"><?php echo htmlspecialchars($service['service_name']); ?></h4>
                                            <p class="text-sm text-gray-600"><?php echo date('M d, Y', strtotime($service['service_date'])); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-amber-500">$<?php echo number_format($service['price'], 2); ?></p>
                                            <button onclick="removeService(<?php echo $service['id']; ?>)" class="text-red-500 hover:text-red-700 text-sm">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 italic">No additional services</p>
                        <?php endif; ?>
                    </div>
                </div> 
            </div>
        </div>
    </div>
    <!-- Edit Booking Modal -->
<div id="editBookingModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-teal-950">Edit Booking</h3>
            <button type="button" onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="editBookingForm">
            <input type="hidden" id="edit_booking_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                <input type="date" id="edit_check_in" required
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                <input type="date" id="edit_check_out" required
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Adults</label>
                <input type="number" id="edit_adults" min="1" max="5" required
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Number of Children</label>
                <input type="number" id="edit_kids" min="0" max="5" required
                       class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                <textarea id="edit_requests" rows="3"
                          class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-teal-950 text-white rounded hover:bg-amber-500">
                    Update Booking
                </button>
            </div>
        </form>
    </div>   
</div>    

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script >



// Edit booking functions
function openEditBookingModal(bookingId) {
    // Fetch booking details
    fetch(`../handlers/booking_handler.php?action=get_booking&id=${bookingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const booking = data.booking;
                
                // Populate form fields
                document.getElementById('edit_booking_id').value = booking.id;
                document.getElementById('edit_check_in').value = booking.check_in;
                document.getElementById('edit_check_out').value = booking.check_out;
                document.getElementById('edit_adults').value = booking.adults;
                document.getElementById('edit_kids').value = booking.kids;
                
                // Get special requests if available
                if (booking.request_text) {
                    document.getElementById('edit_requests').value = booking.request_text;
                }
                
                // Set minimum dates
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('edit_check_in').min = today;
                document.getElementById('edit_check_out').min = today;
                
                // Show modal
                document.getElementById('editBookingModal').classList.remove('hidden');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load booking details',
                    confirmButtonColor: '#f97316'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred',
                confirmButtonColor: '#f97316'
            });
        });
}

function cancelBooking(bookingId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'cancel');
            formData.append('booking_id', bookingId);
            
            fetch('../handlers/booking_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cancelled!',
                        text: 'Your booking has been cancelled.',
                        confirmButtonColor: '#f97316'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message,
                        confirmButtonColor: '#f97316'
                    });
                }
            });
        }
    });
}

function closeEditModal() {
    document.getElementById('editBookingModal').classList.add('hidden');
}

// Add event listener for form submission
document.getElementById('editBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const bookingId = document.getElementById('edit_booking_id').value;
    const checkIn = document.getElementById('edit_check_in').value;
    const checkOut = document.getElementById('edit_check_out').value;
    const adults = document.getElementById('edit_adults').value;
    const kids = document.getElementById('edit_kids').value;
    const requests = document.getElementById('edit_requests').value;
    
    // Validate dates
    if (new Date(checkOut) <= new Date(checkIn)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Dates',
            text: 'Check-out date must be after check-in date',
            confirmButtonColor: '#f97316'
        });
        return;
    }
    
    // Show loading indicator
    Swal.fire({
        title: 'Updating booking...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'edit_booking');
    formData.append('booking_id', bookingId);
    formData.append('check_in', checkIn);
    formData.append('check_out', checkOut);
    formData.append('adults', adults);
    formData.append('kids', kids);
    formData.append('requests', requests);
    
    // Send request
    fetch('../handlers/booking_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your booking has been updated successfully.',
                confirmButtonColor: '#f97316'
            }).then(() => {
                closeEditModal();
                location.reload(); // Refresh to show updated booking
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to update booking',
                confirmButtonColor: '#f97316'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred',
            confirmButtonColor: '#f97316'
        });
    });
});


function updateStatus(id, status) {
    const statusLabels = {
        'pending': 'Pending',
        'confirmed': 'Confirmed',
        'cancelled': 'Cancelled',
        'completed': 'Completed'
    };
    
    Swal.fire({
        title: `Mark as ${statusLabels[status]}?`,
        text: `Are you sure you want to change the booking status to ${statusLabels[status]}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, update status',
        confirmButtonColor: '#f97316',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Updating Status',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Create FormData instead of JSON
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('id', id);
            formData.append('status', status);

            // Send status update to server
            fetch('../handlers/booking_handler.php', {
    method: 'POST',
    body: formData
})
.then(response => response.text()) // <- get raw response first
.then(text => {
    console.log('Raw response:', text);
    const data = JSON.parse(text); // now manually parse
    if (data.status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Status Updated',
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
    console.error('Error parsing or fetching:', error);
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

function recordPayment(bookingId) {
    Swal.fire({
        title: 'Record Payment',
        html: `
            <form id="paymentForm" action="../handlers/payment_handler.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="booking_id" value="${bookingId}">
                <input type="hidden" name="redirect_url" value="../manager/booking_details.php?id=${bookingId}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount ($)</label>
                    <input type="number" name="amount" id="payment_amount" class="swal2-input w-full" step="0.01" min="0.01" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="swal2-input w-full" required>
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                    <input type="text" name="reference_number" id="reference_number" class="swal2-input w-full">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" id="payment_date" class="swal2-input w-full" value="${new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="payment_notes" class="swal2-textarea w-full"></textarea>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Record Payment',
        confirmButtonColor: '#f97316',
        preConfirm: () => {
            // Validate amount
            const amount = document.getElementById('payment_amount').value;
            if (!amount || parseFloat(amount) <= 0) {
                Swal.showValidationMessage('Please enter a valid payment amount');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('paymentForm').submit();
        }
    });
}


function addService(bookingId) {
    // Fetch available services
    fetch('../handlers/service_handler.php?action=get_all')
        .then(response => response.json())
        .then(services => {
            let serviceOptions = '';
            services.forEach(service => {
                serviceOptions += `<option value="${service.id}" data-price="${service.price}">${service.name} ($${service.price})</option>`;
            });

            Swal.fire({
                title: 'Add Service to Booking',
                html: `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                        <select id="service_id" class="swal2-input w-full" onchange="updateServicePrice()">
                            <option value="">Select a service</option>
                            ${serviceOptions}
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                        <input type="number" id="service_price" class="swal2-input w-full" step="0.01" min="0">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" id="service_date" class="swal2-input w-full" value="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea id="service_notes" class="swal2-textarea w-full"></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Add Service',
                confirmButtonColor: '#f97316',
                didOpen: () => {
                    // Add function to update price when service is selected
                    window.updateServicePrice = function() {
                        const select = document.getElementById('service_id');
                        const option = select.options[select.selectedIndex];
                        if (option && option.dataset.price) {
                            document.getElementById('service_price').value = option.dataset.price;
                        }
                    };
                },
                preConfirm: () => {
                    return {
                        booking_id: bookingId,
                        service_id: document.getElementById('service_id').value,
                        price: document.getElementById('service_price').value,
                        service_date: document.getElementById('service_date').value,
                        notes: document.getElementById('service_notes').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Validate service selection
                    if (!result.value.service_id) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Service Required',
                            text: 'Please select a service',
                            confirmButtonColor: '#f97316'
                        });
                        return;
                    }
                    
                    // Show loading state
                    Swal.fire({
                        title: 'Adding Service',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send service data to server
                    fetch('../handlers/booking_service_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'add',
                            ...result.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Service Added',
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
        });
}

function removeService(serviceId) {
    Swal.fire({
        title: 'Remove Service',
        text: 'Are you sure you want to remove this service from the booking?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove it',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Removing Service',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send delete request to server
            fetch('../handlers/booking_service_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete',
                    id: serviceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Service Removed',
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

// Functions for check-in/check-out actions
function processCheckin(bookingId) {
    Swal.fire({
        title: 'Process Check-in',
        text: 'Confirm that the guest is checking in now?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, check in',
        confirmButtonColor: '#10b981',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing Check-in',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send check-in request to server
            fetch('../handlers/booking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'checkin',
                    id: bookingId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Check-in Successful',
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

function earlyCheckout(bookingId) {
    Swal.fire({
        title: 'Process Early Check-out',
        text: 'Confirm that the guest is checking out early?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, check out',
        confirmButtonColor: '#3b82f6',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing Check-out',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send check-out request to server
            fetch('../handlers/booking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'checkout',
                    id: bookingId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Check-out Successful',
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

function extendStay(bookingId) {
    // Fetch booking details first to get current check-out date
    fetch(`../handlers/booking_handler.php?action=get&id=${bookingId}`)
        .then(response => response.json())
        .then(booking => {
            const currentCheckout = booking.check_out;
            
            Swal.fire({
                title: 'Extend Stay',
                html: `
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Check-out Date</label>
                        <input type="date" id="current_checkout" class="swal2-input w-full" value="${currentCheckout}" disabled>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Check-out Date</label>
                        <input type="date" id="new_checkout" class="swal2-input w-full" min="${currentCheckout}">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Extend Stay',
                confirmButtonColor: '#f97316',
                preConfirm: () => {
                    return {
                        id: bookingId,
                        new_checkout: document.getElementById('new_checkout').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Validate new checkout date
                    if (!result.value.new_checkout) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Date Required',
                            text: 'Please select a new check-out date',
                            confirmButtonColor: '#f97316'
                        });
                        return;
                    }
                    
                    // Show loading state
                    Swal.fire({
                        title: 'Extending Stay',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send extend stay request to server
                    fetch('../handlers/booking_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'extend_stay',
                            ...result.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Stay Extended',
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
        });
}

function modifyDates(bookingId) {
    // Fetch booking details first to get current dates
    fetch(`../handlers/booking_handler.php?action=get&id=${bookingId}`)
        .then(response => response.json())
        .then(booking => {
            const currentCheckin = booking.check_in;
            const currentCheckout = booking.check_out;
            
            Swal.fire({
                title: 'Modify Booking Dates',
                html: `
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Check-in Date</label>
                            <input type="date" id="new_checkin" class="swal2-input w-full" value="${currentCheckin}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Check-out Date</label>
                            <input type="date" id="new_checkout" class="swal2-input w-full" value="${currentCheckout}">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update Dates',
                confirmButtonColor: '#f97316',
                preConfirm: () => {
                    return {
                        id: bookingId,
                        new_checkin: document.getElementById('new_checkin').value,
                        new_checkout: document.getElementById('new_checkout').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Validate dates
                    const newCheckin = new Date(result.value.new_checkin);
                    const newCheckout = new Date(result.value.new_checkout);
                    
                    if (newCheckout <= newCheckin) {
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
                        title: 'Updating Dates',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send modify dates request to server
                    fetch('../handlers/booking_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'modify_dates',
                            ...result.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dates Updated',
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
        });
}

function viewFeedback(bookingId) {
    // Fetch feedback for this booking
    fetch(`../handlers/feedback_handler.php?action=get&booking_id=${bookingId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.feedback) {
                const feedback = data.feedback;
                const stars = '★'.repeat(feedback.rating) + '☆'.repeat(5 - feedback.rating);
                
                Swal.fire({
                    title: 'Guest Feedback',
                    html: `
                        <div class="text-center mb-4">
                            <div class="text-2xl text-amber-500">${stars}</div>
                            <div class="text-gray-600">${feedback.rating}/5 rating</div>
                        </div>
                        <div class="text-left p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-700">${feedback.comment || 'No comment provided'}</p>
                        </div>
                        <div class="mt-4 text-gray-500 text-sm">
                            Submitted on ${new Date(feedback.created_at).toLocaleDateString()}
                        </div>
                    `,
                    confirmButtonColor: '#f97316'
                });
            } else {
                Swal.fire({
                    title: 'No Feedback',
                    text: 'No feedback has been submitted for this booking yet.',
                    icon: 'info',
                    confirmButtonColor: '#f97316'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while fetching feedback.',
                confirmButtonColor: '#f97316'
            });
        });
}

function restoreBooking(bookingId) {
    Swal.fire({
        title: 'Restore Booking',
        text: 'Are you sure you want to restore this cancelled booking?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, restore it',
        confirmButtonColor: '#f97316',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Restoring Booking',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send restore request to server
            fetch('../handlers/booking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'restore',
                    id: bookingId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Restored',
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

// Print booking receipt
function printReceipt(bookingId) {
    window.open(`booking_receipt.php?id=${bookingId}`, '_blank');
}

// Send booking confirmation email
function sendConfirmationEmail(bookingId) {
    Swal.fire({
        title: 'Send Confirmation Email',
        text: 'Send a booking confirmation email to the guest?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, send email',
        confirmButtonColor: '#f97316',
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Sending Email',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send email request to server
            fetch('../handlers/email_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_booking_confirmation',
                    booking_id: bookingId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Sent',
                        text: data.message,
                        confirmButtonColor: '#f97316'
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
