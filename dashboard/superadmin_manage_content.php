<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Manage Admins</h2>
    <p class="text-gray-600 dark:text-gray-400">View, add, and remove admins.</p>

    <!-- Add Admin Form -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Add New Admin</h3>
        <form action="../process/superadmin_add_admin.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Full Name</label>
                <input type="text" name="name" required class="w-full p-3 border rounded mt-1">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" required class="w-full p-3 border rounded mt-1">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Password</label>
                <input type="password" name="password" required class="w-full p-3 border rounded mt-1">
            </div>
            <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Add
                Admin</button>
        </form>
    </div>

    <!-- Admin List -->
    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Admin List</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Name</th>
                        <th class="p-3 border">Email</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $admins = $conn->query("SELECT id, name, email FROM users WHERE role='admin'");
                    while ($row = $admins->fetch_assoc()) {
                        echo "<tr>
                                <td class='p-3 border'>{$row['name']}</td>
                                <td class='p-3 border'>{$row['email']}</td>
                                <td class='p-3 border'>
                                    <a href='../process/superadmin_delete_admin.php?id={$row['id']}' class='text-red-500'>Delete</a>
                                </td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>