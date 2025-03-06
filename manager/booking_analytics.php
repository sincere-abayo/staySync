<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Upcoming Check-ins -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Today's Check-ins</h3>
        <?php
        $today_checkins = $conn->query("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE DATE(check_in) = CURDATE() 
            AND booking_status = 'confirmed'
        ")->fetch_assoc();
        ?>
        <p class="text-3xl font-bold"><?php echo $today_checkins['count']; ?></p>
    </div>
    
    <!-- Room Occupancy -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Current Occupancy</h3>
        <?php
        $occupancy = $conn->query("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE check_in <= CURDATE() 
            AND check_out >= CURDATE() 
            AND booking_status = 'confirmed'
        ")->fetch_assoc();
        ?>
        <p class="text-3xl font-bold"><?php echo $occupancy['count']; ?></p>
    </div>
</div>
