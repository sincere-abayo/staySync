<?php 
include_once './database.php';
require_once './includes/session.php';
 ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=], initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css">
    <title>Home</title>
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

    <div class="lg:mx-20 md:mx-10 mx-5 mt-20">
    <div class="flex gap-10 flex-col justify-center items-center md:flex-row lg:items-center lg:justify-between">
        <div class="md:w-[45%] w-full lg:py-10 md:py-5 text-center lg:text-left" data-aos="fade-down">
            <h1 class="text-7xl text-white font-medium ">Hotel Room Book</h1>
            <p class="text-amber-500 text-xl my-5 heading">
                Welcome to our luxury hotel experience. We offer smart
                 amenities and great value for your stay. 
                Our dedicated staff ensures your 
            </p>

            <div class="my-5">
                <button class="bg-amber-500 text-teal-950 py-2 px-10 rounded-3xl text-lg italic
                hover:shadow-sm hover:shadow-amber-400 hover:border-2 hover:border-amber-500 hover:bg-transparent hover:text-white">Get Started</button>
            </div>
        </div>
        <div class="md:w-[55%] w-full"  data-aos="fade-left" >
            <img src="images/bg6.png" alt="" class="w-full mb-5">

        </div>
    </div>
</div>
</section>


<!-- rooms section -->
<section class="bg-gray-100">
    <div class="my-20 lg:px-20 md:px-10 px-5 py-10">
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-teal-950 text-4xl font-semibold">Our Rooms</h1>
            <a href="rooms.php" class="bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-300">
                View All Rooms
            </a>
        </div>
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <?php
            $rooms = $conn->query("SELECT * FROM rooms WHERE status = 'available' ORDER BY created_at DESC LIMIT 6");
            while($room = $rooms->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-aos="fade-up">
                    <a href="room-details.php?id=<?php echo $room['id']; ?>" class="block">
                        <img src="<?php echo $room['image']; ?>" 
                             alt="<?php echo $room['room_type']; ?>" 
                             class="w-full h-64 object-cover hover:scale-105 transition-transform duration-300">
                    </a>
                    <div class="p-6">
                        <h3 class="text-2xl font-semibold text-teal-950 mb-3"><?php echo $room['room_type']; ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo substr($room['description'], 0, 100) . '...'; ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-xl font-medium text-amber-500">$<?php echo number_format($room['price'], 2); ?>/night</span>
                            <a href="booking.php?room_id=<?php echo $room['id']; ?>" 
                               class="bg-teal-950 text-white px-4 py-2 rounded-lg hover:bg-amber-500 transition-colors duration-300">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>


<!-- services -->
<section class="my-20 lg:px-20 md:px-10 px-5">
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-teal-950 text-4xl font-semibold">Our Services</h1>
        <a href="services.php" class="bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-300">
            View All Services
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php
        $services = $conn->query("SELECT * FROM hotel_services LIMIT 8");
        while($service = $services->fetch_assoc()): ?>
            <div class="p-4" data-aos="fade-up">
                <div class="flex justify-center items-center">
                <div class="relative w-32 h-32 rounded-full border-2 border-amber-500 flex items-center justify-center bg-center bg-cover" 
                         style="background-image: url('<?php echo $service['image']; ?>');">
                        <div class="absolute inset-0 bg-teal-950/60 rounded-full"></div>
                        <span class="relative z-10"><i class="<?php echo $service['icon']; ?> text-4xl text-white"></i></span>
                    </div>
                </div>
                <div class="text-center mt-5">
                    <h1 class="mb-2 text-2xl font-bold text-teal-950"><?php echo $service['name']; ?></h1>
                    <p class="text-lg"><?php echo $service['description']; ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>




<!-- Gallery section -->
<section class="my-20 lg:px-20 md:px-10 px-5">
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-teal-950 text-4xl font-semibold">Our Gallery</h1>
        <a href="gallery.php" class="bg-amber-500 text-white px-6 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-300">
            View All Photos
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php
        $gallery_images = $conn->query("SELECT * FROM gallery_images WHERE is_featured = 1 LIMIT 8");
        while($image = $gallery_images->fetch_assoc()): ?>
            <div class="overflow-hidden rounded-lg shadow-lg relative" data-aos="fade-up">
                <img src="<?php echo $image['image_path']; ?>" 
                     alt="<?php echo $image['title']; ?>" 
                     class="w-full h-72 object-cover hover:scale-110 transition-transform duration-300">
                <div class="absolute bottom-0 left-0 right-0 bg-black/50 p-4">
                    <h3 class="text-white font-medium"><?php echo $image['title']; ?></h3>
                    <p class="text-white/80 text-sm"><?php echo $image['category']; ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>




<!-- Newsletter Section -->
<section class="bg-teal-950 py-8 sm:py-12 lg:py-16">
    <div class="lg:px-20 md:px-10 px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="w-full md:w-1/2 text-center md:text-left">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">Subscribe to Our Newsletter</h2>
                <p class="text-amber-500 text-sm sm:text-base">Get exclusive offers, updates, and travel tips delivered directly to your inbox.</p>
            </div>
            <div class="w-full md:w-1/2">
                <form id="newsletterForm" class="flex flex-col sm:flex-row gap-4">
                    <input type="email" name="email" placeholder="Enter your email" 
                        class="w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm sm:text-base">
                    <button class="w-full sm:w-auto bg-amber-500 px-6 py-3 text-white rounded-lg hover:bg-amber-600 whitespace-nowrap text-sm sm:text-base">
                        Subscribe Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-100">
    <div class="lg:px-20 md:px-10 px-4 py-8 sm:py-12 lg:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Hotel Info -->
            <div class="text-center sm:text-left">
                <img src="images/logo.png" alt="Hotel Logo" class="w-16 sm:w-20 mb-4 mx-auto sm:mx-0">
                <p class="text-gray-600 mb-4 text-sm sm:text-base">Experience luxury and comfort at its finest. Your home away from home.</p>
                <div class="flex space-x-6 justify-center sm:justify-start">
                    <a href="#" class="text-teal-950 hover:text-amber-500 text-xl"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-teal-950 hover:text-amber-500 text-xl"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-teal-950 hover:text-amber-500 text-xl"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-teal-950 hover:text-amber-500 text-xl"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="text-center sm:text-left">
                <h3 class="text-lg sm:text-xl font-semibold text-teal-950 mb-4">Quick Links</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-600 hover:text-amber-500 text-sm sm:text-base block">About Us</a></li>
                    <li><a href="rooms.php" class="text-gray-600 hover:text-amber-500 text-sm sm:text-base block">Our Rooms</a></li>
                    <li><a href="services.php" class="text-gray-600 hover:text-amber-500 text-sm sm:text-base block">Services</a></li>
                    <li><a href="gallery.php" class="text-gray-600 hover:text-amber-500 text-sm sm:text-base block">Gallery</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-amber-500 text-sm sm:text-base block">Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="text-center sm:text-left">
                <h3 class="text-lg sm:text-xl font-semibold text-teal-950 mb-4">Contact Info</h3>
                <ul class="space-y-3">
                    <li class="flex items-center text-gray-600 justify-center sm:justify-start">
                        <i class="fas fa-map-marker-alt w-5"></i>
                        <span class="text-sm sm:text-base">123 Hotel Street, City, Country</span>
                    </li>
                    <li class="flex items-center text-gray-600 justify-center sm:justify-start">
                        <i class="fas fa-phone w-5"></i>
                        <span class="text-sm sm:text-base">+1 234 567 8900</span>
                    </li>
                    <li class="flex items-center text-gray-600 justify-center sm:justify-start">
                        <i class="fas fa-envelope w-5"></i>
                        <span class="text-sm sm:text-base">info@hotelname.com</span>
                    </li>
                </ul>
            </div>

            <!-- Opening Hours -->
            <div class="text-center sm:text-left">
                <h3 class="text-lg sm:text-xl font-semibold text-teal-950 mb-4">Opening Hours</h3>
                <ul class="space-y-3">
                    <li class="text-gray-600 text-sm sm:text-base">
                        <span class="font-medium">Check-in:</span> 2:00 PM
                    </li>
                    <li class="text-gray-600 text-sm sm:text-base">
                        <span class="font-medium">Check-out:</span> 12:00 PM
                    </li>
                    <li class="text-gray-600 text-sm sm:text-base">
                        <span class="font-medium">Front Desk:</span> 24/7
                    </li>
                    <li class="text-gray-600 text-sm sm:text-base">
                        <span class="font-medium">Restaurant:</span> 6:30 AM - 10:30 PM
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="bg-teal-950 py-4">
        <div class="lg:px-20 md:px-10 px-4">
            <div class="flex flex-col sm:flex-row justify-between items-center text-white gap-3">
                <p class="text-sm sm:text-base text-center sm:text-left">&copy; 2024 Hotel Name. All rights reserved.</p>
                <div class="flex space-x-4">
                    <a href="#" class="hover:text-amber-500 text-sm sm:text-base">Privacy Policy</a>
                    <a href="#" class="hover:text-amber-500 text-sm sm:text-base">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </div>
</footer>


<!-- scripte -->
 <!-- swal alert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    AOS.init({
        delay:300,
        duration:1000,
    });
    document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'subscribe');
    
    fetch('handlers/newsletter_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Thank You!',
                text: 'You have successfully subscribed to our newsletter.',
                confirmButtonColor: '#f97316'
            });
            this.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: data.message,
                confirmButtonColor: '#f97316'
            });
        }
    });
});

</script>
</body>