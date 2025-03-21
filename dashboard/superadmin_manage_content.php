<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Manage Admins & Users</h2>
    <p class="text-gray-600 dark:text-gray-400">View, add, and remove admins & users.</p>

    <!-- Add Admin/User Form -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Add New User</h3>
        <form action="../process/superadmin_add_admin.php" method="POST">
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
                    <!-- <option value="buyer">Buyer</option>
                    <option value="agent">Agent</option>
                    <option value="owner">Owner</option>
                    <option value="hotel_owner">Hotel Owner</option> -->
                    <option value="admin">Admin</option>
                    <option value="superadmin">Super Admin</option>
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

    <!-- Admin/User List -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Admin & Superadmin List</h3>
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
                    // Fetch only Admins and Superadmins
                    $users = $conn->query("SELECT id, name, lname, email, phone, role, profile_image FROM users 
                                       WHERE role IN ('admin', 'superadmin') 
                                       ORDER BY role ASC");

                    while ($row = $users->fetch_assoc()) {
                        $profileImage = !empty($row['profile_image']) ? "../public/uploads/{$row['profile_image']}" : "../public/uploads/default.png";

                        echo "<tr>
                            <td class='p-3 border'><img src='{$profileImage}' class='w-10 h-10 rounded-full'></td>
                            <td class='p-3 border'>{$row['name']} {$row['lname']}</td>
                            <td class='p-3 border'>{$row['email']}</td>
                            <td class='p-3 border'>{$row['phone']}</td>
                            <td class='p-3 border capitalize font-bold text-blue-600'>{$row['role']}</td>
                            <td class='p-3 border flex space-x-3'>
                                <!-- Edit Button -->
                                <a href='superadmin_edit_admin.php?id={$row['id']}' class='text-blue-500'>Edit</a>

                                <!-- Delete Form -->
                                <form action='../process/superadmin_delete_admin.php' method='POST' onsubmit='return confirmDelete();'>
                                    <input type='hidden' name='user_id' value='{$row['id']}'>
                                    <button type='submit' class='text-red-500'>Delete</button>
                                </form>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript: Confirm Before Deleting -->
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this user?");
        }
    </script>

</div>