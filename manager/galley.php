<?php
session_start();
require_once '../config/database.php';
require_once '../includes/session.php';
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
            <h2 class="text-2xl font-bold text-teal-950">Gallery Management</h2>
            <button onclick="openModal()" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
                <i class="fas fa-plus mr-2"></i>Add New Image
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            $images = $conn->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
            while($image = $images->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="relative h-48">
                        <img src="../<?php echo $image['image_path']; ?>" 
                            alt="<?php echo $image['title']; ?>" 
                            class="w-full h-full object-cover">
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <button onclick="editImage(<?php echo $image['id']; ?>)" 
                                    class="bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteImage(<?php echo $image['id']; ?>)" 
                                    class="bg-red-500 text-white p-2 rounded-full hover:bg-red-600">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-teal-950"><?php echo $image['title']; ?></h3>
                        <p class="text-sm text-gray-600"><?php echo $image['category']; ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function openModal() {
    Swal.fire({
        title: 'Add New Gallery Image',
        html: `
            <input type="text" id="imageTitle" class="swal2-input" placeholder="Image Title">
            <select id="imageCategory" class="swal2-input">
                <option value="room">Room</option>
                <option value="service">Service</option>
                <option value="event">Event</option>
                <option value="amenity">Amenity</option>
            </select>
            <textarea id="imageDescription" class="swal2-textarea" placeholder="Description"></textarea>
            <input type="file" id="galleryImage" class="swal2-input" accept="image/*">
            <div class="flex items-center mt-4">
                <input type="checkbox" id="isFeatured" class="mr-2">
                <label for="isFeatured">Feature this image</label>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add',
        confirmButtonColor: '#f97316',
        preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('title', document.getElementById('imageTitle').value);
            formData.append('category', document.getElementById('imageCategory').value);
            formData.append('description', document.getElementById('imageDescription').value);
            formData.append('is_featured', document.getElementById('isFeatured').checked ? 1 : 0);
            formData.append('image', document.getElementById('galleryImage').files[0]);
            return formData;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitGalleryImage(result.value);
        }
    });
}

function submitGalleryImage(formData) {
    fetch('../handlers/gallery_handler.php', {
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
                title: 'Image Added',
                confirmButtonColor: '#f97316'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'An error occurred while saving the image.',
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

function editImage(id) {
    fetch(`../handlers/gallery_handler.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            Swal.fire({
                title: 'Edit Gallery Image',
                html: `
                    <input type="text" id="imageTitle" class="swal2-input" value="${data.title}">
                    <select id="imageCategory" class="swal2-input">
                        <option value="room" ${data.category === 'room' ? 'selected' : ''}>Room</option>
                        <option value="service" ${data.category === 'service' ? 'selected' : ''}>Service</option>
                        <option value="event" ${data.category === 'event' ? 'selected' : ''}>Event</option>
                        <option value="amenity" ${data.category === 'amenity' ? 'selected' : ''}>Amenity</option>
                    </select>
                    <textarea id="imageDescription" class="swal2-textarea">${data.description}</textarea>
                    <input type="file" id="galleryImage" class="swal2-input" accept="image/*">
                    <div class="flex items-center mt-4">
                        <input type="checkbox" id="isFeatured" ${data.is_featured ? 'checked' : ''} class="mr-2">
                        <label for="isFeatured">Feature this image</label>
                    </div>
                    <img src="../${data.image_path}" class="mt-2 w-full max-h-32 object-cover">
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                confirmButtonColor: '#f97316',
                preConfirm: () => {
                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('id', id);
                    formData.append('title', document.getElementById('imageTitle').value);
                    formData.append('category', document.getElementById('imageCategory').value);
                    formData.append('description', document.getElementById('imageDescription').value);
                    formData.append('is_featured', document.getElementById('isFeatured').checked ? 1 : 0);
                    if(document.getElementById('galleryImage').files[0]) {
                        formData.append('image', document.getElementById('galleryImage').files[0]);
                    }
                    return formData;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitGalleryImage(result.value);
                }
            });
        });
}

function deleteImage(id) {
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
            fetch('../handlers/gallery_handler.php', {
                method: 'DELETE',
                body: new URLSearchParams({id: id})
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
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
