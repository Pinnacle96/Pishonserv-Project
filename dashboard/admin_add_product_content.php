<div class="mt-6 max-w-3xl mx-auto bg-white dark:bg-gray-800 p-6 rounded shadow-md">
    <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">Add New Product</h2>
    <form method="POST" enctype="multipart/form-data">
        <label class="block mb-2 font-semibold">Product Name</label>
        <input type="text" name="name" required class="w-full p-2 border rounded mb-4">

        <label class="block mb-2 font-semibold">Description</label>
        <textarea name="description" required class="w-full p-2 border rounded mb-4" rows="4"></textarea>

        <label class="block mb-2 font-semibold">Price (₦)</label>
        <input type="number" name="price" step="0.01" required class="w-full p-2 border rounded mb-4">

        <label class="block mb-2 font-semibold">Category</label>
        <select name="category_id" required class="w-full p-2 border rounded mb-4">
            <?php while ($cat = $cat_result->fetch_assoc()): ?>
            <option value="<?php echo $cat['id']; ?>"><?php echo ucfirst($cat['name']); ?></option>
            <?php endwhile; ?>
        </select>

        <label class="block mb-2 font-semibold">Upload Product Image</label>
        <input type="file" name="image_file" accept="image/*" required class="w-full p-2 border rounded mb-4">

        <button type="submit" class="bg-[#F4A124] text-white px-6 py-2 rounded hover:bg-[#d88b1c]">
            Add Product
        </button>
    </form>
</div>

<!-- SweetAlert -->
<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<//?php if (isset($_SESSION['success'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '<//?php echo $_SESSION['success']; ?>'
});
<//?php unset($_SESSION['success']);
    endif; ?>

<//?php if (isset($_SESSION['error'])): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<//?php echo $_SESSION['error']; ?>'
});
<//?php unset($_SESSION['error']);
    endif; ?>
</script> -->