<?php
require_once '../includes/session.php';
require_once '../config/database.php';
check_login();

// Default period is last 30 days
$period = isset($_GET['period']) ? $_GET['period'] : '30days';

// Set date range based on period
$end_date = date('Y-m-d');
switch ($period) {
    case '7days':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $period_label = 'Last 7 Days';
        break;
    case '30days':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $period_label = 'Last 30 Days';
        break;
    case '90days':
        $start_date = date('Y-m-d', strtotime('-90 days'));
        $period_label = 'Last 3 Months';
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-1 year'));
        $period_label = 'Last Year';
        break;
    case 'custom':
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $period_label = 'Custom Range';
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $period_label = 'Last 30 Days';
}

// Get revenue summary
$revenue_query = $conn->prepare("
    SELECT 
        SUM(p.amount) as total_revenue,
        COUNT(DISTINCT b.id) as total_bookings,
        AVG(r.price) as average_rate
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN rooms r ON b.room_id = r.id
    WHERE p.payment_date BETWEEN ? AND ?
");
$revenue_query->bind_param("ss", $start_date, $end_date);
$revenue_query->execute();
$revenue_data = $revenue_query->get_result()->fetch_assoc();

// Calculate occupancy rate
$occupancy_query = $conn->prepare("
    SELECT 
        COUNT(*) as booked_days,
        (SELECT COUNT(*) FROM rooms) * DATEDIFF(?, ?) as total_room_days
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE 
        (b.check_in BETWEEN ? AND ? OR b.check_out BETWEEN ? AND ?)
        AND b.booking_status IN ('confirmed', 'completed')
");
$occupancy_query->bind_param("ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
$occupancy_query->execute();
$occupancy_data = $occupancy_query->get_result()->fetch_assoc();
$occupancy_rate = ($occupancy_data['total_room_days'] > 0) ? 
    round(($occupancy_data['booked_days'] / $occupancy_data['total_room_days']) * 100) : 0;

// Get monthly data for charts
$monthly_query = $conn->prepare("
    SELECT 
        DATE_FORMAT(p.payment_date, '%Y-%m') as month,
        DATE_FORMAT(p.payment_date, '%b') as month_name,
        SUM(p.amount) as revenue
    FROM payments p
    WHERE p.payment_date BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
    ORDER BY month
");
$monthly_query->bind_param("ss", $start_date, $end_date);
$monthly_query->execute();
$monthly_result = $monthly_query->get_result();

$months = [];
$revenues = [];
while ($row = $monthly_result->fetch_assoc()) {
    $months[] = $row['month_name'];
    $revenues[] = $row['revenue'];
}

// Get occupancy data by month
$occupancy_monthly_query = $conn->prepare("
    SELECT 
        DATE_FORMAT(b.check_in, '%Y-%m') as month,
        DATE_FORMAT(b.check_in, '%b') as month_name,
        COUNT(*) as bookings,
        (SELECT COUNT(*) FROM rooms) as total_rooms,
        COUNT(*) / (SELECT COUNT(*) FROM rooms) * 100 as occupancy_rate
    FROM bookings b
    WHERE b.check_in BETWEEN ? AND ?
    AND b.booking_status IN ('confirmed', 'completed')
    GROUP BY DATE_FORMAT(b.check_in, '%Y-%m')
    ORDER BY month
");
$occupancy_monthly_query->bind_param("ss", $start_date, $end_date);
$occupancy_monthly_query->execute();
$occupancy_monthly_result = $occupancy_monthly_query->get_result();

$occupancy_months = [];
$occupancy_rates = [];
while ($row = $occupancy_monthly_result->fetch_assoc()) {
    $occupancy_months[] = $row['month_name'];
    $occupancy_rates[] = $row['occupancy_rate'];
}

// Get detailed statistics by month
$detailed_stats_query = $conn->prepare("
    SELECT 
        DATE_FORMAT(b.check_in, '%Y-%m') as month,
        DATE_FORMAT(b.check_in, '%b') as month_name,
        COUNT(DISTINCT b.id) as total_bookings,
        SUM(p.amount) as revenue,
        COUNT(*) / (SELECT COUNT(*) FROM rooms) * 100 as occupancy_rate,
        AVG(IFNULL(f.rating, 0)) as average_rating
    FROM bookings b
    LEFT JOIN payments p ON b.id = p.booking_id
    LEFT JOIN feedback f ON b.id = f.booking_id
    WHERE b.check_in BETWEEN ? AND ?
    GROUP BY DATE_FORMAT(b.check_in, '%Y-%m')
    ORDER BY month
");
$detailed_stats_query->bind_param("ss", $start_date, $end_date);
$detailed_stats_query->execute();
$detailed_stats_result = $detailed_stats_query->get_result();

// Include header
include_once 'includes/header.php';

// Include sidebar
include_once 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Add html2pdf library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex-1 overflow-auto p-6" id="reportContent">
        <!-- Report Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-teal-950">Hotel Performance Reports</h2>
            <!-- <div>
                <button onclick="generatePDF()" class="bg-teal-950 text-white px-4 py-2 rounded-lg hover:bg-teal-900 mr-2">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
                <button onclick="printReport()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div> -->
        </div>

        <!-- Report Period Selector -->
        <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
            <form action="" method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Period</label>
                    <select name="period" class="border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-950" onchange="toggleCustomDates(this.value)">
                        <option value="7days" <?php echo $period === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30days" <?php echo $period === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90days" <?php echo $period === '90days' ? 'selected' : ''; ?>>Last 3 Months</option>
                        <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>Last Year</option>
                        <option value="custom" <?php echo $period === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                    </select>
                </div>
                
                <div id="customDateFields" class="<?php echo $period === 'custom' ? 'flex' : 'hidden'; ?> gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-950">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-950">
                    </div>
                </div>
                
                <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                    Generate Report
                </button>
            </form>
        </div>

        <!-- Current Report Period Banner -->
        <div class="bg-teal-50 border-l-4 border-teal-950 p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-calendar-alt text-teal-950 text-xl mr-3"></i>
                <div>
                    <h3 class="font-semibold text-teal-950">Report Period: <?php echo $period_label; ?></h3>
                    <p class="text-sm text-gray-600">
                        <?php echo date('F j, Y', strtotime($start_date)); ?> - 
                        <?php echo date('F j, Y', strtotime($end_date)); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Revenue Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Total Revenue</h3>
                <div class="flex items-center">
                    <i class="fas fa-dollar-sign text-green-600 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-green-600">
                        $<?php echo number_format($revenue_data['total_revenue'] ?? 0, 2); ?>
                    </span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Average Daily Rate</h3>
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-teal-950 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-teal-950">
                        $<?php echo number_format($revenue_data['average_rate'] ?? 0, 2); ?>
                    </span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Occupancy Rate</h3>
                <div class="flex items-center">
                    <i class="fas fa-bed text-amber-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-amber-500">
                        <?php echo $occupancy_rate; ?>%
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Chart -->
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-teal-950 mb-4">Revenue Trends</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            <!-- Occupancy Chart -->
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-teal-950 mb-4">Occupancy Rates</h3>
                <canvas id="occupancyChart"></canvas>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <table class="min-w-full">
                <thead class="bg-teal-950 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left">Month</th>
                        <th class="px-6 py-3 text-left">Total Bookings</th>
                        <th class="px-6 py-3 text-left">Revenue</th>
                        <th class="px-6 py-3 text-left">Occupancy Rate</th>
                        <th class="px-6 py-3 text-left">Average Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($detailed_stats_result->num_rows > 0): ?>
                        <?php while ($row = $detailed_stats_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><?php echo $row['month_name']; ?></td>
                                <td class="px-6 py-4"><?php echo $row['total_bookings']; ?></td>
                                <td class="px-6 py-4">$<?php echo number_format($row['revenue'], 2); ?></td>
                                <td class="px-6 py-4"><?php echo round($row['occupancy_rate']); ?>%</td>
                                <td class="px-6 py-4">
                                    <?php 
                                        $rating = $row['average_rating'];
                                        if ($rating > 0) {
                                            echo number_format($rating, 1) . '/5';
                                        } else {
                                            echo 'No ratings';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No data available for the selected period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Additional Reports Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Top Performing Rooms -->
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-teal-950 mb-4">Top Performing Rooms</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Room</th>
                                <th class="px-4 py-2 text-left">Bookings</th>
                                <th class="px-4 py-2 text-left">Revenue</th>
                                <th class="px-4 py-2 text-left">Occupancy</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            // Get top performing rooms
                            $top_rooms_query = $conn->prepare("
                                SELECT 
                                    r.id, r.room_number, r.room_type,
                                    COUNT(b.id) as booking_count,
                                    SUM(p.amount) as revenue,
                                    COUNT(b.id) / DATEDIFF(?, ?) * 100 as occupancy_rate
                                FROM rooms r
                                LEFT JOIN bookings b ON r.id = b.room_id AND b.check_in BETWEEN ? AND ?
                                LEFT JOIN payments p ON b.id = p.booking_id
                                GROUP BY r.id
                                ORDER BY revenue DESC
                                LIMIT 5
                            ");
                            $top_rooms_query->bind_param("ssss", $start_date, $end_date, $start_date, $end_date);
                            $top_rooms_query->execute();
                            $top_rooms_result = $top_rooms_query->get_result();
                            
                            if ($top_rooms_result->num_rows > 0):
                                while ($room = $top_rooms_result->fetch_assoc()):
                            ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <div class="font-medium"><?php echo $room['room_number']; ?></div>
                                        <div class="text-xs text-gray-500"><?php echo $room['room_type']; ?></div>
                                    </td>
                                    <td class="px-4 py-2"><?php echo $room['booking_count']; ?></td>
                                    <td class="px-4 py-2">$<?php echo number_format($room['revenue'], 2); ?></td>
                                    <td class="px-4 py-2"><?php echo round($room['occupancy_rate']); ?>%</td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-center text-gray-500">No data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Customer Demographics -->
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-teal-950 mb-4">Booking Sources</h3>
                <canvas id="bookingSourcesChart"></canvas>
                <?php
                // This would ideally come from a booking_sources table or field
                // For now, we'll use dummy data
                $booking_sources = [
                    'Direct Website' => 45,
                    'Online Travel Agencies' => 30,
                    'Phone Reservations' => 15,
                    'Walk-ins' => 10
                ];
                ?>
            </div>
        </div>
    </div>

    <script>
        // Toggle custom date fields
        function toggleCustomDates(value) {
            const customFields = document.getElementById('customDateFields');
            if (value === 'custom') {
                customFields.classList.remove('hidden');
                customFields.classList.add('flex');
            } else {
                customFields.classList.add('hidden');
                customFields.classList.remove('flex');
            }
        }
        
        // Generate PDF
        function generatePDF() {
            const element = document.getElementById('reportContent');
            const opt = {
                margin: 10,
                filename: 'hotel_performance_report.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Remove buttons for PDF
            const buttonsContainer = element.querySelector('div:nth-child(1) > div');
            const originalContent = buttonsContainer.innerHTML;
            buttonsContainer.innerHTML = '';
            
            html2pdf().set(opt).from(element).save().then(() => {
                // Restore buttons after PDF generation
                buttonsContainer.innerHTML = originalContent;
            });
        }
        
        // Print report
        function printReport() {
            window.print();
        }

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Revenue',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: 'rgb(13, 148, 136)',
                    backgroundColor: 'rgba(13, 148, 136, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Occupancy Chart
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        new Chart(occupancyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($occupancy_months); ?>,
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: <?php echo json_encode($occupancy_rates); ?>,
                    backgroundColor: 'rgb(245, 158, 11)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });

        // Booking Sources Chart
        const sourcesCtx = document.getElementById('bookingSourcesChart').getContext('2d');
        new Chart(sourcesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($booking_sources)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($booking_sources)); ?>,
                    backgroundColor: [
                        'rgb(13, 148, 136)',
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)',
                        'rgb(139, 92, 246)'
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
