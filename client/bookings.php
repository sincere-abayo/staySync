<?php
require_once '../includes/session.php';
require_once '../includes/session_timeout.php';
require_once '../config/database.php';
check_session_timeout();
if (!is_logged_in()) {
    header('Location: ../login.html');
    exit;
}

$user_id = get_user_id();

include 'includes/sidebar.php';
?>




    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-teal-950 mb-8">My Bookings</h1>
<?php
 // Display session messages
 if (isset($_SESSION['payment_message'])) {
    $message_type = $_SESSION['payment_message']['type'];
    $message_text = $_SESSION['payment_message']['text'];
    
    $bg_color = $message_type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700';
    
    echo "<div class='$bg_color border-l-4 p-4 mb-6' role='alert'>";
    echo "<p>$message_text</p>";
    echo "</div>";
    
    unset($_SESSION['payment_message']);
}

?>
        <!-- Booking Status Tabs -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="filterBookings('all')" 
                            class="tab-btn active mr-8 py-4 px-1 border-b-2 font-medium text-sm">
                        All Bookings
                    </button>
                    <button onclick="filterBookings('pending')" 
                            class="tab-btn mr-8 py-4 px-1 border-b-2 font-medium text-sm">
                        Pending
                    </button>
                    <button onclick="filterBookings('confirmed')" 
                            class="tab-btn mr-8 py-4 px-1 border-b-2 font-medium text-sm">
                        Confirmed
                    </button>
                    <button onclick="filterBookings('completed')" 
                            class="tab-btn mr-8 py-4 px-1 border-b-2 font-medium text-sm">
                        Completed
                    </button>
                    <button onclick="filterBookings('cancelled')" 
                            class="tab-btn py-4 px-1 border-b-2 font-medium text-sm">
                        Cancelled
                    </button>
                </nav>
            </div>
        </div>

       <!-- Bookings List -->
