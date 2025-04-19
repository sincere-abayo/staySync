<?php
require_once '../includes/session.php';
require_once '../includes/session_timeout.php';
require_once '../config/database.php';

check_login();
error_reporting(E_ALL);
ini_set('display_errors', 1);
$client_id  = get_user_id();
$client = $conn->query("SELECT * FROM users WHERE id = $client_id")->fetch_assoc();

// Get or initialize loyalty points
$loyalty_query = $conn->query("SELECT points FROM loyalty_points WHERE user_id = $client_id");
if ($loyalty_query->num_rows === 0) {
    // Initialize points for new user
    $conn->query("INSERT INTO loyalty_points (user_id, points) VALUES ($client_id, 0)");
    $points = 0;
} else {
    $loyalty_points = $loyalty_query->fetch_assoc();
    $points = $loyalty_points['points'];
}

// Get booking statistics
$stats = $conn->query("SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as active_bookings,
    SUM(r.price * DATEDIFF(b.check_out, b.check_in)) as total_spent
    FROM bookings b
    LEFT JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = $client_id")->fetch_assoc();

    
// Get active bookings
$active_bookings = $conn->query("
    SELECT b.*, r.room_type, r.image, r.price
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = $client_id 
    AND b.booking_status = 'confirmed'
    ORDER BY b.check_in DESC
    LIMIT 2
");


// Get past bookings
$past_bookings = $conn->query("
    SELECT b.*, r.room_type, r.image
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = $client_id 
    AND b.booking_status = 'completed'
    ORDER BY b.check_out DESC
    LIMIT 1
");
include_once 'includes/sidebar.php';
?>

        <!-- Quick Stats -->
        <h3 class="text-xl font-semibold text-teal-950 mb-4">Booking Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Total Bookings</h3>
                <p class="text-3xl font-bold text-amber-500"><?php echo $stats['total_bookings']?? 0; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Active Bookings</h3>
                <p class="text-3xl font-bold text-amber-500"><?php echo $stats['active_bookings'] ?? 0; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Loyalty Points</h3>
                <p class="text-3xl font-bold text-amber-500"><?php echo $points ?? 0; ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-lg font-semibold text-teal-950 mb-2">Total Spent</h3>
                <p class="text-3xl font-bold text-amber-500">$<?php echo $stats['total_spent'] ?? 0; ?></p>
            </div>
        </div>

        <!-- Active Bookings Section -->
<div class="mb-8">
    <h2 class="text-2xl font-bold mb-4 text-teal-950">Your Active Bookings</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <?php
        if($active_bookings->num_rows > 0):
        while($booking = $active_bookings->fetch_assoc()): ?>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-teal-950"><?php echo $booking['room_type']; ?></h3>
                        <p class="text-gray-600">Booking ID: #<?php echo $booking['id']; ?></p>
                        <p class="text-gray-600">Check-in: <?php echo date('d M Y', strtotime($booking['check_in'])); ?></p>
                        <p class="text-gray-600">Check-out: <?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
                        <p class="text-amber-500 font-bold mt-2">$<?php echo number_format($booking['price'], 2); ?>/night</p>
                    </div>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                        <?php echo ucfirst($booking['booking_status']); ?>
                    </span>
                </div>
                <div class="flex justify-end">
                    <button onclick="cancelBooking(<?php echo $booking['id']; ?>)" 
                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Cancel Booking
                    </button>
                </div>
            </div>
        <?php endwhile;
        else: ?>
        <p class="text-gray-600">No active bookings found.</p>
        <?php endif; ?>
    </div>
</div>


       <!-- Past Bookings Section -->
<div class="mb-8">
    <h2 class="text-2xl font-bold mb-4 text-teal-950">Past Bookings</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <?php 
        $past_bookings = $conn->query("
            SELECT b.*, r.room_type, r.image, r.price
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.user_id = $client_id 
            AND b.booking_status = 'completed'
            ORDER BY b.check_out DESC
            LIMIT 2
        ");
        if($past_bookings->num_rows > 0):
        while($booking = $past_bookings->fetch_assoc()): 
            $nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24);
            $total = $booking['price'] * $nights;
        ?>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-semibold text-teal-950"><?php echo $booking['room_type']; ?></h3>
                        <p class="text-gray-600">Booking ID: #<?php echo $booking['id']; ?></p>
                        <p class="text-gray-600">Stay: <?php echo date('d M Y', strtotime($booking['check_in'])); ?> - <?php echo date('d M Y', strtotime($booking['check_out'])); ?></p>
                        <p class="text-gray-600">Duration: <?php echo $nights; ?> nights</p>
                        <p class="text-amber-500 font-bold mt-2">Total Paid: $<?php echo number_format($total, 2); ?></p>
                    </div>
                    <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">Completed</span>
                </div>
                
                <!-- Rating Section -->
                <div class="mt-4 border-t pt-4">
                    <h4 class="font-semibold mb-2 text-teal-950">Rate Your Stay</h4>
                    <div class="flex space-x-2 mb-2" id="stars_<?php echo $booking['id']; ?>">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="far fa-star text-amber-500 cursor-pointer hover:text-amber-600" 
                               onclick="rateBooking(<?php echo $booking['id']; ?>, <?php echo $i; ?>)"></i>
                        <?php endfor; ?>
                    </div>
                    <textarea 
                        id="review_<?php echo $booking['id']; ?>"
                        placeholder="Share your experience..." 
                        class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500"
                        rows="3"
                    ></textarea>
                    <button onclick="submitReview(<?php echo $booking['id']; ?>)" 
                            class="mt-2 bg-teal-950 text-white px-4 py-2 rounded hover:bg-amber-500 transition-colors">
                        Submit Review
                    </button>
                </div>
            </div>
        <?php endwhile;
        else: ?>
        <p class="text-gray-600">No past bookings found.</p>
        <?php endif; ?>
    </div>
</div>


      
    </div>

    <?php include 'includes/footer.php'; ?>