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
            <label class="block text-gray-700 font-semibold">Description</label>
            <textarea name="description" required class="w-full p-3 border rounded mt-1"></textarea>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Upload Image</label>
            <input type="file" name="image" required class="w-full p-3 border rounded mt-1">
        </div>
        <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Add
            Property</button>
    </form>
</div>