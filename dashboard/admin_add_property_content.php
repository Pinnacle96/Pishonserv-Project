<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Add New Property</h2>

    <form action="" method="POST" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Title</label>
            <input type="text" name="title" required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Price</label>
            <input type="number" name="price" required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Location</label>
            <input type="text" name="location" required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Type</label>
            <select name="type" required class="w-full p-3 border rounded mt-1">
                <option value="house">House</option>
                <option value="apartment">Apartment</option>
                <option value="land">Land</option>
                <option value="shortlet">Shortlet</option>
                <option value="hotel">Hotel</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Listing Type</label>
            <select name="listing_type" required class="w-full p-3 border rounded mt-1">
                <option value="for_sale">For Sale</option>
                <option value="for_rent">For Rent</option>
                <option value="short_let">Short Let</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Status</label>
            <select name="status" required class="w-full p-3 border rounded mt-1">
                <option value="available">Available</option>
                <option value="sold">Sold</option>
                <option value="rented">Rented</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bedrooms</label>
            <input type="number" name="bedrooms" min="1" required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Bathrooms</label>
            <input type="number" name="bathrooms" min="1" required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Size (sqft)</label>
            <input type="text" name="size" required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Garage</label>
            <input type="number" name="garage" min="0" required class="w-full p-3 border rounded mt-1">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Description</label>
            <textarea name="description" required class="w-full p-3 border rounded mt-1"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Upload Images (Max 7)</label>
            <input type="file" name="images[]" multiple required class="w-full p-3 border rounded mt-1">
            <small class="text-gray-500">Images will be auto-compressed to save space.</small>
        </div>
        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Add
            Property</button>
    </form>
</div>