<div class="space-y-6">
    <?php
    $bookings = $conn->query("
        SELECT b.*, r.room_type, r.image, r.price, r.room_number, r.floor_number, 
               r.capacity, r.size, u.name, u.email, u.phone,
               DATEDIFF(b.check_out, b.check_in) as nights
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN users u ON b.user_id = u.id
        WHERE b.user_id = $user_id
        ORDER BY b.created_at DESC
    ");

    while($booking = $bookings->fetch_assoc()):
        $total = $booking['price'] * $booking['nights'];
        $tax = $total * 0.1;
        $grand_total = $total + $tax;
    ?>
        <div class="booking-card bg-white rounded-lg shadow-md overflow-hidden" 
             data-status="<?php echo $booking['booking_status']; ?>">
            <!-- Main Booking Info -->
            <div class="md:flex">
                <div class="md:w-1/3">
                    <img src="../<?php echo $booking['image']; ?>" 
                         alt="<?php echo $booking['room_type']; ?>"
                         class="h-48 w-full object-cover md:h-full">
                </div>
                <div class="p-6 md:w-2/3">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-teal-950"><?php echo $booking['room_type']; ?></h3>
                            <p class="text-gray-600">Adults number: #<?php echo $booking['adults']; ?></p>
                            <p class="text-gray-600">Kids number: #<?php echo $booking['kids']; ?></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            <?php
                            switch($booking['booking_status']) {
                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                            }
                            ?>">
                            <?php echo ucfirst($booking['booking_status']); ?>
                        </span>
                    </div>

                    <!-- Booking Details Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-gray-600">Check-in</p>
                            <p class="font-medium"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Check-out</p>
                            <p class="font-medium"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Room Number</p>
                            <p class="font-medium"><?php echo $booking['room_number']; ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Floor</p>
                            <p class="font-medium"><?php echo $booking['floor_number']; ?></p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Room Rate (<?php echo $booking['nights']; ?> nights)</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Tax (10%)</span>
                            <span>$<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="flex justify-between font-bold text-amber-500">
                            <span>Total</span>
                            <span>$<?php echo number_format($grand_total, 2); ?></span>
                        </div>
                        Countdown Timer & Reminders Display:
<div class="booking-status-tracker">
    <?php
    $today = new DateTime();
    $check_in = new DateTime($booking['check_in']);
    $interval = $today->diff($check_in);
    $days_remaining = $interval->days;
    ?>
    
    <?php if($booking['booking_status'] == 'confirmed' && $check_in > $today): ?>
        <div class="bg-blue-50 p-4 rounded-lg">
            <h4 class="font-medium text-blue-800">
                <i class="fas fa-clock mr-2"></i>
                Check-in in <?php echo $days_remaining; ?> days
            </h4>
            <div class="mt-2 space-y-2">
                <button onclick="viewRoomFeatures(<?php echo $booking['room_id']; ?>)" 
                        class="text-sm text-blue-600 hover:underline">
                    View Room Features
                </button>
                <button onclick="requestSupport(<?php echo $booking['id']; ?>)" 
                        class="text-sm text-blue-600 hover:underline">
                    Need Assistance?
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>
                        <!-- Add this after the Payment Summary section -->
                <?php if($booking['booking_status'] == 'pending' && $booking['payment_status'] == 'pending'): ?>
                <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg mb-4">
                <div class="flex items-center justify-between">
                <div>
                <h4 class="font-medium text-amber-800">Payment Required</h4>
                <p class="text-sm text-amber-600">50% advance payment: $<?php echo number_format($grand_total * 0.5, 2); ?></p>
                </div>
                <button onclick="processPayment(<?php echo $booking['id']; ?>, <?php echo $grand_total * 0.5; ?>)"
                    class="bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600">
                Pay Now
                </button>
        </div>
    </div>
<?php endif; ?>

                    </div>
<!-- Add after the Payment Summary section -->
<?php if($booking['booking_status'] == 'confirmed' || $booking['payment_status'] == 'partial'): 
    // Get payment details
    $payment_query = $conn->query("
        SELECT * FROM payments 
        WHERE booking_id = {$booking['id']} 
        ORDER BY payment_date DESC 
        LIMIT 1
    ");
    $payment = $payment_query->fetch_assoc();
    $payment_details = json_decode($payment['payment_details'], true);
?>
    <div class="bg-green-50 border border-green-200 p-4 rounded-lg mb-4">
        <h4 class="font-medium text-green-800 mb-2">Payment Information</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-600">Payment Method</p>
                <p class="font-medium"><?php 
    // First decode the JSON payment details
    $payment_details = json_decode($payment['payment_details'], true);
    
    // Check payment method and display appropriate details
    if ($payment['payment_method'] === 'card') {
        echo 'Card ending in ' . (isset($payment_details['card_number']) ? substr($payment_details['card_number'], -4) : 'XXXX');
    } else if ($payment['payment_method'] === 'mobile_money') {
        echo (isset($payment_details['network']) ? $payment_details['network'] : 'Mobile Money') . 
             ' (' . (isset($payment_details['phone_number']) ? $payment_details['phone_number'] : 'N/A') . ')';
    } else {
        // For other payment methods like cash, bank transfer, etc.
        echo ucfirst(str_replace('_', ' ', $payment['payment_method']));
        if (isset($payment_details['reference']) && !empty($payment_details['reference'])) {
            echo ' (Ref: ' . $payment_details['reference'] . ')';
        }
    }
?></p>

            </div>
            <div>
                <p class="text-gray-600">Transaction ID</p>
                <p class="font-medium"><?php echo $payment['transaction_id']; ?></p>
            </div>
            <div>
                <p class="text-gray-600">Amount Paid</p>
                <p class="font-medium text-green-600">$<?php echo number_format($payment['amount'], 2); ?></p>
            </div>
            <div>
                <p class="text-gray-600">Payment Date</p>
                <p class="font-medium"><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <?php if($booking['booking_status'] == 'pending'): ?>
                            <button onclick="cancelBooking(<?php echo $booking['id']; ?>)"
                                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                Cancel Booking
                            </button>
                        <?php endif; ?>
                        
                        <?php if($booking['booking_status'] == 'completed'): ?>
                            <button onclick="addReview(<?php echo $booking['id']; ?>)"
                                    class="px-4 py-2 bg-teal-950 text-white rounded hover:bg-amber-500">
                                Add Review
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

    </div>


    
<!-- Add this before closing body tag -->
<div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg max-w-md mx-auto mt-20 p-6">
        <h3 class="text-xl font-bold text-teal-950 mb-4">Write a Review</h3>
        <form id="reviewForm">
            <input type="hidden" id="booking_id" name="booking_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                <div class="flex space-x-2" id="ratingStars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="far fa-star text-amber-500 cursor-pointer text-2xl" 
                           onclick="setRating(<?php echo $i; ?>)"></i>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="rating" name="rating">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                <textarea id="review_text" name="review_text" rows="4" required
                          class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-amber-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeReviewModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-teal-950 text-white rounded hover:bg-amber-500">
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>
<!-- swal cdn -->
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function filterBookings(status) {
            document.querySelectorAll('.booking-card').forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-amber-500', 'text-amber-500');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            event.target.classList.add('border-amber-500', 'text-amber-500');
            event.target.classList.remove('border-transparent', 'text-gray-500');
        }

        function cancelBooking(bookingId) {
    Swal.fire({
        title: 'Cancel Booking',
        text: 'Are you sure you want to cancel this booking?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../handlers/booking_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=cancel&booking_id=${bookingId}`
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

   

function addReview(bookingId) {
    document.getElementById('booking_id').value = bookingId;
    document.getElementById('reviewModal').classList.remove('hidden');
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    document.getElementById('reviewForm').reset();
    resetStars();
} 

function setRating(rating) {
    document.getElementById('rating').value = rating;
    const stars = document.querySelectorAll('#ratingStars i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('far');
            star.classList.add('fas');
        } else {
            star.classList.remove('fas');
            star.classList.add('far');
        }
    });
}

function resetStars() {
    const stars = document.querySelectorAll('#ratingStars i');
    stars.forEach(star => {
        star.classList.remove('fas');
        star.classList.add('far');
    });
    document.getElementById('rating').value = '';
}

document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'add_review');
    
    fetch('../handlers/review_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Thank You!',
                text: 'Your review has been submitted successfully.',
                confirmButtonColor: '#f97316'
            }).then(() => {
                closeReviewModal();
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
});


// function processPayment(bookingId, amount) {
//     Swal.fire({
//         title: 'Select Payment Method',
//         html: `
//             <div class="grid grid-cols-2 gap-4 mb-4">
//                 <button onclick="showCardPayment(${bookingId}, ${amount})" 
//                         class="p-4 border rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500">
//                     <i class="fas fa-credit-card text-2xl mb-2 text-amber-500"></i>
//                     <p class="font-medium">Card Payment</p>
//                 </button>
//                 <button onclick="showMobileMoneyPayment(${bookingId}, ${amount})" 
//                         class="p-4 border rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-amber-500">
//                     <i class="fas fa-mobile-alt text-2xl mb-2 text-amber-500"></i>
//                     <p class="font-medium">Mobile Money</p>
//                 </button>
//             </div>
//         `,
//         showConfirmButton: false,
//         showCloseButton: true
//     });
// }


function showCardPayment(bookingId, amount) {
    const cardForm = () => Swal.fire({
        title: 'Card Payment',
        html: `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                    <input type="text" id="card_number" class="swal2-input" 
                           value="${document.getElementById('card_number')?.value || ''}"
                           placeholder="1234 5678 9012 3456"
                           autocomplete="cc-number"
                           maxlength="16">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="text" id="expiry_date" class="swal2-input" 
                               value="${document.getElementById('expiry_date')?.value || ''}"
                               placeholder="MM/YY"
                               autocomplete="cc-exp"
                               maxlength="5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                        <input type="text" id="cvv" class="swal2-input" 
                               value="${document.getElementById('cvv')?.value || ''}"
                               placeholder="123"
                               autocomplete="cc-csc"
                               maxlength="3">
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: `Pay $${amount}`,
        confirmButtonColor: '#f97316',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return submitPayment(bookingId, amount, 'card')
                .catch(error => {
                    Swal.showValidationMessage(error.message);
                    return false;
                });
        }
    }).then((result) => {
        if (result.isDismissed && result.dismiss === 'error') {
            cardForm(); // Reopen form with existing data
        }
    });

    cardForm();
}

function showMobileMoneyPayment(bookingId, amount) {
    Swal.fire({
        title: 'Mobile Money Payment',
        html: `
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="phone_number" class="swal2-input" placeholder="Enter Mobile Money number">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Network</label>
                    <select id="network" class="swal2-input">
                        <option value="mtn">MTN Mobile Money</option>
                        <option value="airtel">Airtel Money</option>
                        <option value="vodafone">Vodafone Cash</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: `Pay $${amount}`,
        confirmButtonColor: '#f97316',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            submitPayment(bookingId, amount, 'mobile_money');
        }
    });
}

