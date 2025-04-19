<?php
require_once '../includes/session.php';
require_once '../config/database.php';
check_login();

// Check if user is requesting their own data
$user_id = $_GET['user_id'] ?? 0;
$logged_in_user = $_SESSION['user_id'];

if ($user_id != $logged_in_user && $_SESSION['user_role'] !== 'manager') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'export_csv':
        // Get date range
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 year'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="booking_report_' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'Booking ID',
            'Room Type',
            'Room Number',
            'Check-in Date',
            'Check-out Date',
            'Nights',
            'Adults',
            'Children',
            'Booking Status',
            'Payment Status',
            'Total Amount',
            'Created Date'
        ]);
        
        // Get booking data
        $stmt = $conn->prepare("
            SELECT b.*, r.room_type, r.room_number, r.price
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            WHERE b.user_id = ?
            AND b.created_at BETWEEN ? AND ?
            ORDER BY b.created_at DESC
        ");
        $stmt->bind_param("iss", $user_id, $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Add data rows
        while ($row = $result->fetch_assoc()) {
            $nights = (strtotime($row['check_out']) - strtotime($row['check_in'])) / (60 * 60 * 24);
            $total = $row['price'] * $nights;
            
            fputcsv($output, [
                $row['id'],
                $row['room_type'],
                $row['room_number'],
                date('Y-m-d', strtotime($row['check_in'])),
                date('Y-m-d', strtotime($row['check_out'])),
                $nights,
                $row['adults'],
                $row['kids'],
                $row['booking_status'],
                $row['payment_status'],
                number_format($total, 2),
                date('Y-m-d', strtotime($row['created_at']))
            ]);
        }
        
        fclose($output);
        exit;
        
    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid action']);
        exit;
}