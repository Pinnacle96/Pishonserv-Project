<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Property</h2>

    <form action="" method="POST" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">

        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">

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
                <?php
                $property_types = ["house", "apartment", "land", "shortlet", "hotel"];
                foreach ($property_types as $type) {
                    echo "<option value='$type' " . (($property['type'] == $type) ? 'selected' : '') . ">" . ucfirst($type) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Listing Type -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Listing Type</label>
            <select name="listing_type" class="w-full p-3 border rounded mt-1">
                <?php
                $listing_types = ["for_sale", "for_rent", "short_let"];
                foreach ($listing_types as $listing) {
                    echo "<option value='$listing' " . (($property['listing_type'] == $listing) ? 'selected' : '') . ">" . ucfirst(str_replace("_", " ", $listing)) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Property Status -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Status</label>
            <select name="status" class="w-full p-3 border rounded mt-1">
                <?php
                $statuses = ["available", "sold", "rented"];
                foreach ($statuses as $status) {
                    echo "<option value='$status' " . (($property['status'] == $status) ? 'selected' : '') . ">" . ucfirst($status) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Bedrooms -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bedrooms</label>
            <input type="number" name="bedrooms" value="<?php echo $property['bedrooms']; ?>" min="0"
                class="w-full p-3 border rounded mt-1">
        </div>

        <!-- Bathrooms -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bathrooms</label>
            <input type="number" name="bathrooms" value="<?php echo $property['bathrooms']; ?>" min="0"
                class="w-full p-3 border rounded mt-1">
        </div>

        <!-- Size -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Size (sqft/acres)</label>
            <input type="text" name="size" value="<?php echo $property['size']; ?>"
                class="w-full p-3 border rounded mt-1">
        </div>

        <!-- Garage -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Garage</label>
            <input type="number" name="garage" value="<?php echo $property['garage']; ?>" min="0"
                class="w-full p-3 border rounded mt-1">
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Description</label>
            <textarea name="description" required
                class="w-full p-3 border rounded mt-1"><?php echo htmlspecialchars($property['description']); ?></textarea>
        </div>

        <!-- Current Images Preview -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Current Images</label>
            <div class="grid grid-cols-3 gap-2">
                <?php
                if (!empty($property['images'])) {
                    $images = explode(',', $property['images']);
                    foreach ($images as $image) {
                        echo "<img src='../public/uploads/$image' class='w-24 h-24 object-cover rounded' onerror=\"this.onerror=null; this.src='../public/uploads/default.png';\">";
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
            <input type="file" name="images[]" multiple accept="image/*" class="w-full p-3 border rounded mt-1"
                onchange="previewNewImages(event)">
            <small class="text-gray-500">Uploading new images will replace the existing ones.</small>
        </div>

        <!-- New Images Preview -->
        <div id="newImagesPreview" class="grid grid-cols-3 gap-2 mt-2"></div>

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

<!-- JavaScript: Image Preview Before Upload -->
<script>
    function previewNewImages(event) {
        const previewDiv = document.getElementById('newImagesPreview');
        previewDiv.innerHTML = ''; // Clear existing previews

        for (let file of event.target.files) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-24 h-24 object-cover rounded';
                previewDiv.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }
</script>