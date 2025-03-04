<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Property</h2>

    <form action="" method="POST" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">

        <!-- Property Title -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required
                class="w-full p-3 border rounded mt-1">
        </div>

        <!-- Price -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Price (₦)</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($property['price']); ?>" required
                class="w-full p-3 border rounded mt-1">
        </div>

        <!-- Location -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Location</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required
                class="w-full p-3 border rounded mt-1">
        </div>

        <!-- Property Type -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Property Type</label>
            <select name="type" class="w-full p-3 border rounded mt-1">
                <option value="house" <?php if ($property['type'] == 'house') echo 'selected'; ?>>House</option>
                <option value="apartment" <?php if ($property['type'] == 'apartment') echo 'selected'; ?>>Apartment
                </option>
                <option value="land" <?php if ($property['type'] == 'land') echo 'selected'; ?>>Land</option>
                <option value="shortlet" <?php if ($property['type'] == 'shortlet') echo 'selected'; ?>>Shortlet
                </option>
                <option value="hotel" <?php if ($property['type'] == 'hotel') echo 'selected'; ?>>Hotel</option>
            </select>
        </div>

        <!-- Listing Type -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Listing Type</label>
            <select name="listing_type" class="w-full p-3 border rounded mt-1">
                <option value="for_sale" <?php if ($property['listing_type'] == 'for_sale') echo 'selected'; ?>>For Sale
                </option>
                <option value="for_rent" <?php if ($property['listing_type'] == 'for_rent') echo 'selected'; ?>>For Rent
                </option>
                <option value="short_let" <?php if ($property['listing_type'] == 'short_let') echo 'selected'; ?>>Short
                    Let</option>
            </select>

        </div>

        <!-- Property Status -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Status</label>
            <select name="status" class="w-full p-3 border rounded mt-1">
                <option value="available" <?php if ($property['status'] == 'available') echo 'selected'; ?>>Available
                </option>
                <option value="sold" <?php if ($property['status'] == 'sold') echo 'selected'; ?>>Sold</option>
                <option value="rented" <?php if ($property['status'] == 'rented') echo 'selected'; ?>>Rented</option>
            </select>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Description</label>
            <textarea name="description" required
                class="w-full p-3 border rounded mt-1"><?php echo htmlspecialchars($property['description']); ?></textarea>
        </div>

        <!-- Current Images -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Current Images</label>
            <div class="grid grid-cols-3 gap-2">
                <?php 
                if (!empty($property['images'])) {
                    $images = explode(',', $property['images']);
                    foreach ($images as $image) {
                        if (!empty($image)) {
                            echo "<img src='../public/uploads/$image' class='w-24 h-24 object-cover rounded' onerror=\"this.onerror=null; this.src='../public/uploads/default.png';\">";
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

        <!-- Admin Approval -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Admin Approval</label>
            <select name="admin_approved" class="w-full p-3 border rounded mt-1">
                <option value="1" <?php if ($property['admin_approved'] == 1) echo 'selected'; ?>>Approved</option>
                <option value="0" <?php if ($property['admin_approved'] == 0) echo 'selected'; ?>>Pending Approval
                </option>
            </select>
        </div>

        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">
            Update Property
        </button>
    </form>
</div>