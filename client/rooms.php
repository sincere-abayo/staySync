<?php require_once '../config/database.php'; ?>

    <?php include 'includes/sidebar.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <!-- Search and Filter Section -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                    <select id="roomType" class="w-full border rounded-lg p-2">
                        <option value="">All Types</option>
                        <option value="Standard">Standard Room</option>
                        <option value="Deluxe">Deluxe Room</option>
                        <option value="Suite">Suite Room</option>
                        <option value="Family">Family Room</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                    <select id="priceRange" class="w-full border rounded-lg p-2">
                        <option value="">All Prices</option>
                        <option value="0-100">Under $100</option>
                        <option value="100-200">$100 - $200</option>
                        <option value="200-300">$200 - $300</option>
                        <option value="300+">$300+</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                    <select id="capacity" class="w-full border rounded-lg p-2">
                        <option value="">Any Capacity</option>
                        <option value="1-2">1-2 Persons</option>
                        <option value="3-4">3-4 Persons</option>
                        <option value="5+">5+ Persons</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sortBy" class="w-full border rounded-lg p-2">
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="capacity_asc">Capacity: Low to High</option>
                        <option value="capacity_desc">Capacity: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Results Count and View Toggle -->
        <div class="flex justify-between items-center mb-6">
            <h2 id="resultsCount" class="text-xl font-semibold text-teal-950">Showing all rooms</h2>
            <div class="flex gap-2">
                <button onclick="toggleView('grid')" class="p-2 rounded-lg bg-teal-950 text-white">
                    <i class="fas fa-th"></i>
                </button>
                <button onclick="toggleView('list')" class="p-2 rounded-lg bg-gray-200 text-gray-700">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- Rooms Grid -->
        <div id="roomsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $rooms = $conn->query("SELECT * FROM rooms WHERE status = 'available' ORDER BY price ASC");
            while($room = $rooms->fetch_assoc()): ?>
                <div class="room-card bg-white rounded-lg shadow-lg overflow-hidden" 
                     data-type="<?php echo $room['room_type']; ?>"
                     data-price="<?php echo $room['price']; ?>"
                     data-capacity="<?php echo $room['capacity']; ?>">
                    <img src="../<?php echo $room['image']; ?>" 
                         alt="<?php echo $room['room_type']; ?>"
                         class="w-full h-64 object-cover">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-teal-950 mb-2"><?php echo $room['room_type']; ?></h3>
                        <div class="flex gap-4 mb-4 text-gray-600">
                            <span><i class="fas fa-user-friends mr-2"></i><?php echo $room['capacity']; ?> Persons</span>
                            <span><i class="fas fa-ruler-combined mr-2"></i><?php echo $room['size']; ?> sq ft</span>
                        </div>
                        <p class="text-gray-600 mb-4"><?php echo $room['description']; ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-amber-500">$<?php echo number_format($room['price'], 2); ?></span>
                            <button onclick="openBookingModal(<?php echo $room['id']; ?>)" 
                                    class="bg-teal-950 text-white px-6 py-2 rounded-lg hover:bg-amber-500 transition-colors">
                                Book Now
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <!-- Add this inside the booking modal div -->
<div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg max-w-2xl mx-auto mt-20 p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-teal-950">Book Room</h2>
            <button onclick="closeBookingModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="bookingForm" class="space-y-4">
            <input type="hidden" id="room_id" name="room_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                    <input type="date" id="check_in" name="check_in" required 
                           min="<?php echo date('Y-m-d'); ?>"
                           class="w-full border rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                    <input type="date" id="check_out" name="check_out" required 
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                           class="w-full border rounded-lg p-2">
                </div>
                <div class="hover:border border-2 border-teal-950 rounded-lg p-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Number of Adults</label>
        <input type="number" id="adults" name="adults" min="1" required 
               class="w-full border  border-amber-500 border-2 rounded-lg p-2">
    </div>
      <div class="hover:border border-2 border-teal-950 rounded-lg p-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Number of Kids</label>
        <input type="number" id="kids" name="kids" min="0" value="0" 
               class="w-full border border-amber-500 border-2 rounded-lg p-2">
    </div>
      <div class=" hover:border border-2 border-teal-950 rounded-lg p-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
        <textarea id="requests" name="requests" 
                  class="w-full border border-amber-500 border-2 rounded-lg p-2" rows="2"></textarea>
    </div>
            </div>
            <div class="border-t pt-4 mt-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Room Rate:</span>
                    <span class="font-bold text-teal-950" id="roomRate"></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Number of Nights:</span>
                    <span class="font-bold text-teal-950" id="numberOfNights">0</span>
                </div>
                <div class="flex justify-between items-center text-lg font-bold">
                    <span class="text-gray-600">Total Amount:</span>
                    <span class="text-amber-500" id="totalAmount">$0.00</span>
                </div>
            </div>
            <div class="flex justify-end gap-4 mt-6">
                <button type="button" onclick="closeBookingModal()" 
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-teal-950 text-white rounded-lg hover:bg-amber-500">
                    Confirm Booking
                </button>
            </div>
        </form>
    </div>
