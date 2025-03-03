
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/session.php';
// check_admin();

?>
<head>
    <meta name="viewport" content="width=], initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
</head>

<div class="flex-1 overflow-auto">
    <div class="p-6">
    <!-- Header with Add Room Button -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-teal-950">Room Management</h2>
        <button onclick="openModal()" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
            <i class="fas fa-plus mr-2"></i>Add New Room
        </button>
    </div>

    <!-- Room Table -->
 <!-- Room Table -->
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-teal-950 text-white">
            <tr>
                <th class="px-6 py-3 text-left">Room Number</th>
                <th class="px-6 py-3 text-left">Room Type</th>
                <th class="px-6 py-3 text-left">Description</th>
                <th class="px-6 py-3 text-left">Price/Night</th>
                <th class="px-6 py-3 text-left">Size (sq ft)</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php
            $rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number");
            while ($room = $rooms->fetch_assoc()):
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4"><?php echo $room['room_number']; ?></td>
                <td class="px-6 py-4"><?php echo $room['room_type']; ?></td>
                <td class="px-6 py-4"><?php echo $room['description']; ?></td>
                <td class="px-6 py-4">$<?php echo number_format($room['price'], 2); ?></td>
                <td class="px-6 py-4"><?php echo $room['size']; ?></td>
                <td class="px-6 py-4">
                    <span class="<?php 
                        echo $room['status'] === 'available' ? 'bg-green-100 text-green-800' : 
                            ($room['status'] === 'booked' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'); 
                        ?> px-3 py-1 rounded-full text-sm">
                        <?php echo ucfirst($room['status']); ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex space-x-2">
                        <button onclick="editRoom(<?php echo $room['id']; ?>)" 
                                class="text-blue-500 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteRoom(<?php echo $room['id']; ?>)" 
                                class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<!-- Edit Room Modal -->
<div id="editRoomModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-bold text-teal-950 mb-4">Edit Room</h3>
            <form id="editRoomForm" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" id="edit_room_id" name="room_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Room Number</label>
                    <input type="text" id="edit_room_number" name="room_number" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Room Type</label>
                    <select id="edit_room_type" name="room_type" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                        <option value="Standard">Standard Room</option>
                        <option value="Deluxe">Deluxe Room</option>
                        <option value="Suite">Suite Room</option>
                        <option value="Executive">Executive Suite</option>
                        <option value="Family">Family Room</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price per Night ($)</label>
                    <input type="number" id="edit_price" name="price" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Room Size (sq ft)</label>
                    <input type="number" id="edit_size" name="size" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Capacity (persons)</label>
                    <input type="number" id="edit_capacity" name="capacity" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Amenities</label>
                    <textarea id="edit_amenities" name="amenities" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="edit_description" name="description" rows="3" required 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Image</label>
                    <img id="current_room_image" src="" alt="Room Image" class="w-full h-40 object-cover rounded-md mb-2">
                    <input type="file" name="image" accept="image/*" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                </div>

                <input type="hidden" name="action" value="update">
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">
                        Update Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Add Room Modal -->
    <div id="addRoomModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-teal-950 mb-4">Add New Room</h3>
                <form id="roomForm" enctype="multipart/form-data" class="space-y-4" >
    <div>
        <label class="block text-sm font-medium text-gray-700">Room Number</label>
        <input type="text" name="room_number" required 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Room Type</label>
        <select name="room_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
            <option value="Standard">Standard Room</option>
            <option value="Deluxe">Deluxe Room</option>
            <option value="Suite">Suite Room</option>
            <option value="Executive">Executive Suite</option>
            <option value="Family">Family Room</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Price per Night ($)</label>
        <input type="number" name="price" required 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Room Size (meters)</label>
        <input type="number" name="size" required 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Capacity (persons)</label>
        <input type="number" name="capacity" required 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Amenities</label>
        <textarea name="amenities" rows="3" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200"
                  placeholder="WiFi, TV, Mini Bar, etc."></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" rows="3" required 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Room Image</label>
        <input type="file" name="image" accept="image/*" 
               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
    </div>

    <input type="hidden" name="action" value="add">
    
    <div class="flex justify-end space-x-3 mt-6">
    <button type="button" onclick="closeModal()" 
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
        Cancel
    </button>
    <button type="submit" 
            class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">
        Add Room
    </button>
</div>

</form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openModal() {
    document.getElementById('addRoomModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('addRoomModal').classList.add('hidden');
    document.getElementById('roomForm').reset();
}

document.getElementById('roomForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Adding New Room',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('../handlers/room_handler.php', {
    method: 'POST',
    body: formData,
    headers: {
        'Accept': 'application/json'
    }
})

    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Room Added Successfully',
                text: 'The new room has been added to the system',
                confirmButtonColor: '#f97316'
            }).then(() => {
                closeModal();
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#f97316'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong! Please try again.',
            confirmButtonColor: '#f97316'
        });
    });
});

function deleteRoom(roomId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting Room',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`../handlers/room_handler.php?action=delete&id=${roomId}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Room has been deleted.',
                        confirmButtonColor: '#f97316'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}

function editRoom(roomId) {
    // Show loading state
    Swal.fire({
        title: 'Loading Room Details',
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`../handlers/room_handler.php?action=get&id=${roomId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                document.getElementById('edit_room_id').value = data.id;
                document.getElementById('edit_room_number').value = data.room_number;
                document.getElementById('edit_room_type').value = data.room_type;
                document.getElementById('edit_price').value = data.price;
                document.getElementById('edit_size').value = data.size;
                document.getElementById('edit_capacity').value = data.capacity;
                document.getElementById('edit_amenities').value = data.amenities;
                document.getElementById('edit_description').value = data.description;
                if (data.image) {
                    document.getElementById('current_room_image').src = '../' + data.image;
                }
                document.getElementById('editRoomModal').classList.remove('hidden');
                Swal.close();
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load room details',
                confirmButtonColor: '#f97316'
            });
        });
}

function closeEditModal() {
    document.getElementById('editRoomModal').classList.add('hidden');
    document.getElementById('editRoomForm').reset();
}

document.getElementById('editRoomForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'update');
    
    console.log('Sending update request with data:', Object.fromEntries(formData));

    fetch('../handlers/room_handler.php', {
    method: 'POST',
    body: formData,
    headers: {
    'Accept': 'application/json'
}
})
.then(response => {
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return response.json();
})
.then(data => {
    console.log('Parsed response data:', data);
    if (data.status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Room Updated',
            text: 'Room details have been updated successfully',
            confirmButtonColor: '#f97316'
        }).then(() => {
            closeEditModal();
            location.reload();
        });
    } else {
        throw new Error(data.message || 'Update failed');
    }
})
.catch(error => {
    console.error('Error details:', error);
    Swal.fire({
        icon: 'error',
        title: 'Update Failed',
        text: error.message || 'Something went wrong while updating the room',
        confirmButtonColor: '#f97316'
    });
});
});


// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addRoomModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

