<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Property</h2>
    <form action="" method="POST" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Title</label>
            <input type="text" name="title"
                value="<?php echo htmlspecialchars($property['title'] ?? '', ENT_QUOTES); ?>" required
                class="w-full p-3 border rounded mt-1">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Price</label>
            <input type="number" name="price" value="<?php echo $property['price'] ?? ''; ?>" required
                class="w-full p-3 border rounded mt-1">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Location</label>
            <input type="text" name="location"
                value="<?php echo htmlspecialchars($property['location'] ?? '', ENT_QUOTES); ?>" required
                class="w-full p-3 border rounded mt-1">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Type</label>
            <select name="type" class="w-full p-3 border rounded mt-1">
                <option value="house" <?php echo ($property['type'] ?? '') == 'house' ? 'selected' : ''; ?>>House
                </option>
                <option value="apartment" <?php echo ($property['type'] ?? '') == 'apartment' ? 'selected' : ''; ?>>
                    Apartment</option>
                <option value="land" <?php echo ($property['type'] ?? '') == 'land' ? 'selected' : ''; ?>>Land</option>
                <option value="shortlet" <?php echo ($property['type'] ?? '') == 'shortlet' ? 'selected' : ''; ?>>
                    Shortlet</option>
                <option value="hotel" <?php echo ($property['type'] ?? '') == 'hotel' ? 'selected' : ''; ?>>Hotel
                </option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Status</label>
            <select name="status" class="w-full p-3 border rounded mt-1">
                <option value="available" <?php echo ($property['status'] ?? '') == 'available' ? 'selected' : ''; ?>>
                    Available</option>
                <option value="sold" <?php echo ($property['status'] ?? '') == 'sold' ? 'selected' : ''; ?>>Sold
                </option>
                <option value="rented" <?php echo ($property['status'] ?? '') == 'rented' ? 'selected' : ''; ?>>Rented
                </option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Description</label>
            <textarea name="description" required
                class="w-full p-3 border rounded mt-1"><?php echo htmlspecialchars($property['description'] ?? '', ENT_QUOTES); ?></textarea>
        </div>

        <!-- Display Current Images -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Current Images</label>
            <div class="grid grid-cols-3 gap-2">
                <?php 
                if (!empty($property['images'])) {
                    $images = explode(',', $property['images']);
                    foreach ($images as $image) {
                        if (!empty($image)) {
                            echo "<img src='../public/uploads/$image' class='w-24 h-24 object-cover rounded' 
                                    onerror=\"this.onerror=null; this.src='../public/uploads/default.png';\">";
                        }
                    }
                } else {
                    echo "<p class='text-gray-500'>No images available.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Upload New Images -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Upload New Images (Max 7)</label>
            <input type="file" name="images[]" multiple accept="image/*" class="w-full p-3 border rounded mt-1">
            <small class="text-gray-500">Uploading new images will replace the existing ones.</small>
        </div>

        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Update
            Property</button>
    </form>
</div>