<?php
// ERROR REPORTING
// error rpoerting 
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
include_once './database.php';
require_once './includes/session.php';

// Get filter parameters
$room_type = isset($_GET['type']) ? $_GET['type'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
$capacity = isset($_GET['capacity']) ? (int)$_GET['capacity'] : 0;
$view_type = isset($_GET['view']) ? $_GET['view'] : '';
$is_accessible = isset($_GET['accessible']) ? (int)$_GET['accessible'] : -1;

// Build the query with filters
$query = "SELECT r.*, 
          (SELECT image_path FROM room_images WHERE room_id = r.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM rooms r 
          WHERE r.status = 'available'";

// Apply filters
if (!empty($room_type)) {
    $query .= " AND r.room_type = '" . $conn->real_escape_string($room_type) . "'";
}
if ($min_price > 0) {
    $query .= " AND r.price >= " . $min_price;
}
if ($max_price < 10000) {
    $query .= " AND r.price <= " . $max_price;
}
if ($capacity > 0) {
    $query .= " AND r.capacity >= " . $capacity;
}
if (!empty($view_type)) {
    $query .= " AND r.view_type = '" . $conn->real_escape_string($view_type) . "'";
}
if ($is_accessible != -1) {
    $query .= " AND r.is_accessible = " . $is_accessible;
}

$query .= " ORDER BY r.price ASC";

// Execute the query
$result = $conn->query($query);

// Get unique room types, view types for filters
$room_types = $conn->query("SELECT DISTINCT room_type FROM rooms ORDER BY room_type");
$view_types = $conn->query("SELECT DISTINCT view_type FROM rooms WHERE view_type IS NOT NULL ORDER BY view_type");

// Get min and max prices for the price slider
$price_range = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM rooms")->fetch_assoc();
$db_min_price = $price_range['min_price'] ?? 0;
$db_max_price = $price_range['max_price'] ?? 1000;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <!-- Hero Section -->
    <section class="bg-teal-950 text-white py-12 md:py-20">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-1/2">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Explore Our Rooms</h1>
                <p class="text-xl mb-8">Find the perfect accommodation for your stay</p>
            </div>
            <div class="md:w-1/2 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Book Your Stay?</h2>
                <p class="text-xl mb-8 max-w-3xl mx-auto">Experience luxury, comfort, and exceptional service at StaySync Hotel. Book your room today and create unforgettable memories.</p>
                <a href="booking.php" class="bg-amber-500 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-amber-600 transition-colors">
                    Book Now
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Filters Sidebar -->
                <div class="lg:w-1/4">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
                        <h2 class="text-2xl font-bold text-teal-950 mb-6">Filters</h2>
                        
                        <form action="" method="GET" class="space-y-6">
                            <!-- Room Type Filter -->
                            <div>
                                <h3 class="text-lg font-semibold text-teal-950 mb-3">Room Type</h3>
                                <div class="space-y-2">
                                    <div>
                                        <input type="radio" id="all_types" name="type" value="" <?php echo empty($room_type) ? 'checked' : ''; ?> class="mr-2">
                                        <label for="all_types">All Types</label>
                                    </div>
                                    <?php while ($type = $room_types->fetch_assoc()): ?>
                                        <div>
                                            <input type="radio" id="type_<?php echo htmlspecialchars($type['room_type']); ?>" 
                                                name="type" value="<?php echo htmlspecialchars($type['room_type']); ?>" 
                                                <?php echo $room_type === $type['room_type'] ? 'checked' : ''; ?> class="mr-2">
                                            <label for="type_<?php echo htmlspecialchars($type['room_type']); ?>"><?php echo htmlspecialchars($type['room_type']); ?></label>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            
                            <!-- Price Range Filter -->
                            <div>
                                <h3 class="text-lg font-semibold text-teal-950 mb-3">Price Range</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="min_price" class="block text-sm text-gray-600 mb-1">Min Price ($)</label>
                                        <input type="number" id="min_price" name="min_price" min="<?php echo $db_min_price; ?>" 
                                            max="<?php echo $db_max_price; ?>" value="<?php echo $min_price; ?>" 
                                            class="w-full border rounded-lg p-2">
                                    </div>
                                    <div>
                                        <label for="max_price" class="block text-sm text-gray-600 mb-1">Max Price ($)</label>
                                        <input type="number" id="max_price" name="max_price" min="<?php echo $db_min_price; ?>" 
                                            max="<?php echo $db_max_price; ?>" value="<?php echo $max_price ?: $db_max_price; ?>" 
                                            class="w-full border rounded-lg p-2">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Capacity Filter -->
                            <div>
                                <h3 class="text-lg font-semibold text-teal-950 mb-3">Capacity</h3>
                                <select name="capacity" class="w-full border rounded-lg p-2">
                                    <option value="0" <?php echo $capacity == 0 ? 'selected' : ''; ?>>Any</option>
                                    <option value="1" <?php echo $capacity == 1 ? 'selected' : ''; ?>>1+ Person</option>
                                    <option value="2" <?php echo $capacity == 2 ? 'selected' : ''; ?>>2+ People</option>
                                    <option value="3" <?php echo $capacity == 3 ? 'selected' : ''; ?>>3+ People</option>
                                    <option value="4" <?php echo $capacity == 4 ? 'selected' : ''; ?>>4+ People</option>
                                    <option value="5" <?php echo $capacity == 5 ? 'selected' : ''; ?>>5+ People</option>
                                </select>
                            </div>
                            
                            <!-- View Type Filter -->
                            <div>
                                <h3 class="text-lg font-semibold text-teal-950 mb-3">View</h3>
                                <div class="space-y-2">
                                    <div>
                                        <input type="radio" id="all_views" name="view" value="" <?php echo empty($view_type) ? 'checked' : ''; ?> class="mr-2">
                                        <label for="all_views">All Views</label>
                                    </div>
                                    <?php while ($view = $view_types->fetch_assoc()): ?>
                                        <div>
                                            <input type="radio" id="view_<?php echo htmlspecialchars($view['view_type']); ?>" 
                                                name="view" value="<?php echo htmlspecialchars($view['view_type']); ?>" 
                                                <?php echo $view_type === $view['view_type'] ? 'checked' : ''; ?> class="mr-2">
                                            <label for="view_<?php echo htmlspecialchars($view['view_type']); ?>"><?php echo htmlspecialchars($view['view_type']); ?> View</label>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            
                            <!-- Accessibility Filter -->
                            <div>
                                <h3 class="text-lg font-semibold text-teal-950 mb-3">Accessibility</h3>
                                <div class="space-y-2">
                                    <div>
                                        <input type="radio" id="all_access" name="accessible" value="-1" <?php echo $is_accessible == -1 ? 'checked' : ''; ?> class="mr-2">
                                        <label for="all_access">All Rooms</label>
                                    </div>
                                    <div>
                                        <input type="radio" id="accessible" name="accessible" value="1" <?php echo $is_accessible == 1 ? 'checked' : ''; ?> class="mr-2">
                                        <label for="accessible">Accessible Rooms</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Apply Filters Button -->
                            <div>
                                <button type="submit" class="w-full bg-amber-500 text-white py-2 rounded-lg hover:bg-amber-600 transition-colors">
                                    Apply Filters
                                </button>
                            </div>
                            
                            <!-- Reset Filters Link -->
                            <div class="text-center">
                                <a href="rooms.php" class="text-teal-950 hover:text-amber-500">Reset Filters</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Rooms Grid -->
                <div class="lg:w-3/4">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php while ($room = $result->fetch_assoc()): ?>
                                <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform hover:scale-105">
                                    <div class="h-48 overflow-hidden">
                                        <?php if ($room['primary_image']): ?>
                                            <img src="<?php echo htmlspecialchars($room['primary_image']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" class="w-full h-full object-cover">
                                        <?php elseif (isset($room['image']) && $room['image']): ?>
                                            <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <img src="images/swimming.jpg" alt="Room" class="w-full h-full object-cover">
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-xl font-bold text-teal-950"><?php echo htmlspecialchars($room['room_type']); ?> Room</h3>
                                            <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                Room <?php echo htmlspecialchars($room['room_number']); ?>
                                            </span>
                                        </div>
                                        
                                        <p class="text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($room['description'] ?: 'Experience comfort and luxury in our ' . $room['room_type'] . ' room.'); ?></p>
                                        
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                <i class="fas fa-ruler-combined mr-1"></i> <?php echo $room['size']; ?> m²
                                            </span>
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                <i class="fas fa-user mr-1"></i> <?php echo $room['capacity']; ?> Guests
                                            </span>
                                            <?php if (isset($room['view_type']) && $room['view_type']): ?>
                                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                    <i class="fas fa-mountain mr-1"></i> <?php echo htmlspecialchars($room['view_type']); ?> View
                                                </span>
                                            <?php endif; ?>
                                            <?php if (isset($room['is_accessible']) && $room['is_accessible']): ?>
                                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                    <i class="fas fa-wheelchair mr-1"></i> Accessible
                                                </span>
                                            <?php endif; ?>
                                            </div>
                                        
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="text-2xl font-bold text-amber-500">$<?php echo number_format($room['price'], 2); ?></span>
                                                <span class="text-gray-500 text-sm">/night</span>
                                            </div>
                                            <a href="room-details.php?id=<?php echo $room['id']; ?>" class="bg-teal-950 text-white px-4 py-2 rounded-lg hover:bg-amber-500 transition-colors">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- No results message (hidden when results exist) -->
                        <div class="hidden">
                            <div class="text-center py-12">
                                <i class="fas fa-bed text-gray-300 text-5xl mb-4"></i>
                                <h3 class="text-2xl font-bold text-gray-700 mb-2">No Rooms Found</h3>
                                <p class="text-gray-500 mb-6">Try adjusting your filters to find available rooms.</p>
                                <a href="rooms.php" class="bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600 transition-colors">
                                    Reset Filters
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No results message (shown when no results) -->
                        <div class="text-center py-12">
                            <i class="fas fa-bed text-gray-300 text-5xl mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-700 mb-2">No Rooms Found</h3>
                            <p class="text-gray-500 mb-6">Try adjusting your filters to find available rooms.</p>
                            <a href="rooms.php" class="bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600 transition-colors">
                                Reset Filters
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-teal-950 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Book Your Stay?</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Experience luxury, comfort, and exceptional service at StaySync Hotel. Book your room today and create unforgettable memories.</p>
            <a href="booking.php" class="bg-amber-500 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-amber-600 transition-colors">
                Book Now
            </a>
        </div>
    </section>

    <script>
        // Price range validation
        document.addEventListener('DOMContentLoaded', function() {
            const minPriceInput = document.getElementById('min_price');
            const maxPriceInput = document.getElementById('max_price');
            
            // Ensure max price is always >= min price
            minPriceInput.addEventListener('change', function() {
                if (parseInt(minPriceInput.value) > parseInt(maxPriceInput.value)) {
                    maxPriceInput.value = minPriceInput.value;
                }
            });
            
            // Ensure min price is always <= max price
            maxPriceInput.addEventListener('change', function() {
                if (parseInt(maxPriceInput.value) < parseInt(minPriceInput.value)) {
                    minPriceInput.value = maxPriceInput.value;
                }
            });
        });
    </script>
</body>
</html>
