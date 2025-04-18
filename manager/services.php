<?php
session_start();
require_once '../config/database.php';
// check_admin();

// Include header
include_once 'includes/header.php';

// Include sidebar
include_once 'includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 overflow-auto">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-teal-950">Services Management</h2>
            <button onclick="openModal()" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                <i class="fas fa-plus mr-2"></i>Add New Service
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $services = $conn->query("SELECT * FROM hotel_services ORDER BY created_at DESC");
            while($service = $services->fetch_assoc()): ?>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center">
                        <div class="w-16 h-16 rounded-lg overflow-hidden">
                            <img src="../<?php echo $service['image']; ?>" 
                                alt="<?php echo $service['name']; ?>" 
                                class="w-full h-full object-cover">
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-semibold text-teal-950"><?php echo $service['name']; ?></h3>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="editService(<?php echo $service['id']; ?>)" class="text-blue-500 hover:text-blue-700">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteService(<?php echo $service['id']; ?>)" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                    <p class="text-gray-600"><?php echo $service['description']; ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openModal() {
    Swal.fire({
        title: 'Add New Service',
        html: `
            <input type="text" id="serviceName" class="swal2-input" placeholder="Service Name">
            <textarea id="serviceDescription" class="swal2-textarea" placeholder="Service Description"></textarea>
            <input type="hidden" id="serviceIcon" class="swal2-input" value="fa-swimming-pool" placeholder="FontAwesome Icon Class">
            <input type="file" id="serviceImage" class="swal2-input" accept="image/*">
        `,
        showCancelButton: true,
        confirmButtonText: 'Add',
        confirmButtonColor: '#f97316',
        preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('name', document.getElementById('serviceName').value);
            formData.append('description', document.getElementById('serviceDescription').value);
            formData.append('icon', document.getElementById('serviceIcon').value);
            
            // Check if a file was selected before appending
            const fileInput = document.getElementById('serviceImage');
            if (fileInput.files.length > 0) {
                formData.append('image', fileInput.files[0]);
            }
            
            return formData;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitService(result.value);
        }
    });
}

function submitService(formData) {
    fetch('../handlers/services_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        // Try to parse as JSON, but handle case where there might be PHP warnings
        let data;
        try {
            // If there are PHP warnings, they'll be at the beginning of the response
            // Try to extract just the JSON part
            const jsonStart = text.indexOf('{');
            const jsonEnd = text.lastIndexOf('}') + 1;
            
            if (jsonStart >= 0 && jsonEnd > jsonStart) {
                const jsonString = text.substring(jsonStart, jsonEnd);
                data = JSON.parse(jsonString);
            } else {
                throw new Error('No valid JSON found in response');
            }
        } catch (e) {
            console.error('Error parsing JSON:', text);
            throw new Error('Invalid JSON response from server');
        }
        
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: data.message,
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'An error occurred while saving the service.',
                confirmButtonColor: '#f97316'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            text: 'There was a problem connecting to the server. Please try again.',
            confirmButtonColor: '#f97316'
        });
    });
}

function editService(id) {
    fetch(`../handlers/services_handler.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                title: 'Edit Service',
                html: `
                    <input type="text" id="serviceName" class="swal2-input" value="${data.name}">
                    <textarea id="serviceDescription" class="swal2-textarea">${data.description}</textarea>
                    <input type="hidden" id="serviceIcon" class="swal2-input" value="${data.icon}">
                    <input type="file" id="serviceImage" class="swal2-input" accept="image/*">
                    <img src="../${data.image}" class="mt-2 w-full max-h-32 object-cover">
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                confirmButtonColor: '#f97316',
                preConfirm: () => {
                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('id', id);
                    formData.append('name', document.getElementById('serviceName').value);
                    formData.append('description', document.getElementById('serviceDescription').value);
                    formData.append('icon', document.getElementById('serviceIcon').value);
                    if(document.getElementById('serviceImage').files[0]) {
                        formData.append('image', document.getElementById('serviceImage').files[0]);
                    }
                    return formData;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitService(result.value);
                }
            });
        });
}

function deleteService(id) {
    Swal.fire({
        title: 'Delete Service?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#gray',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../handlers/services_handler.php', {
                method: 'DELETE',
                body: new URLSearchParams({id: id})
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Service Deleted',
                        confirmButtonColor: '#f97316'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