// view room Features
function processPayment(bookingId, amount) {
    // First show the payment form modal
    Swal.fire({
        title: 'Payment Details',
        html: `
            <form id="paymentForm" action="../handlers/payment_handler.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="booking_id" value="${bookingId}">
                <input type="hidden" name="amount" value="${amount}">
                <input type="hidden" name="redirect_url" value="${window.location.href}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="swal2-input w-full" onchange="togglePaymentFields()">
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                    <input type="text" id="reference_number" name="reference_number" class="swal2-input w-full" placeholder="Transaction reference (if applicable)">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date</label>
                    <input type="date" id="payment_date" name="payment_date" class="swal2-input w-full" value="${new Date().toISOString().split('T')[0]}">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" class="swal2-textarea w-full" placeholder="Additional payment details"></textarea>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Process Payment',
        confirmButtonColor: '#f97316',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            // Validate form
            const paymentMethod = document.getElementById('payment_method').value;
            const paymentDate = document.getElementById('payment_date').value;
            
            if (!paymentMethod || !paymentDate) {
                Swal.showValidationMessage('Please fill all required fields');
                return false;
            }
            
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form
            document.getElementById('paymentForm').submit();
        }
    });
}

//  submitPayment function that matches the cancelBooking format
function submitPayment(bookingId, amount, paymentMethod, paymentDetails) {
    // Show loading state
    Swal.fire({
        title: 'Processing Payment',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Construct URL-encoded body string
    let bodyData = `action=process_payment&booking_id=${bookingId}&amount=${amount}&payment_method=${paymentMethod}`;
    
    // Add payment-specific data
    if (paymentMethod === 'card') {
        bodyData += `&card_number=${paymentDetails.cardNumber}&expiry_date=${paymentDetails.expiryDate}&cvv=${paymentDetails.cvv}`;
    } else {
        bodyData += `&phone_number=${paymentDetails.phoneNumber}&network=${paymentDetails.network}`;
    }
    
    fetch('../handlers/booking_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: bodyData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Payment Successful',
                text: 'Your booking has been confirmed!',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Payment Failed',
                text: data.message || 'An error occurred while processing your payment.',
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


function viewRoomFeatures(roomId) {
    fetch(`../handlers/room_handler.php?action=get_features&room_id=${roomId}`)
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            title: 'Room Features',
            html: `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium">Amenities</h4>
                            <ul class="list-disc pl-4">
                                ${data.amenities.map(a => `<li>${a}</li>`).join('')}
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium">Services</h4>
                            <ul class="list-disc pl-4">
                                ${data.services.map(s => `<li>${s}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h4 class="font-medium">Special Instructions</h4>
                        <p>${data.instructions}</p>
                    </div>
                </div>
            `
        });
    });
}

// request requestSupport
function requestSupport(bookingId) {
    Swal.fire({
        title: 'Contact Support',
        html: `
            <div class="space-y-4">
                <select id="support_type" class="swal2-input">
                    <option value="general">General Inquiry</option>
                    <option value="checkin">Check-in Assistance</option>
                    <option value="special">Special Requests</option>
                    <option value="modification">Booking Modification</option>
                </select>
                <textarea id="support_message" class="swal2-textarea" 
                          placeholder="How can we help you?"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Request',
        preConfirm: () => {
            return submitSupportRequest(bookingId);
        }
    });
}

</script>

    <?php include 'includes/footer.php'; ?>

