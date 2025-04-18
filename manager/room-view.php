<?php
require_once '../includes/session.php';
require_once '../config/database.php';

$room_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$room = $conn->query("SELECT * FROM rooms WHERE id = $room_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details - <?php echo $room['room_number']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Room Header -->
            <div class="relative h-64">
                <img src="../<?php echo $room['image']; ?>" alt="Room Image" class="w-full h-full object-cover">
                <div class="absolute top-0 left-0 p-4">
                    <button onclick="history.back()" class="bg-white/80 p-2 rounded-full hover:bg-white">
                        <i class="fas fa-arrow-left text-teal-950"></i>
                    </button>
                </div>
            </div>

            <!-- Room Details -->
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-teal-950">Room <?php echo $room['room_number']; ?></h1>
                        <p class="text-gray-600"><?php echo $room['room_type']; ?></p>
                    </div>
                    <span class="<?php 
                        echo $room['status'] === 'available' ? 'bg-green-100 text-green-800' : 
                            ($room['status'] === 'booked' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800'); 
                        ?> px-4 py-2 rounded-full text-sm font-medium">
                        <?php echo ucfirst($room['status']); ?>
                    </span>
                </div>

                <!-- Main Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Price per Night</h3>
                            <p class="text-lg font-semibold text-teal-950">$<?php echo number_format($room['price'], 2); ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Room Size</h3>
                            <p class="text-lg font-semibold text-teal-950"><?php echo $room['size']; ?> sq ft</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Capacity</h3>
                            <p class="text-lg font-semibold text-teal-950"><?php echo $room['capacity']; ?> persons</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Floor Number</h3>
                            <p class="text-lg font-semibold text-teal-950"><?php echo $room['floor_number']; ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">View Type</h3>
                            <p class="text-lg font-semibold text-teal-950"><?php echo $room['view_type']; ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Bed Configuration</h3>
                            <p class="text-lg font-semibold text-teal-950"><?php echo $room['bed_config']; ?></p>
                        </div>
                    </div>
                </div>

              
            </div>
            <!-- Room Services Section -->
<div class="border-t pt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-teal-950">Room Services</h2>
        <button onclick="addService(<?php echo $room_id; ?>)" 
                class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
            <i class="fas fa-plus mr-2"></i>Add Service
        </button>
    </div>
    <?php
    $services = $conn->query("SELECT * FROM room_services WHERE room_id = $room_id");
    if ($services->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php while($service = $services->fetch_assoc()): ?>
                <div class="p-4 border rounded-lg">
                    <div class="flex justify-between">
                        <h3 class="font-medium text-teal-950"><?php echo $service['service_name']; ?></h3>
                        <div class="flex space-x-2">
                           
                            <button onclick="deleteService(<?php echo $service['id']; ?>)" 
                                    class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-gray-600"><?php echo $service['service_description']; ?></p>
                    <p class="text-amber-500 font-medium mt-2">$<?php echo number_format($service['price'], 2); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Maintenance Section -->
<div class="border-t pt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-teal-950">Maintenance History</h2>
        <button onclick="addMaintenance(<?php echo $room_id; ?>)" 
                class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
            <i class="fas fa-plus mr-2"></i>Add Maintenance
        </button>
    </div>
    <?php
    $maintenance = $conn->query("SELECT * FROM room_maintenance WHERE room_id = $room_id ORDER BY scheduled_date DESC");
    if ($maintenance->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while($record = $maintenance->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo $record['maintenance_type']; ?></td>
                            <td class="px-6 py-4"><?php echo $record['description']; ?></td>
                            <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($record['scheduled_date'])); ?></td>
                            <td class="px-6 py-4">
                                <span class="<?php 
                                    echo $record['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                        ($record['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'); 
                                    ?> px-2 py-1 rounded-full text-xs">
                                    <?php echo ucfirst($record['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                   
                                    <button onclick="deleteMaintenance(<?php echo $record['id']; ?>)" 
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
    <?php endif; ?>
</div>

<!-- Cleaning Schedule Section -->
<div class="border-t pt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-teal-950">Cleaning Schedule</h2>
        <button onclick="addCleaning(<?php echo $room_id; ?>)" 
                class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
            <i class="fas fa-plus mr-2"></i>Add Schedule
        </button>
    </div>
    <?php
    $cleaning = $conn->query("SELECT * FROM room_cleaning WHERE room_id = $room_id ORDER BY next_scheduled DESC");
    if ($cleaning->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Cleaned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Next Schedule</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while($schedule = $cleaning->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4"><?php echo $schedule['cleaning_type']; ?></td>
                            <td class="px-6 py-4"><?php echo date('M d, Y H:i', strtotime($schedule['last_cleaned'])); ?></td>
                            <td class="px-6 py-4"><?php echo date('M d, Y H:i', strtotime($schedule['next_scheduled'])); ?></td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    
                                    <button onclick="deleteCleaning(<?php echo $schedule['id']; ?>)" 
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
    <?php endif; ?>
</div>
<!-- Room Images Gallery -->
<div class="border-t pt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-teal-950">Room Gallery</h2>
        <button onclick="addImages(<?php echo $room_id; ?>)" 
                class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
            <i class="fas fa-plus mr-2"></i>Add Images
        </button>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php
        $images = $conn->query("SELECT * FROM room_images WHERE room_id = $room_id");
        while($image = $images->fetch_assoc()): ?>
            <div class="relative group">
                <img src="../<?php echo $image['image_path']; ?>" 
                     alt="Room Image" 
                     class="w-full h-48 object-cover rounded-lg">
                <div class="absolute top-2 right-2 hidden group-hover:flex space-x-2">
                    <button onclick="deleteImage(<?php echo $image['id']; ?>)" 
                            class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php if(!$image['is_primary']): ?>
                        <button onclick="setPrimaryImage(<?php echo $image['id']; ?>)" 
                                class="bg-amber-500 text-white p-2 rounded-full hover:bg-amber-600">
                            <i class="fas fa-star"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
// Service Management
function addService(roomId) {
    Swal.fire({
        title: 'Add Room Service',
        html: `
            <input type="text" id="serviceName" class="swal2-input" placeholder="Service Name">
            <textarea id="serviceDescription" class="swal2-textarea" placeholder="Service Description"></textarea>
            <input type="number" id="servicePrice" class="swal2-input" placeholder="Price">
            <input type="hidden" name="add" value="service" class="swal2-input">

        `,
        showCancelButton: true,
        confirmButtonText: 'Add',
        confirmButtonColor: '#f97316',
        preConfirm: () => {
            return {
                name: document.getElementById('serviceName').value,
                description: document.getElementById('serviceDescription').value,
                price: document.getElementById('servicePrice').value,
                room_id: roomId
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Handle form submission
            submitService(result.value);
        }
    });
}

// Maintenance Management
function addMaintenance(roomId) {
    Swal.fire({
        title: 'Schedule Maintenance',
        html: `
            <select id="maintenanceType" class="swal2-input">
                <option value="Repair">Repair</option>
                <option value="Inspection">Inspection</option>
                <option value="Upgrade">Upgrade</option>
            </select>
            <textarea id="maintenanceDescription" class="swal2-textarea" placeholder="Description"></textarea>
            <input type="date" id="scheduledDate" class="swal2-input">
        `,
        showCancelButton: true,
        confirmButtonText: 'Schedule',
        confirmButtonColor: '#f97316',
        preConfirm: () => {
            return {
                type: document.getElementById('maintenanceType').value,
                description: document.getElementById('maintenanceDescription').value,
                scheduled_date: document.getElementById('scheduledDate').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitMaintenance(roomId);
        }
    });
}


// Cleaning Management
function addCleaning(roomId) {
    Swal.fire({
        title: 'Add Cleaning Schedule',
        html: `
            <select id="cleaningType" class="swal2-input">
                <option value="Regular">Regular Cleaning</option>
                <option value="Deep">Deep Cleaning</option>
                <option value="Sanitization">Sanitization</option>
            </select>
            <input type="datetime-local" id="nextScheduled" class="swal2-input">
        `,
        showCancelButton: true,
        confirmButtonText: 'Schedule',
        confirmButtonColor: '#f97316',
        preConfirm: () => {
            return {
                type: document.getElementById('cleaningType').value,
                next_scheduled: document.getElementById('nextScheduled').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitCleaning(roomId);
        }
    });
}
// Delete functions
function deleteService(id) {
    confirmDelete('service', id);
}

function deleteMaintenance(id) {
    confirmDelete('maintenance', id);
}

function deleteCleaning(id) {
    confirmDelete('cleaning', id);
}

function confirmDelete(type, id) {
    Swal.fire({
        title: 'Are you sure?',
        text: `This ${type} record will be permanently deleted.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#gray',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            handleDelete(type, id);
        }
    });
}
function submitService(data) {
    fetch('../handlers/room_services_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'add_service',
            ...data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Service Added',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        }
    });
}

function handleDelete(type, id) {
    fetch('../handlers/room_services_handler.php', {
        method: 'DELETE',
        body: new URLSearchParams({
            type: type,
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Record has been deleted.',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        }
    });
}




function submitMaintenance(roomId) {
    const maintenanceType = document.getElementById('maintenanceType').value;
    const description = document.getElementById('maintenanceDescription').value;
    const scheduledDate = document.getElementById('scheduledDate').value;

    fetch('../handlers/room_services_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'add_maintenance',
            room_id: roomId,
            type: maintenanceType,
            description: description,
            scheduled_date: scheduledDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Maintenance Scheduled',
                text: 'New maintenance record has been added',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong!',
            confirmButtonColor: '#f97316'
        });
    });
}


function editMaintenance(id) {
    fetch(`../handlers/room_services_handler.php?action=get_maintenance&id=${id}`)
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                title: 'Edit Maintenance Record',
                html: `
                    <select id="maintenanceType" class="swal2-input">
                        <option value="Repair" ${data.maintenance_type === 'Repair' ? 'selected' : ''}>Repair</option>
                        <option value="Inspection" ${data.maintenance_type === 'Inspection' ? 'selected' : ''}>Inspection</option>
                        <option value="Upgrade" ${data.maintenance_type === 'Upgrade' ? 'selected' : ''}>Upgrade</option>
                    </select>
                    <textarea id="maintenanceDescription" class="swal2-textarea">${data.description}</textarea>
                    <input type="date" id="scheduledDate" class="swal2-input" value="${data.scheduled_date}">
                    <select id="maintenanceStatus" class="swal2-input">
                        <option value="pending" ${data.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="in_progress" ${data.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                        <option value="completed" ${data.status === 'completed' ? 'selected' : ''}>Completed</option>
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                confirmButtonColor: '#f97316',
                preConfirm: () => {
                    return {
                        id: id,
                        type: document.getElementById('maintenanceType').value,
                        description: document.getElementById('maintenanceDescription').value,
                        scheduled_date: document.getElementById('scheduledDate').value,
                        status: document.getElementById('maintenanceStatus').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    updateMaintenance(result.value);
                }
            });
        });
}

function updateMaintenance(data) {
    fetch('../handlers/room_services_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'update_maintenance',
            ...data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Maintenance record has been updated',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        }
    });
}
function submitCleaning(roomId) {
    const cleaningType = document.getElementById('cleaningType').value;
    const nextScheduled = document.getElementById('nextScheduled').value;

    fetch('../handlers/room_services_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'add_cleaning',
            room_id: roomId,
            type: cleaningType,
            next_scheduled: nextScheduled
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Cleaning Scheduled',
                text: 'New cleaning schedule has been added',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong!',
            confirmButtonColor: '#f97316'
        });
    });
}
function addImages(roomId) {
    Swal.fire({
        title: 'Add Room Images',
        html: `
            <input type="file" id="roomImages" multiple accept="image/*" 
                   class="swal2-input">
        `,
        showCancelButton: true,
        confirmButtonText: 'Upload',
        confirmButtonColor: '#f97316',
        preConfirm: () => {
            const formData = new FormData();
            const fileInput = document.getElementById('roomImages');
            for(let i = 0; i < fileInput.files.length; i++) {
                formData.append('images[]', fileInput.files[i]);
            }
            formData.append('room_id', roomId);
            formData.append('action', 'add_images');
            return formData;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            uploadImages(result.value);
        }
    });
}

function uploadImages(formData) {
    fetch('../handlers/room_services_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Images Uploaded',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        }
    });
}
function deleteImage(imageId) {
    Swal.fire({
        title: 'Delete Image?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#gray',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../handlers/room_services_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'delete_image',
                    image_id: imageId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Image Deleted',
                        confirmButtonColor: '#f97316'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}

function setPrimaryImage(imageId) {
    fetch('../handlers/room_services_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'set_primary',
            image_id: imageId,
            room_id: <?php echo $room_id; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Primary Image Set',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        }
    });
}

</script>

</body>

</html>
