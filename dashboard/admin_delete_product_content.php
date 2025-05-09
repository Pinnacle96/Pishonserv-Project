<div class="mt-10 max-w-xl mx-auto bg-white dark:bg-gray-800 p-6 rounded shadow-md text-center">
    <h2 class="text-2xl font-bold mb-4 text-red-600">Confirm Delete</h2>
    <p class="mb-6 text-lg text-gray-700 dark:text-gray-200">Are you sure you want to delete
        <strong><?php echo htmlspecialchars($product['name']); ?></strong>?</p>
    <form method="POST" class="flex justify-center space-x-4">
        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">Yes, Delete</button>
        <a href="admin_products.php" class="bg-gray-300 text-black px-6 py-2 rounded hover:bg-gray-400">Cancel</a>
    </form>
</div>