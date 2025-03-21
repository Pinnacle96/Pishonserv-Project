<?php if (isset($_SESSION['success'])): ?>
<p class="text-green-500"><?php echo $_SESSION['success'];
                                unset($_SESSION['success']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
<p class="text-red-500"><?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?></p>
<?php endif; ?>
<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Manage Users</h2>
    <p class="text-gray-600 dark:text-gray-400">View, add, and remove users (excluding Admins and Superadmins).</p>

    <!-- Add User Form -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Add New User</h3>
        <form action="../process/admin_add_user.php" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <label class="block text-gray-700 font-semibold">First Name</label>
                    <input type="text" name="name" required class="w-full p-3 border rounded mt-1">
                </div>
                <!-- Last Name -->
                <div>
                    <label class="block text-gray-700 font-semibold">Last Name</label>
                    <input type="text" name="lname" required class="w-full p-3 border rounded mt-1">
                </div>
            </div>
            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" required class="w-full p-3 border rounded mt-1">
            </div>
            <!-- Phone -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Phone</label>
                <input type="text" name="phone" required class="w-full p-3 border rounded mt-1">
            </div>
            <!-- Role Selection -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Role</label>
                <select name="role" required class="w-full p-3 border rounded mt-1">
                    <option value="" disabled selected>Select Role</option>
                    <option value="buyer">Buyer</option>
                    <option value="agent">Agent</option>
                    <option value="owner">Owner</option>
                    <option value="hotel_owner">Hotel Owner</option>
                </select>
            </div>
            <!-- Password -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Password</label>
                <input type="password" name="password" required class="w-full p-3 border rounded mt-1">
            </div>
            <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">
                Add User
            </button>
        </form>
    </div>

    <!-- User List -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">User List</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Profile</th>
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">Email</th>
                        <th class="p-3 border">Phone</th>
                        <th class="p-3 border">Role</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch only non-admin users
                    $users = $conn->query("SELECT id, name, lname, email, phone, role, profile_image FROM users 
                                           WHERE role IN ('buyer', 'agent', 'owner', 'hotel_owner')
                                           ORDER BY role ASC");
                    if (!$users) {
                        echo "<tr><td colspan='6'>Error: " . $conn->error . "</td></tr>";
                    } else {
                        while ($row = $users->fetch_assoc()) {
                            $profileImage = !empty($row['profile_image']) ? "../public/uploads/{$row['profile_image']}" : "../public/uploads/default.png";
                            echo "<tr>
                                <td class='p-3 border'><img src='{$profileImage}' class='w-10 h-10 rounded-full'></td>
                                <td class='p-3 border'>" . htmlspecialchars($row['name'] . " " . $row['lname']) . "</td>
                                <td class='p-3 border'>" . htmlspecialchars($row['email']) . "</td>
                                <td class='p-3 border'>" . htmlspecialchars($row['phone']) . "</td>
                                <td class='p-3 border capitalize font-bold text-blue-600'>" . htmlspecialchars($row['role']) . "</td>
                                <td class='p-3 border flex space-x-3'>
                                    <!-- Edit Button -->
                                    <a href='admin_edit_user.php?id={$row['id']}' class='text-blue-500'>Edit</a>
                                    <!-- Delete Form -->
                                    <form action='../process/admin_delete_user.php' method='POST' class='delete-form'>
                                        <input type='hidden' name='user_id' value='{$row['id']}'>
                                        <button type='submit' class='text-red-500'>Delete</button>
                                    </form>
                                </td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- JavaScript: Confirm Before Deleting with SweetAlert -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });
        });
    });
    </script>
</div>