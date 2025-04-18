<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">


        <!-- Client Feedback Section -->
        <div class="flex-1 overflow-auto">
<div class="p-5">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-teal-950">Client Feedback</h2>
        <div class="flex items-center space-x-2">
            <span class="text-amber-500 font-bold text-xl">4.8</span>
            <div class="flex text-amber-500">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <span class="text-gray-500">(256 reviews)</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Review 1 -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-teal-950 rounded-full flex items-center justify-center text-white font-bold">
                        SM
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-teal-950">Sarah Mitchell</h3>
                        <p class="text-gray-500 text-sm">Deluxe Room - Dec 2023</p>
                    </div>
                </div>
                <div class="flex text-amber-500">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <p class="text-gray-600">"Exceptional service and beautiful rooms. The staff went above and beyond to make our stay memorable."</p>
        </div>

        <!-- Review 2 -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-amber-500 rounded-full flex items-center justify-center text-white font-bold">
                        JD
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-teal-950">James Davis</h3>
                        <p class="text-gray-500 text-sm">Suite Room - Nov 2023</p>
                    </div>
                </div>
                <div class="flex text-amber-500">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="far fa-star"></i>
                </div>
            </div>
            <p class="text-gray-600">"Luxurious amenities and great location. The breakfast buffet was outstanding!"</p>
        </div>

        <!-- Review 3 -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-teal-800 rounded-full flex items-center justify-center text-white font-bold">
                        EW
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-teal-950">Emma Wilson</h3>
                        <p class="text-gray-500 text-sm">Family Room - Oct 2023</p>
                    </div>
                </div>
                <div class="flex text-amber-500">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                </div>
            </div>
            <p class="text-gray-600">"Perfect for family stays. Kids loved the pool area and the staff was very accommodating."</p>
        </div>

        <!-- Review 4 -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold">
                        RL
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-teal-950">Robert Lee</h3>
                        <p class="text-gray-500 text-sm">Executive Suite - Sep 2023</p>
                    </div>
                </div>
                <div class="flex text-amber-500">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <p class="text-gray-600">"Top-notch business facilities and excellent room service. Will definitely return!"</p>
        </div>

        <!-- Review 5 -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-teal-700 rounded-full flex items-center justify-center text-white font-bold">
                        AK
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-teal-950">Anna Kim</h3>
                        <p class="text-gray-500 text-sm">Deluxe Room - Aug 2023</p>
                    </div>
                </div>
                <div class="flex text-amber-500">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <p class="text-gray-600">"Impeccable cleanliness and modern amenities. The spa services were exceptional!"</p>
        </div>

        <!-- Continue with reviews 6-10 in the same format -->
        <!-- Additional reviews hidden for brevity -->
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center space-x-2">
            <button class="px-3 py-1 rounded-lg bg-teal-950 text-white hover:bg-amber-500">1</button>
            <button class="px-3 py-1 rounded-lg hover:bg-amber-500 hover:text-white">2</button>
            <button class="px-3 py-1 rounded-lg hover:bg-amber-500 hover:text-white">3</button>
            <span class="px-3 py-1">...</span>
            <button class="px-3 py-1 rounded-lg hover:bg-amber-500 hover:text-white">10</button>
        </nav>
    </div>
</div>
</div>

        </div>
        </body>
        </html>