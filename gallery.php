<?php require_once 'database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery - Stay Sync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold text-teal-950 mb-12 text-center">Our Photo Gallery</h1>

        <!-- Category Filter -->
        <div class="flex justify-center gap-4 mb-8">
            <button onclick="filterGallery('all')" class="category-btn active">All</button>
            <button onclick="filterGallery('room')" class="category-btn">Rooms</button>
            <button onclick="filterGallery('service')" class="category-btn">Services</button>
            <button onclick="filterGallery('event')" class="category-btn">Events</button>
            <button onclick="filterGallery('amenity')" class="category-btn">Amenities</button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            $gallery = $conn->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
            while($image = $gallery->fetch_assoc()): ?>
                <div class="gallery-item <?php echo $image['category']; ?>" data-aos="fade-up">
                    <div class="relative overflow-hidden rounded-lg shadow-lg group">
                        <img src="<?php echo $image['image_path']; ?>" 
                             alt="<?php echo $image['title']; ?>"
                             class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
                            <h3 class="text-white font-medium"><?php echo $image['title']; ?></h3>
                            <p class="text-white/80 text-sm"><?php echo $image['description']; ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <style>
        .category-btn {
            @apply px-4 py-2 rounded-lg text-teal-950 border border-teal-950 hover:bg-teal-950 hover:text-white transition-colors;
        }
        .category-btn.active {
            @apply bg-teal-950 text-white;
        }
    </style>

    <script>
        function filterGallery(category) {
            const items = document.querySelectorAll('.gallery-item');
            const buttons = document.querySelectorAll('.category-btn');

            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            items.forEach(item => {
                if (category === 'all' || item.classList.contains(category)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        AOS.init({
            delay: 300,
            duration: 1000
        });
    </script>
</body>
</html>
