<?php
require_once 'database.php';

$room_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$room = $conn->query("SELECT * FROM rooms WHERE id = $room_id")->fetch_assoc();

// Get room images
$images = $conn->query("SELECT * FROM room_images WHERE room_id = $room_id");
$room_images = [];
while($img = $images->fetch_assoc()) {
    $room_images[] = $img['image_path'];
}
// If no additional images, use main room image
if(empty($room_images) && $room['image']) {
    $room_images[] = $room['image'];
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=], initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
    <title><?php echo $room['room_type']; ?> - Stay Sync</title>
</head>

<body>

<section class="lg:mx-20 md:mx-10 mx-5">
        <a href="index.php" class="text-teal-950 hover:text-amber-500">
            <i class="fas fa-arrow-left mr-2"></i>Back to Home
        </a>
        <div class="flex flex-col md:flex-row items-start gap-10 my-10">
            <div class="md:w-[70%] w-full " data-aos="fade-right" data-aos-duration="1000">
                <img id="mainImage" src="<?php echo $room_images[0]; ?>" alt="<?php echo $room['room_type']; ?>" 
                     class="w-full h-[400px] object-cover mb-4 rounded-lg">
                <div class="flex gap-2 overflow-x-auto">
                    <?php foreach($room_images as $index => $image): ?>
                        <img src="<?php echo $image; ?>" 
                             alt="Room View <?php echo $index + 1; ?>" 
                             class="w-24 h-24 cursor-pointer hover:opacity-80 rounded-lg" 
                             onclick="changeImage('<?php echo $image; ?>')">
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="md:w-[30%]" data-aos="fade-left" data-aos-duration="1000">
                <h2 class="text-2xl font-bold mb-4 text-teal-950"><?php echo $room['room_type']; ?></h2>
                <div class="space-y-3">
                    <p class="text-md"><i class="fas fa-bed mr-2 text-amber-500"></i><?php echo $room['bed_config']; ?></p>
                    <p class="text-md"><i class="fas fa-ruler-combined mr-2 text-amber-500"></i><?php echo $room['size']; ?> sq ft</p>
                    <p class="text-md"><i class="fas fa-user-friends mr-2 text-amber-500"></i>Max occupancy: <?php echo $room['capacity']; ?> persons</p>
                    <p class="text-md"><i class="fas fa-mountain mr-2 text-amber-500"></i><?php echo $room['view_type']; ?></p>
                    <?php 
                    $amenities = explode(',', $room['amenities']);
                    foreach($amenities as $amenity): ?>
                        <p class="text-md"><i class="fas fa-check mr-2 text-amber-500"></i><?php echo trim($amenity); ?></p>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-xl font-semibold mb-2 text-teal-950">Description</h3>
                    <p class="text-gray-700"><?php echo $room['description']; ?></p>
                </div>

                <div class="mt-6">
                    <p class="text-lg font-medium text-amber-500">$<?php echo number_format($room['price'], 2); ?>/night</p>
                    <a href="booking.php?room_id=<?php echo $room['id']; ?>" 
                       class="mt-4 block text-center bg-teal-950 text-white px-6 py-2 rounded-lg hover:bg-amber-500 transition-colors duration-300">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="lg:mx-20 md:mx-10 mx-5 my-10">
    <h2 class="text-2xl font-bold mb-6 text-teal-950">Other Available Rooms</h2>
    <div class="grid md:grid-cols-3 gap-6" data-aos="fade-up" data-aos-duration="1000">
        <?php
        $other_rooms = $conn->query("SELECT * FROM rooms WHERE id != $room_id AND status = 'available' LIMIT 3");
        while($other_room = $other_rooms->fetch_assoc()):
            $room_image = $conn->query("SELECT image_path FROM room_images WHERE room_id = {$other_room['id']} AND is_primary = 1 
                                      UNION 
                                      SELECT image_path FROM room_images WHERE room_id = {$other_room['id']} LIMIT 1")->fetch_assoc();
            $image_path = $room_image ? $room_image['image_path'] : ($other_room['image'] ?? 'images/default-room.jpg');
        ?>
            <div class="border rounded-lg overflow-hidden shadow-md">
                <img src="<?php echo $image_path; ?>" 
                     alt="<?php echo $other_room['room_type']; ?>" 
                     class="w-full h-52 object-cover">
                <div class="p-4">
                    <h3 class="text-xl font-semibold text-teal-950"><?php echo $other_room['room_type']; ?></h3>
                    <p class="text-gray-600 mt-2"><?php echo substr($other_room['description'], 0, 100) . '...'; ?></p>
                    <p class="text-amber-500 mt-2">$<?php echo number_format($other_room['price'], 2); ?>/night</p>
                    <a href="room-details.php?id=<?php echo $other_room['id']; ?>" 
                       class="mt-4 block text-center bg-teal-950 text-white px-6 py-2 rounded-lg hover:bg-amber-500 transition-colors duration-300">
                        View Details
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<script src="https://unpkg.com/aos@next/dist/aos.js"></script>

    <script>
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
        }

        AOS.init({
        delay:300,
        duration:1000,
    });
    </script>
</body>
