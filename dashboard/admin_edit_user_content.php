<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit User</h2>
    <p class="text-gray-600 dark:text-gray-400">Modify user details.</p>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Update User Information</h3>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="text-red-500"><?php echo $_SESSION['error'];
                                    unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['warning'])): ?>
            <p class="text-yellow-500"><?php echo $_SESSION['warning'];
                                        unset($_SESSION['warning']); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <label class="block text-gray-700 font-semibold">First Name</label>
                    <input type="text" name="name" required
                        value="<?php echo htmlspecialchars($user['name'] ?? '', ENT_QUOTES); ?>"
                        class="w-full p-3 border rounded mt-1">
                </div>
                <!-- Last Name -->
                <div>
                    <label class="block text-gray-700 font-semibold">Last Name</label>
                    <input type="text" name="lname" required
                        value="<?php echo htmlspecialchars($user['lname'] ?? '', ENT_QUOTES); ?>"
                        class="w-full p-3 border rounded mt-1">
                </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" required
                    value="<?php echo htmlspecialchars($user['email'] ?? '', ENT_QUOTES); ?>"
                    class="w-full p-3 border rounded mt-1">
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Phone</label>
                <input type="text" name="phone"
                    value="<?php echo htmlspecialchars($user['phone'] ?? '', ENT_QUOTES); ?>"
                    class="w-full p-3 border rounded mt-1">
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Role</label>
                <select name="role" required class="w-full p-3 border rounded mt-1">
                    <option value="buyer" <?php echo ($user['role'] === 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                    <option value="agent" <?php echo ($user['role'] === 'agent') ? 'selected' : ''; ?>>Agent</option>
                    <option value="owner" <?php echo ($user['role'] === 'owner') ? 'selected' : ''; ?>>Owner</option>
                    <option value="hotel_owner" <?php echo ($user['role'] === 'hotel_owner') ? 'selected' : ''; ?>>Hotel
                        Owner</option>
                </select>
            </div>

            <!-- Profile Image -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Current Profile Image</label>
                <img id="previewImage"
                    src="../public/uploads/<?php echo htmlspecialchars($user['profile_image'] ?? 'default.png', ENT_QUOTES); ?>"
                    class="w-24 h-24 mx-auto rounded-full border mt-2"
                    onerror="this.onerror=null; this.src='../public/uploads/default.png';">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Change Profile Image</label>
                <input type="file" name="profile_image" accept="image/*" class="w-full p-3 border rounded mt-1"
                    onchange="previewImage(event)">
            </div>

            <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">
                Update User
            </button>
        </form>

        <a href="admin_users.php" class="block text-center text-blue-500 mt-4">Back to User List</a>
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