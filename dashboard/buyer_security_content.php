<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Security Settings</h2>
    <p class="text-gray-600 dark:text-gray-400">Change your password & enable security options.</p>

    <form action="../process/change_password.php" method="POST"
        class="mt-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <label class="block text-gray-700 dark:text-gray-300">Current Password</label>
        <input type="password" name="current_password" required class="w-full p-3 border rounded mt-2">

        <label class="block text-gray-700 dark:text-gray-300 mt-4">New Password</label>
        <input type="password" name="new_password" required class="w-full p-3 border rounded mt-2">

        <button type="submit" class="mt-4 bg-[#F4A124] text-white px-6 py-2 rounded">Change Password</button>
    </form>
</div>