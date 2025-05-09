<div class="mt-6 max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 rounded shadow-md">
    <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">Edit Product</h2>
    <form method="POST">
        <label class="block mb-2 font-semibold">Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required
            class="w-full p-2 border rounded mb-4">

        <label class="block mb-2 font-semibold">Description</label>
        <textarea name="description" required class="w-full p-2 border rounded mb-4"
            rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label class="block mb-2 font-semibold">Price (₦)</label>
        <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required
            class="w-full p-2 border rounded mb-4">

        <label class="block mb-2 font-semibold">Category</label>
        <select name="category_id" required class="w-full p-2 border rounded mb-4">
            <?php while ($cat = $cat_result->fetch_assoc()): ?>
            <option value="<?php echo $cat['id']; ?>"
                <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                <?php echo ucfirst($cat['name']); ?>
            </option>
            <?php endwhile; ?>
        </select>

        <label class="block mb-2 font-semibold">Upload New Image (optional)</label>
        <input type="file" name="image_file" accept="image/*" class="w-full p-2 border rounded mb-4">

        <?php
        $img_url = '/pishonserv/' . ltrim($product['image'], '/');
        ?>

        <p class="text-sm text-gray-500 mb-4">
            Current image:
            <a href="<?php echo $img_url; ?>" target="_blank" class="text-blue-600 underline">View</a>
        </p>


        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update Product</button>
    </form>
</div>