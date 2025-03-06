
<?php
function sendCheckInReminder($booking) {
    $to = $booking['email'];
    $subject = "Check-in Reminder for Tomorrow - Room {$booking['room_number']}";
    
    $message = "
    <html>
    <body>
        <h2>Check-in Reminder</h2>
        <p>Dear {$booking['name']},</p>
        <p>This is a reminder that your check-in is scheduled for tomorrow at our hotel.</p>
        
        <h3>Booking Details:</h3>
        <ul>
            <li>Room Number: {$booking['room_number']}</li>
            <li>Check-in Date: {$booking['check_in']}</li>
            <li>Check-out Date: {$booking['check_out']}</li>
        </ul>
        
        <p>Please bring a valid ID for check-in.</p>
        <p>We look forward to welcoming you!</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Stay Sync Hotel <noreply@staysync.com>\r\n";
    
    return mail($to, $subject, $message, $headers);
}
