
<nav class="flex bg-teal-950 flex-col md:flex-row justify-between shadow-sm shadow-amber-500 items-center lg:px-20 md:px-10 px-5 py-4">
            <div class="flex justify-between items-center w-full md:w-auto">
               <img src="images/logo.png" alt="" class="w-20">
               <button class="md:hidden text-white text-2xl" onclick="toggleMenu()">
                   <i class="fas fa-bars"></i>
               </button>
           </div>

            <ul id="mobileMenu" class="hidden md:flex flex-col md:flex-row gap-4 md:gap-10 text-center mb-4 md:mb-0 w-full md:w-auto">
               <li>
                   <a href="index.php" class="text-xl text-white font-normal hover:border-b-2 hover:border-amber-500 px-3 py-1 block" >Home</a>
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

