<!-- Page Layout -->
<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Admin</h2>
    <p class="text-gray-600 dark:text-gray-400">Modify admin details.</p>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Update Admin Information</h3>

        <?php if (isset($_SESSION['error'])): ?>
        <p class="text-red-500"><?php echo $_SESSION['error'];
                                    unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <label class="block text-gray-700 font-semibold">First Name</label>
                    <input type="text" name="name" required
                        value="<?php echo htmlspecialchars($admin['name'] ?? '', ENT_QUOTES); ?>"
                        class="w-full p-3 border rounded mt-1">
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block text-gray-700 font-semibold">Last Name</label>
                    <input type="text" name="lname" required
                        value="<?php echo htmlspecialchars($admin['lname'] ?? '', ENT_QUOTES); ?>"
                        class="w-full p-3 border rounded mt-1">
                </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" required
                    value="<?php echo htmlspecialchars($admin['email'] ?? '', ENT_QUOTES); ?>"
                    class="w-full p-3 border rounded mt-1">
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Phone</label>
                <input type="text" name="phone"
                    value="<?php echo htmlspecialchars($admin['phone'] ?? '', ENT_QUOTES); ?>"
                    class="w-full p-3 border rounded mt-1">
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Role</label>
                <select name="role" required class="w-full p-3 border rounded mt-1">
                    <option value="admin" <?php echo ($admin['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="superadmin" <?php echo ($admin['role'] === 'superadmin') ? 'selected' : ''; ?>>
                        Superadmin</option>
                </select>
            </div>

            <!-- Profile Image -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Current Profile Image</label>
                <img id="previewImage" src="../public/uploads/<?php echo $admin['profile_image']; ?>"
                    class="w-24 h-24 mx-auto rounded-full border mt-2"
                    onerror="this.onerror=null; this.src='../public/uploads/default.png';">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Change Profile Image</label>
                <input type="file" name="profile_image" accept="image/*" class="w-full p-3 border rounded mt-1"
                    onchange="previewImage(event)">
            </div>

            <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">
                Update Admin
            </button>
        </form>

        <a href="superadmin_manage.php" class="block text-center text-blue-500 mt-4">Back to Admin List</a>
    </div>
</div>

<!-- JavaScript: Image Preview -->
<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.getElementById('previewImage');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>