</div>

    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
        <!-- Modal content here -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function filterRooms() {
            const type = document.getElementById('roomType').value;
            const priceRange = document.getElementById('priceRange').value;
            const capacity = document.getElementById('capacity').value;
            const rooms = document.querySelectorAll('.room-card');
            let visibleCount = 0;

            rooms.forEach(room => {
                let show = true;
                const roomData = room.dataset;

                if (type && roomData.type !== type) show = false;
                if (priceRange) {
                    const [min, max] = priceRange.split('-').map(p => p === '+' ? Infinity : Number(p));
                    const price = Number(roomData.price);
                    if (price < min || price > max) show = false;
                }
                if (capacity) {
                    const [min, max] = capacity.split('-').map(c => c === '+' ? Infinity : Number(c));
                    const cap = Number(roomData.capacity);
                    if (cap < min || cap > max) show = false;
                }

                room.style.display = show ? 'block' : 'none';
                if (show) visibleCount++;
            });

            document.getElementById('resultsCount').textContent = 
                `Showing ${visibleCount} room${visibleCount !== 1 ? 's' : ''}`;
        }

        function toggleView(view) {
            const container = document.getElementById('roomsContainer');
            if (view === 'grid') {
                container.classList.remove('grid-cols-1');
                container.classList.add('md:grid-cols-2', 'lg:grid-cols-3');
            } else {
                container.classList.remove('md:grid-cols-2', 'lg:grid-cols-3');
                container.classList.add('grid-cols-1');
            }
        }

        // Add event listeners
        ['roomType', 'priceRange', 'capacity'].forEach(id => {
            document.getElementById(id).addEventListener('change', filterRooms);
        });

        document.getElementById('sortBy').addEventListener('change', function() {
            const container = document.getElementById('roomsContainer');
            const rooms = Array.from(container.children);
            
            rooms.sort((a, b) => {
                const aData = a.dataset;
                const bData = b.dataset;
                
                switch(this.value) {
                    case 'price_asc':
                        return Number(aData.price) - Number(bData.price);
                    case 'price_desc':
                        return Number(bData.price) - Number(aData.price);
                    case 'capacity_asc':
                        return Number(aData.capacity) - Number(bData.capacity);
                    case 'capacity_desc':
                        return Number(bData.capacity) - Number(aData.capacity);
                }
            });

            rooms.forEach(room => container.appendChild(room));
        });
        let currentRoom = null;

function openBookingModal(roomId) {
    currentRoom = roomId;
    document.getElementById('room_id').value = roomId;
    document.getElementById('bookingModal').classList.remove('hidden');
    fetchRoomDetails(roomId);
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('bookingForm').reset();
}

function fetchRoomDetails(roomId) {
    fetch(`../handlers/room_handler.php?action=get&id=${roomId}`)
        .then(response => response.json())
        .then(room => {
            document.getElementById('adults').max = room.capacity;
            document.getElementById('roomRate').textContent = `$${room.price}/night`;
            calculateTotal(room.price);
        });
}

function calculateTotal(rate) {
    const checkIn = new Date(document.getElementById('check_in').value);
    const checkOut = new Date(document.getElementById('check_out').value);
    
    if (checkIn && checkOut && checkOut > checkIn) {
        const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        const total = nights * rate;
        
        document.getElementById('numberOfNights').textContent = nights;
        document.getElementById('totalAmount').textContent = `$${total.toFixed(2)}`;
    }
}

document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'book');
    
    fetch('../handlers/booking_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Booking Confirmed!',
                text: 'Your room has been successfully booked.',
                confirmButtonColor: '#f97316'
            }).then(() => {
                window.location.href = 'bookings.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Booking Failed',
                text: data.message,
                confirmButtonColor: '#f97316'
            });
        }
    });
});

['check_in', 'check_out'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
        if (currentRoom) {
            fetchRoomDetails(currentRoom);
        }
    });
});

    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
