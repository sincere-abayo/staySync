<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
include_once './database.php';
require_once './includes/session.php';
 $user_role =  get_user_role();

// Get room ID from URL
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no valid ID provided, redirect to rooms page
if (!$room_id) {
    header('Location: rooms.php');
    exit;
}

// Get room details
$stmt = $conn->prepare("
    SELECT r.*
    FROM rooms r
    WHERE r.id = ?
");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

// If room not found, redirect to rooms page
if ($result->num_rows === 0) {
    header('Location: rooms.php');
    exit;
}

$room = $result->fetch_assoc();

// Get room images
$images_query = $conn->prepare("
    SELECT * FROM room_images 
    WHERE room_id = ? 
    ORDER BY is_primary DESC, id ASC
");
$images_query->bind_param("i", $room_id);
$images_query->execute();
$images_result = $images_query->get_result();

// Get room reviews
$reviews_query = $conn->prepare("
    SELECT r.*, u.name as guest_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.booking_id IN (SELECT id FROM bookings WHERE room_id = ?)
    ORDER BY r.created_at DESC
    LIMIT 5
");
$reviews_query->bind_param("i", $room_id);
$reviews_query->execute();
$reviews_result = $reviews_query->get_result();

// Calculate average rating
$avg_rating_query = $conn->prepare("
    SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
    FROM reviews r
    JOIN bookings b ON r.booking_id = b.id
    WHERE b.room_id = ?
");
$avg_rating_query->bind_param("i", $room_id);
$avg_rating_query->execute();
$rating_data = $avg_rating_query->get_result()->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$review_count = $rating_data['review_count'] ?? 0;

// Get similar rooms
$similar_rooms_query = $conn->prepare("
    SELECT r.*, 
    (SELECT image_path FROM room_images WHERE room_id = r.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM rooms r
    WHERE r.room_type = ? AND r.id != ? AND r.status = 'available'
    LIMIT 3
");
$similar_rooms_query->bind_param("si", $room['room_type'], $room_id);
$similar_rooms_query->execute();
$similar_rooms_result = $similar_rooms_query->get_result();

// Parse amenities - using the amenities field from the rooms table
$amenities = [];
if (!empty($room['amenities'])) {
    // Check if it's JSON
    $decoded = json_decode($room['amenities'], true);
    if (is_array($decoded)) {
        $amenities = $decoded;
    } else {
        // If not JSON, assume it's comma-separated
        $amenities = explode(',', $room['amenities']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($room['room_type']); ?> Room - StaySync Hotel</title>
    <title>All Rooms - StaySync Hotel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<section class="bg-teal-950">


      <nav class="flex flex-col md:flex-row justify-between shadow-sm shadow-amber-500 items-center lg:px-20 md:px-10 px-5 py-4"> 
          <div class="flex justify-between items-center w-full md:w-auto">
              <img src="images/logo.png" alt="" class="w-20">
              <button class="md:hidden text-white text-2xl" onclick="toggleMenu()">
                  <i class="fas fa-bars"></i>
              </button>
          </div>

          <ul id="mobileMenu" class="hidden md:flex flex-col md:flex-row gap-4 md:gap-10 text-center mb-4 md:mb-0 w-full md:w-auto">
              <li>
                  <a href="" class="text-xl text-white font-normal hover:border-b-2 hover:border-amber-500 px-3 py-1 block" >Home</a>
              </li>
              <li>
                  <a href="services.php" class="text-xl text-white font-normal hover:border-b-2 hover:border-amber-500 px-3 py-1 block" >Service</a>
              </li>
              <li>
                  <a href="rooms.php" class="text-xl text-white font-normal hover:border-b-2 hover:border-amber-500 px-3 py-1 block" >Rooms</a>
              </li>
              <li>
                  <a href="gallery.php" class="text-xl text-white font-normal hover:border-b-2 hover:border-amber-500 px-3 py-1 block" >Gallery</a>
              </li>
          </ul>

          <div id="mobileButtons" class="hidden gap-5 md:flex flex-col md:flex-row gap-4 w-full md:w-auto">
           <a href="login.html" class="w-full md:w-auto">
              <button class="bg-amber-500 px-10 py-2 text-xl font-medium rounded-md w-full">Login</button>
           </a>
           <a href="register.html" class="w-full md:w-auto">
              <button class="bg-white px-10 py-2 text-xl text-teal-950 md:mt-0 mt-2 font-medium rounded-md w-full">Register</button>
           </a>
          </div>
      </nav>
      <script>
          function toggleMenu() {
              const menu = document.getElementById('mobileMenu');
              const buttons = document.getElementById('mobileButtons');
              menu.classList.toggle('hidden');
              buttons.classList.toggle('hidden');
          }
      </script>
    <!-- Room Details Section -->
    <section class="py-12">
    <div class="container mx-auto px-4">
            <!-- Breadcrumbs -->
            <div class="flex items-center text-sm text-gray-600 mb-12">
                <a href="index.php" class="hover:text-amber-500">Home</a>
                <span class="mx-2">/</span>
                <a href="rooms.php" class="hover:text-amber-500">Rooms</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900"><?php echo htmlspecialchars($room['room_type']); ?> Room</span>
            </div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Room Images Gallery -->
                <div class="relative bg-gray-200 h-96 overflow-hidden">
    <?php if ($images_result->num_rows > 0): ?>
        <div class="swiper roomGallery h-full">
            <div class="swiper-wrapper">
                <?php while ($image = $images_result->fetch_assoc()): ?>
                    <div class="swiper-slide">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($room['room_type']); ?> Room" 
                             class="w-full h-full object-cover"
                             loading="eager">
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    <?php else: ?>
        <div class="h-full">
            <?php if (!empty($room['image'])): ?>
                <img src="<?php echo htmlspecialchars($room['image']); ?>" 
                     alt="<?php echo htmlspecialchars($room['room_type']); ?> Room" 
                     class="w-full h-full object-cover"
                     loading="eager">
            <?php else: ?>
                <div class="flex items-center justify-center h-full bg-gray-300">
                    <span class="text-gray-500 text-lg">No images available</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>


                <!-- Room Details -->
                <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-teal-950 mb-2"><?php echo htmlspecialchars($room['room_type']); ?> Room</h1>
            <div class="flex items-center mb-2">
                <div class="flex text-amber-500 mr-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= floor($avg_rating)): ?>
                            <i class="fas fa-star"></i>
                        <?php elseif ($i - 0.5 <= $avg_rating): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php else: ?>
                            <i class="far fa-star"></i>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <span class="text-gray-600"><?php echo $avg_rating; ?> (<?php echo $review_count; ?> reviews)</span>
            </div>
            <p class="text-gray-600">Room <?php echo htmlspecialchars($room['room_number']); ?> • Floor <?php echo htmlspecialchars($room['floor_number'] ?? '1'); ?></p>
        </div>
        <div class="mt-4 md:mt-0">
    <div class="text-3xl font-bold text-amber-500 mb-2">$<?php echo number_format($room['price'], 2); ?> <span class="text-gray-500 text-lg font-normal">/night</span></div>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <!-- User is logged in, show direct booking button -->
        <a href="client/rooms.php" class="block bg-amber-500 text-white text-center px-6 py-3 rounded-lg font-semibold hover:bg-amber-600 transition-colors">
            Book This Room
        </a>
    <?php else: ?>
        <!-- User is not logged in, show login required message -->
        <a href="login.html" class="block bg-amber-500 text-white text-center px-6 py-3 rounded-lg font-semibold hover:bg-amber-600 transition-colors">
            Login to Book
        </a>
        <p class="text-sm text-gray-600 mt-2 text-center">Login is required before booking a room</p>
    <?php endif; ?>
</div>

    </div>

    <!-- Room Description -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-teal-950 mb-4">Room Description</h2>
        <p class="text-gray-700 leading-relaxed">
            <?php echo nl2br(htmlspecialchars($room['description'] ?: 'Experience comfort and luxury in our ' . $room['room_type'] . ' room. This spacious accommodation offers all the amenities you need for a pleasant stay.')); ?>
        </p>
    </div>

    <!-- Room Features -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <!-- Room Details -->
        <div>
            <h2 class="text-2xl font-bold text-teal-950 mb-4">Room Details</h2>
            <ul class="space-y-3">
                <li class="flex items-center">
                    <i class="fas fa-ruler-combined text-amber-500 w-6"></i>
                    <span class="ml-2">Size: <?php echo $room['size']; ?> m²</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-user-friends text-amber-500 w-6"></i>
                    <span class="ml-2">Capacity: <?php echo $room['capacity']; ?> Guests</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-bed text-amber-500 w-6"></i>
                    <span class="ml-2">Bed: <?php echo htmlspecialchars($room['bed_config'] ?? 'King Size Bed'); ?></span>
                </li>
                <?php if ($room['view_type']): ?>
                <li class="flex items-center">
                    <i class="fas fa-mountain text-amber-500 w-6"></i>
                    <span class="ml-2">View: <?php echo htmlspecialchars($room['view_type']); ?></span>
                </li>
                <?php endif; ?>
                <?php if ($room['is_accessible']): ?>
                <li class="flex items-center">
                    <i class="fas fa-wheelchair text-amber-500 w-6"></i>
                    <span class="ml-2">Accessible Room</span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
                    <!-- Policies -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-teal-950 mb-4">Policies</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-teal-950 mb-2">Check-in</h3>
                                <p class="text-gray-700">From 2:00 PM</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-teal-950 mb-2">Check-out</h3>
                                <p class="text-gray-700">Until 12:00 PM</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-teal-950 mb-2">Cancellation</h3>
                                <p class="text-gray-700">Free cancellation up to 24 hours before check-in</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold text-teal-950">Guest Reviews</h2>
                            <!-- <a href="#" class="text-amber-500 hover:text-amber-600">View all reviews</a> -->
                        </div>
                        
                        <?php if ($reviews_result->num_rows > 0): ?>
                            <div class="space-y-6">
                                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                                    <div class="border-b pb-6">
                                        <div class="flex justify-between mb-2">
                                            <div>
                                                <h3 class="font-semibold text-teal-950"><?php echo htmlspecialchars($review['guest_name']); ?></h3>
                                                <p class="text-gray-500 text-sm"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></p>
                                            </div>
                                            <div class="flex text-amber-500">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $review['rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-gray-700"><?php echo htmlspecialchars($review['review_text'] ?? $review['comment']); ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-gray-50 p-6 rounded-lg text-center">
                                <p class="text-gray-600 mb-4">No reviews yet for this room.</p>
                                <!-- <p class="text-gray-600">Be the first to share your experience after your stay!</p> -->
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Rooms Section -->
    <?php if ($similar_rooms_result->num_rows > 0): ?>
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-teal-950 mb-8 text-center">Similar Rooms You May Like</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($similar = $similar_rooms_result->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform hover:scale-105">
                        <div class="h-48 overflow-hidden">
                            <?php if ($similar['primary_image']): ?>
                                <img src="<?php echo htmlspecialchars($similar['primary_image']); ?>" alt="<?php echo htmlspecialchars($similar['room_type']); ?>" class="w-full h-full object-cover">
                            <?php elseif ($similar['image']): ?>
                                <img src="<?php echo htmlspecialchars($similar['image']); ?>" alt="<?php echo htmlspecialchars($similar['room_type']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <img src="assets/images/room-placeholder.jpg" alt="Room" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-teal-950"><?php echo htmlspecialchars($similar['room_type']); ?> Room</h3>
                                <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    Room <?php echo htmlspecialchars($similar['room_number']); ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($similar['description'] ?: 'Experience comfort and luxury in our ' . $similar['room_type'] . ' room.'); ?></p>
                            
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    <i class="fas fa-ruler-combined mr-1"></i> <?php echo $similar['size']; ?> m²
                                </span>
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    <i class="fas fa-user mr-1"></i> <?php echo $similar['capacity']; ?> Guests
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-2xl font-bold text-amber-500">$<?php echo number_format($similar['price'], 2); ?></span>
                                    <span class="text-gray-500 text-sm">/night</span>
                                </div>
                                <a href="room-details.php?id=<?php echo $similar['id']; ?>" class="bg-teal-950 text-white px-4 py-2 rounded-lg hover:bg-amber-500 transition-colors">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Call to Action -->
    <section class="bg-teal-950 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Book Your Stay?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Experience luxury, comfort, and exceptional service at StaySync Hotel. Book your room today and create unforgettable memories.</p>
            <a href="booking.php?room_id=<?php echo $room_id; ?>" class="bg-amber-500 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-amber-600 transition-colors">
                Book Now
            </a>
        </div>
    </section>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Swiper
            const swiper = new Swiper('.roomGallery', {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                autoplay: {
                    delay: 5000,
                },
            });
        });
    </script>
</body>
</html>
