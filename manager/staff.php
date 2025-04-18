
<head>
    <meta name="viewport" content="width=], initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
</head>

<div class="flex-1 overflow-auto">
    <div class="p-6">
    <!-- Staff Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-teal-950">
            <h3 class="text-gray-500 text-sm">Total Staff</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-users text-teal-950 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-teal-950">45</span>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-amber-500">
            <h3 class="text-gray-500 text-sm">On Duty Today</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-user-clock text-amber-500 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-amber-500">32</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm">Departments</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-building text-green-500 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-green-500">8</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-lg border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm">New Hires</h3>
            <div class="flex items-center mt-2">
                <i class="fas fa-user-plus text-blue-500 text-2xl mr-3"></i>
                <span class="text-2xl font-bold text-blue-500">5</span>
            </div>
        </div>
    </div>

    <!-- Staff List Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-teal-950">Staff Management</h2>
        <button onclick="openStaffModal()" class="bg-amber-500 text-white px-4 py-2 rounded-lg hover:bg-amber-600">
            <i class="fas fa-plus mr-2"></i>Add New Staff
        </button>
    </div>

    <!-- Staff Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-teal-950 text-white">
                <tr>
                    <th class="px-6 py-3 text-left">Employee ID</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Department</th>
                    <th class="px-6 py-3 text-left">Position</th>
                    <th class="px-6 py-3 text-left">Contact</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">EMP001</td>
                    <td class="px-6 py-4 flex items-center">
                        <div class="w-8 h-8 bg-teal-950 rounded-full flex items-center justify-center text-white text-sm mr-3">JD</div>
                        John Doe
                    </td>
                    <td class="px-6 py-4">Front Desk</td>
                    <td class="px-6 py-4">Receptionist</td>
                    <td class="px-6 py-4">+1 234-567-8900</td>
                    <td class="px-6 py-4">
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Active</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <!-- Add more staff rows here -->
            </tbody>
        </table>
    </div>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-teal-950 mb-4">Add New Staff Member</h3>
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                            <option>Front Desk</option>
                            <option>Housekeeping</option>
                            <option>Restaurant</option>
                            <option>Maintenance</option>
                            <option>Security</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Position</label>
                        <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                        <input type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-950 focus:ring focus:ring-teal-200">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeStaffModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">Add Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openStaffModal() {
    document.getElementById('addStaffModal').classList.remove('hidden');
}

function closeStaffModal() {
    document.getElementById('addStaffModal').classList.add('hidden');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addStaffModal');
    if (event.target == modal) {
        closeStaffModal();
    }
}
</script>
