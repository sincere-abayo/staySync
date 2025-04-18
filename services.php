<?php require_once 'database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - Stay Sync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold text-teal-950 mb-12 text-center">Our Hotel Services</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $services = $conn->query("SELECT * FROM hotel_services");
            while($service = $services->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-aos="fade-up">
                    <img src="<?php echo $service['image']; ?>" 
                         alt="<?php echo $service['name']; ?>"
                         class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-teal-950 mb-4"><?php echo $service['name']; ?></h2>
                        <p class="text-gray-600"><?php echo $service['description']; ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            delay: 300,
            duration: 1000
        });
    </script>
</body>
</html>
