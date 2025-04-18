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
        </div>

        <!-- Report Period Selector -->
        <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
            <div class="flex gap-4">
                <select class="border p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-950">
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                    <option>Last 3 Months</option>
                    <option>Last Year</option>
                </select>
                <button class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                    Generate Report
                </button>
            </div>
        </div>

        <!-- Revenue Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Total Revenue</h3>
                <div class="flex items-center">
                    <i class="fas fa-dollar-sign text-green-600 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-green-600">$45,250</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Average Daily Rate</h3>
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-teal-950 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-teal-950">$299</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-gray-500 mb-2">Occupancy Rate</h3>
                <div class="flex items-center">
                    <i class="fas fa-bed text-amber-500 text-2xl mr-3"></i>
                    <span class="text-2xl font-bold text-amber-500">75%</span>
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
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">January</td>
                        <td class="px-6 py-4">156</td>
                        <td class="px-6 py-4">$30,250</td>
                        <td class="px-6 py-4">65%</td>
                        <td class="px-6 py-4">4.5/5</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">February</td>
                        <td class="px-6 py-4">178</td>
                        <td class="px-6 py-4">$35,420</td>
                        <td class="px-6 py-4">70%</td>
                        <td class="px-6 py-4">4.7/5</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">March</td>
                        <td class="px-6 py-4">205</td>
                        <td class="px-6 py-4">$42,150</td>
                        <td class="px-6 py-4">75%</td>
                        <td class="px-6 py-4">4.8/5</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">April</td>
                        <td class="px-6 py-4">198</td>
                        <td class="px-6 py-4">$45,300</td>
                        <td class="px-6 py-4">80%</td>
                        <td class="px-6 py-4">4.6/5</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">May</td>
                        <td class="px-6 py-4">212</td>
                        <td class="px-6 py-4">$48,750</td>
                        <td class="px-6 py-4">78%</td>
                        <td class="px-6 py-4">4.9/5</td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">June</td>
                        <td class="px-6 py-4">189</td>
                        <td class="px-6 py-4">$45,250</td>
                        <td class="px-6 py-4">75%</td>
                        <td class="px-6 py-4">4.7/5</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Monthly Revenue',
                    data: [30000, 35000, 42000, 45000, 48000, 45250],
                    borderColor: 'rgb(13, 148, 136)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Occupancy Chart
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        new Chart(occupancyCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Occupancy Rate (%)',
                    data: [65, 70, 75, 80, 78, 75],
                    backgroundColor: 'rgb(245, 158, 11)',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    </script>
</body>
</html>
