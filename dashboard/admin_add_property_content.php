<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Add New Property</h2>

    <form action="" method="POST" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 mt-6 p-6 rounded-lg shadow-md">
        <!-- Title -->
        <div class="mb-4">
            <label for="title" class="block text-gray-700 dark:text-gray-300 font-semibold">Title</label>
            <input type="text" id="title" name="title" required
                class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                placeholder="e.g., Modern 3-Bedroom Villa">
            <small class="text-gray-500 dark:text-gray-400">Enter a descriptive title for the property.</small>
        </div>

        <!-- Price and Location -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="price" class="block text-gray-700 dark:text-gray-300 font-semibold">Price (₦)</label>
                <input type="number" id="price" name="price" required step="0.01" min="0"
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="e.g., 5000000">
            </div>
            <div>
                <label for="location" class="block text-gray-700 dark:text-gray-300 font-semibold">Location</label>
                <input type="text" id="location" name="location" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="e.g., Lekki Phase 1, Lagos">
            </div>
        </div>

        <!-- Type and Listing Type -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="type" class="block text-gray-700 dark:text-gray-300 font-semibold">Property Type</label>
                <select id="type" name="type" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
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
            <div>
                <label for="listing_type" class="block text-gray-700 dark:text-gray-300 font-semibold">Listing
                    Type</label>
                <select id="listing_type" name="listing_type" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
                    <option value="" disabled selected>Select Listing Type</option>
                    <option value="for_sale">For Sale</option>
                    <option value="for_rent">For Rent</option>
                    <option value="short_let">Short Let</option>
                </select>
            </div>
        </div>

        <!-- Bedrooms, Bathrooms, Garage -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="bedrooms" class="block text-gray-700 dark:text-gray-300 font-semibold">Bedrooms</label>
                <input type="number" id="bedrooms" name="bedrooms" min="1" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="e.g., 3">
            </div>
            <div>
                <label for="bathrooms" class="block text-gray-700 dark:text-gray-300 font-semibold">Bathrooms</label>
                <input type="number" id="bathrooms" name="bathrooms" min="1" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="e.g., 2">
            </div>
            <div>
                <label for="garage" class="block text-gray-700 dark:text-gray-300 font-semibold">Garage Spaces</label>
                <input type="number" id="garage" name="garage" min="0" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="e.g., 1">
            </div>
        </div>

        <!-- Size and Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="size" class="block text-gray-700 dark:text-gray-300 font-semibold">Size (sqft)</label>
                <input type="text" id="size" name="size" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="e.g., 1500">
            </div>
            <div>
                <label for="status" class="block text-gray-700 dark:text-gray-300 font-semibold">Status</label>
                <select id="status" name="status" required
                    class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
                    <option value="" disabled selected>Select Status</option>
                    <option value="available">Available</option>
                    <option value="sold">Sold</option>
                    <option value="rented">Rented</option>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-gray-700 dark:text-gray-300 font-semibold">Description</label>
            <textarea id="description" name="description" required
                class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                rows="4" placeholder="e.g., Spacious villa with modern amenities..."></textarea>
        </div>

        <!-- Images -->
        <div class="mb-4">
            <label for="images" class="block text-gray-700 dark:text-gray-300 font-semibold">Upload Images (Max
                7)</label>
            <input type="file" id="images" name="images[]" multiple required accept="image/*"
                class="w-full p-3 border rounded-lg mt-1 bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
            <small class="text-gray-500 dark:text-gray-400">Images will be auto-compressed to save space. Accepted: JPG,
                PNG, GIF.</small>
            <div id="image-preview" class="mt-2 flex flex-wrap gap-2"></div>
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="bg-[#F4A124] text-white w-full py-3 rounded-lg hover:bg-[#d88b1c] focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
            Add Property
        </button>
    </form>
</div>

<!-- JavaScript for Image Preview -->
<script>
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = ''; // Clear previous previews
        const files = e.target.files;

        if (files.length > 7) {
            alert('You can upload a maximum of 7 images.');
            e.target.value = ''; // Reset input
            return;
        }

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!file.type.startsWith('image/')) continue;

            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.createElement('img');
                img.src = event.target.result;
                img.classList.add('w-20', 'h-20', 'object-cover', 'rounded', 'border', 'border-gray-300',
                    'dark:border-gray-600');
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
</script>