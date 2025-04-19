<?php
require_once '../includes/session.php';
require_once '../config/database.php';
check_login();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$client_id = $_SESSION['user_id'];

// Get date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 year'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get booking statistics
$booking_stats = $conn->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
        SUM(DATEDIFF(check_out, check_in)) as total_nights,
        AVG(DATEDIFF(check_out, check_in)) as avg_stay_length
    FROM bookings
    WHERE user_id = ? 
    AND created_at BETWEEN ? AND ?
");
$booking_stats->bind_param("iss", $client_id, $start_date, $end_date);
$booking_stats->execute();
$stats = $booking_stats->get_result()->fetch_assoc();

// Get payment statistics
$payment_stats = $conn->prepare("
    SELECT 
        SUM(p.amount) as total_spent,
        COUNT(p.id) as payment_count,
        AVG(p.amount) as avg_payment
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    WHERE b.user_id = ?
    AND p.payment_date BETWEEN ? AND ?
");
$payment_stats->bind_param("iss", $client_id, $start_date, $end_date);
$payment_stats->execute();
$payments = $payment_stats->get_result()->fetch_assoc();

// Get room type distribution
$room_types = $conn->prepare("
    SELECT 
        r.room_type,
        COUNT(*) as booking_count,
        SUM(DATEDIFF(b.check_out, b.check_in)) as nights_stayed
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    AND b.created_at BETWEEN ? AND ?
    GROUP BY r.room_type
    ORDER BY booking_count DESC
");
$room_types->bind_param("iss", $client_id, $start_date, $end_date);
$room_types->execute();
$room_distribution = $room_types->get_result();

// Get monthly booking trends
$monthly_trends = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as booking_count,
        SUM(DATEDIFF(check_out, check_in)) as nights_booked
    FROM bookings
    WHERE user_id = ?
    AND created_at BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");
$monthly_trends->bind_param("iss", $client_id, $start_date, $end_date);
$monthly_trends->execute();
$trends = $monthly_trends->get_result();

// Get current loyalty points
$loyalty_query = $conn->prepare("SELECT points FROM loyalty_points WHERE user_id = ?");
$loyalty_query->bind_param("i", $client_id);
$loyalty_query->execute();
$loyalty_result = $loyalty_query->get_result();
$loyalty_points = $loyalty_result->num_rows > 0 ? $loyalty_result->fetch_assoc()['points'] : 0;


// Include sidebar
include_once 'includes/sidebar.php';
?>

        <h1 class="text-3xl font-bold text-teal-950 mb-6">Your Booking Reports</h1>
        
        <!-- Date Range Filter -->
        <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
            <form action="" method="GET" class="flex flex-col md:flex-row items-end space-y-4 md:space-y-0 md:space-x-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" 
                           class="border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" 
                           class="border rounded-lg p-2 focus:ring-2 focus:ring-amber-500">
                </div>
                <div>
                    <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                        Apply Filter
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-gray-500 text-sm">Total Bookings</h3>
                <div class="flex items-center mt-2">
                    <i class="fas fa-calendar-check text-amber-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-teal-950"><?php echo $stats['total_bookings'] ?? 0; ?></span>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-gray-500 text-sm">Total Nights Stayed</h3>
                <div class="flex items-center mt-2">
                    <i class="fas fa-moon text-amber-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-teal-950"><?php echo $stats['total_nights'] ?? 0; ?></span>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-gray-500 text-sm">Total Spent</h3>
                <div class="flex items-center mt-2">
                    <i class="fas fa-dollar-sign text-amber-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-teal-950">$<?php echo number_format($payments['total_spent'] ?? 0, 2); ?></span>
                </div>
            </div>
            
            <div class="bg-white p-4 rounded-lg shadow-lg border-t-4 border-amber-500">
                <h3 class="text-gray-500 text-sm">Loyalty Points</h3>
                <div class="flex items-center mt-2">
                    <i class="fas fa-award text-amber-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-teal-950"><?php echo $loyalty_points; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Booking Status Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold text-teal-950 mb-4">Booking Status Distribution</h2>
                <div class="relative" style="height: 250px;">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold text-teal-950 mb-4">Room Type Preferences</h2>
                <div class="relative" style="height: 250px;">
                    <canvas id="roomTypeChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Monthly Booking Trends -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-bold text-teal-950 mb-4">Monthly Booking Trends</h2>
            <div class="relative" style="height: 300px;">
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
        </div>
        
        <!-- Booking Details Table -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
            <h2 class="text-xl font-bold text-teal-950 mb-4">Recent Bookings</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-in</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nights</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        // Get recent bookings
                        $recent_bookings = $conn->prepare("
                            SELECT b.*, r.room_number, r.room_type, r.price
                            FROM bookings b
                            JOIN rooms r ON b.room_id = r.id
                            WHERE b.user_id = ?
                            ORDER BY b.created_at DESC
                            LIMIT 10
                        ");
                        $recent_bookings->bind_param("i", $client_id);
                        $recent_bookings->execute();
                        $bookings_result = $recent_bookings->get_result();
                        
                        if ($bookings_result->num_rows > 0):
                            while ($booking = $bookings_result->fetch_assoc()):
                                $nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24);
                                $total = $booking['price'] * $nights;
                                
                                // Determine status class
                                $status_class = '';
                                switch($booking['booking_status']) {
                                    case 'confirmed':
                                        $status_class = 'bg-green-100 text-green-800';
                                        break;
                                    case 'pending':
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'bg-red-100 text-red-800';
                                        break;
                                    case 'completed':
                                        $status_class = 'bg-blue-100 text-blue-800';
                                        break;
                                    default:
                                        $status_class = 'bg-gray-100 text-gray-800';
                                }
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">#<?php echo $booking['id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($booking['room_type']); ?> (<?php echo $booking['room_number']; ?>)</td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $nights; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">$<?php echo number_format($total, 2); ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No bookings found for the selected period.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($bookings_result->num_rows > 0): ?>
                <div class="mt-4 text-right">
                    <a href="bookings.php" class="text-amber-500 hover:text-amber-600">
                        View All Bookings <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
     
        <!-- Export Options -->
        <!-- <div class="flex justify-end mb-6">
            <button onclick="generatePDF()" class="bg-teal-950 text-white px-4 py-2 rounded-lg hover:bg-teal-900 mr-2">
                <i class="fas fa-file-pdf mr-2"></i> Export as PDF
            </button>
            <button onclick="exportCSV()" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                <i class="fas fa-file-csv mr-2"></i> Export as CSV
            </button>
        </div> -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Booking Status Chart
    const statusCtx = document.getElementById('bookingStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Completed', 'Cancelled', 'Pending'],
            datasets: [{
                data: [
                    <?php echo $stats['confirmed_bookings'] ?? 0; ?>,
                    <?php echo $stats['completed_bookings'] ?? 0; ?>,
                    <?php echo $stats['cancelled_bookings'] ?? 0; ?>,
                    <?php echo ($stats['total_bookings'] ?? 0) - (($stats['confirmed_bookings'] ?? 0) + ($stats['completed_bookings'] ?? 0) + ($stats['cancelled_bookings'] ?? 0)); ?>
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',  // green
                    'rgba(59, 130, 246, 0.7)',  // blue
                    'rgba(239, 68, 68, 0.7)',   // red
                    'rgba(245, 158, 11, 0.7)'   // amber
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    
    // Room Type Chart
    const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
    new Chart(roomTypeCtx, {
        type: 'pie',
        data: {
            labels: [
                <?php 
                $room_distribution->data_seek(0);
                while ($room = $room_distribution->fetch_assoc()) {
                    echo "'" . $room['room_type'] . "', ";
                }
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    $room_distribution->data_seek(0);
                    while ($room = $room_distribution->fetch_assoc()) {
                        echo $room['booking_count'] . ", ";
                    }
                    ?>
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.7)',  // green
                    'rgba(245, 158, 11, 0.7)',  // amber
                    'rgba(59, 130, 246, 0.7)',  // blue
                    'rgba(139, 92, 246, 0.7)',  // purple
                    'rgba(236, 72, 153, 0.7)'   // pink
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
    
    // Monthly Trends Chart
    const trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php 
                $trends->data_seek(0);
                while ($trend = $trends->fetch_assoc()) {
                    echo "'" . date('M Y', strtotime($trend['month'] . '-01')) . "', ";
                }
                ?>
            ],
            datasets: [{
                label: 'Bookings',
                data: [
                    <?php 
                    $trends->data_seek(0);
                    while ($trend = $trends->fetch_assoc()) {
                        echo $trend['booking_count'] . ", ";
                    }
                    ?>
                ],
                backgroundColor: 'rgba(245, 158, 11, 0.7)',
                borderColor: 'rgba(245, 158, 11, 1)',
                borderWidth: 1
            }, {
                label: 'Nights Booked',
                data: [
                    <?php 
                    $trends->data_seek(0);
                    while ($trend = $trends->fetch_assoc()) {
                        echo $trend['nights_booked'] . ", ";
                    }
                    ?>
                ],
                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1,
                type: 'line'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

// Function to generate PDF report
function generatePDF() {
    // Show loading indicator
    const loadingToast = Swal.fire({
        title: 'Generating PDF',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Get the report content
    const element = document.querySelector('.flex-1');
    const reportTitle = document.querySelector('h1').textContent;
    
    // Configure PDF options
    const opt = {
        margin: [10, 10],
        filename: `${reportTitle.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    
    // Generate PDF
    html2pdf().set(opt).from(element).save().then(() => {
        loadingToast.close();
        Swal.fire({
            icon: 'success',
            title: 'PDF Generated',
            text: 'Your report has been downloaded.',
            confirmButtonColor: '#0f766e'
        });
    }).catch(error => {
        loadingToast.close();
        console.error('PDF generation error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to generate PDF. Please try again.',
            confirmButtonColor: '#0f766e'
        });
    });
}

// Function to export data as CSV
function exportCSV() {
    // Show loading indicator
    const loadingToast = Swal.fire({
        title: 'Generating CSV',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Fetch booking data for CSV
    fetch(`../handlers/report_handler.php?action=export_csv&user_id=<?php echo $client_id; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>`)
        .then(response => response.blob())
        .then(blob => {
            loadingToast.close();
            
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `booking_report_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            
            // Clean up
            window.URL.revokeObjectURL(url);
            
            Swal.fire({
                icon: 'success',
                title: 'CSV Generated',
                text: 'Your report has been downloaded.',
                confirmButtonColor: '#0f766e'
            });
        })
        .catch(error => {
            loadingToast.close();
            console.error('CSV generation error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to generate CSV. Please try again.',
                confirmButtonColor: '#0f766e'
            });
        });
}

// Function to toggle sidebar on mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('-translate-x-full');
}

// Handle responsive sidebar
function handleResize() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth < 1024) {
        sidebar.classList.add('fixed', 'h-full', 'z-40', '-translate-x-full');
        sidebar.classList.remove('relative');
    } else {
        sidebar.classList.remove('fixed', 'h-full', 'z-40', '-translate-x-full');
        sidebar.classList.add('relative');
    }
}

window.addEventListener('resize', handleResize);
handleResize();
</script>

<?php include_once 'includes/footer.php'; ?>
