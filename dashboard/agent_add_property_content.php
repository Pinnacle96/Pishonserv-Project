<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Add New Property</h2>

    <form action="../process/agent_add_property.php" method="POST" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <?php echo csrf_token_input(); ?>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Title</label>
            <input type="text" name="title" required class="w-full p-3 border rounded mt-1"
                placeholder="e.g., Luxurious 3-Bedroom Apartment">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Price (₦)</label>
            <input type="number" name="price" required class="w-full p-3 border rounded mt-1" min="0" step="0.01"
                placeholder="e.g., 5000000">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Location</label>
            <input type="text" name="location" required class="w-full p-3 border rounded mt-1"
                placeholder="e.g., Lekki Phase 1, Lagos">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Listing Type</label>
            <select name="listing_type" required class="w-full p-3 border rounded mt-1">
                <option value="" disabled selected>Select Listing Type</option>
                <option value="for_sale">For Sale</option>
                <option value="for_rent">For Rent</option>
                <option value="short_let">Short Let</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Property Type</label>
            <select name="type" required class="w-full p-3 border rounded mt-1">
                <option value="" disabled selected>Select Property Type</option>
                <option value="apartment">Apartment</option>
                <option value="office">Office</option>
                <option value="event_center">Event Center</option>
                <option value="hotel">Hotel</option>
                <option value="short_stay">Short Stay</option>
                <option value="house">House</option>
                <option value="villa">Villa</option>
                <option value="condo">Condo</option>
                <option value="townhouse">Townhouse</option>
                <option value="duplex">Duplex</option>
                <option value="penthouse">Penthouse</option>
                <option value="studio">Studio</option>
                <option value="bungalow">Bungalow</option>
                <option value="commercial">Commercial</option>
                <option value="warehouse">Warehouse</option>
                <option value="retail">Retail</option>
                <option value="land">Land</option>
                <option value="farmhouse">Farmhouse</option>
                <option value="mixed_use">Mixed Use</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bedrooms</label>
            <input type="number" name="bedrooms" min="0" required class="w-full p-3 border rounded mt-1"
                placeholder="e.g., 3">
            <small class="text-gray-500">Enter 0 for properties like land with no bedrooms.</small>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bathrooms</label>
            <input type="number" name="bathrooms" min="0" required class="w-full p-3 border rounded mt-1"
                placeholder="e.g., 2">
            <small class="text-gray-500">Enter 0 for properties like land with no bathrooms.</small>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Size (e.g., sqft, acres)</label>
            <input type="text" name="size" required class="w-full p-3 border rounded mt-1"
                placeholder="e.g., 2000 sqft or 5 acres">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Garage Spaces</label>
            <input type="number" name="garage" min="0" required class="w-full p-3 border rounded mt-1"
                placeholder="e.g., 1">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Description</label>
            <textarea name="description" required class="w-full p-3 border rounded mt-1" rows="4"
                placeholder="Describe the property features..."></textarea>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Upload Images</label>
            <input type="file" name="images[]" multiple accept="image/*" class="w-full p-3 border rounded mt-1">
            <small class="text-gray-500">Upload up to 7 images (JPEG, PNG, etc.).</small>
        </div>

        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">
            Add Property
        </button>
    </form>
</div>