<?php
//session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in first.";
    header("Location: ../auth/login.php");
    exit();
}
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Profile Settings</h2>
    <p class="text-gray-600 dark:text-gray-400">Update your personal information.</p>

    <form action="../process/update_profile.php" method="POST"
        class="mt-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <label class="block text-gray-700 dark:text-gray-300">Name</label>
        <input type="text" name="name" value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>"
            class="w-full p-3 border rounded mt-2">

        <label class="block text-gray-700 dark:text-gray-300 mt-4">Email</label>
        <input type="email" name="email"
            value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>"
            class="w-full p-3 border rounded mt-2">


        <button type="submit" class="mt-4 bg-[#F4A124] text-white px-6 py-2 rounded">Save Changes</button>
    </form>
</div>