<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Property</h2>

    <!-- Form Action: Include Property ID in the URL -->
    <form action="agent_edit_property.php?id=<?php echo $property['id'] ?? ''; ?>" method="POST"
        enctype="multipart/form-data" class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <?php echo csrf_token_input(); ?>
        <input type="hidden" name="property_id" value="<?php echo $property['id'] ?? ''; ?>">

        <!-- Title -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Title</label>
            <input type="text" name="title" required class="w-full p-3 border rounded mt-1"
                value="<?php echo htmlspecialchars($property['title'] ?? '', ENT_QUOTES); ?>">
        </div>

        <!-- Price -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Price (₦)</label>
            <input type="number" name="price" required class="w-full p-3 border rounded mt-1" min="0" step="0.01"
                value="<?php echo $property['price'] ?? ''; ?>">
        </div>

        <!-- Location -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Location</label>
            <input type="text" name="location" required class="w-full p-3 border rounded mt-1"
                value="<?php echo htmlspecialchars($property['location'] ?? '', ENT_QUOTES); ?>">
        </div>

        <!-- Listing Type -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Listing Type</label>
            <select name="listing_type" required class="w-full p-3 border rounded mt-1">
                <option value="for_sale"
                    <?php echo ($property['listing_type'] ?? '') == 'for_sale' ? 'selected' : ''; ?>>For Sale</option>
                <option value="for_rent"
                    <?php echo ($property['listing_type'] ?? '') == 'for_rent' ? 'selected' : ''; ?>>For Rent</option>
                <option value="short_let"
                    <?php echo ($property['listing_type'] ?? '') == 'short_let' ? 'selected' : ''; ?>>Short Let</option>
            </select>
        </div>

        <!-- Property Type -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Property Type</label>
            <select name="type" required class="w-full p-3 border rounded mt-1">
                <?php
                $property_types = ["apartment", "office", "event_center", "hotel", "short_stay", "house", "villa", "condo", "townhouse", "duplex", "penthouse", "studio", "bungalow", "commercial", "warehouse", "retail", "land", "farmhouse", "mixed_use"];
                foreach ($property_types as $type) {
                    echo "<option value='$type' " . (($property['type'] ?? '') == $type ? 'selected' : '') . ">" . ucfirst(str_replace("_", " ", $type)) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Bedrooms -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bedrooms</label>
            <input type="number" name="bedrooms" min="0" required class="w-full p-3 border rounded mt-1"
                value="<?php echo $property['bedrooms'] ?? '0'; ?>">
        </div>

        <!-- Bathrooms -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bathrooms</label>
            <input type="number" name="bathrooms" min="0" required class="w-full p-3 border rounded mt-1"
                value="<?php echo $property['bathrooms'] ?? '0'; ?>">
        </div>

        <!-- Size -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Size (e.g., sqft, acres)</label>
            <input type="text" name="size" required class="w-full p-3 border rounded mt-1"
                value="<?php echo $property['size'] ?? ''; ?>">
        </div>

        <!-- Garage Spaces -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Garage Spaces</label>
            <input type="number" name="garage" min="0" required class="w-full p-3 border rounded mt-1"
                value="<?php echo $property['garage'] ?? '0'; ?>">
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Status</label>
            <select name="status" class="w-full p-3 border rounded mt-1">
                <option value="rented" <?php echo ($property['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending
                </option>
                <option value="available" <?php echo ($property['status'] ?? '') == 'available' ? 'selected' : ''; ?>>
                    Available</option>
                <option value="sold" <?php echo ($property['status'] ?? '') == 'sold' ? 'selected' : ''; ?>>Sold
                </option>
                <option value="rented" <?php echo ($property['status'] ?? '') == 'rented' ? 'selected' : ''; ?>>Rented
                </option>
            </select>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Description</label>
            <textarea name="description" required class="w-full p-3 border rounded mt-1"
                rows="4"><?php echo htmlspecialchars($property['description'] ?? '', ENT_QUOTES); ?></textarea>
        </div>

        <!-- Display Current Images -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Current Images</label>
            <div class="grid grid-cols-3 gap-2">
                <?php
                if (!empty($property['images'])) {
                    $images = explode(',', $property['images']);
                    foreach ($images as $image) {
                        echo "<img src='../public/uploads/$image' class='w-24 h-24 object-cover rounded' 
                                onerror=\"this.onerror=null; this.src='../public/uploads/default.png';\">";
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

        <!-- Submit Button -->
        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Update
            Property</button>
    </form>
</div>