<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Manage Products</h2>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md overflow-x-auto">
        <h3 class="text-xl font-bold mb-4">All Product Listings</h3>
        <a href="admin_add_product.php"
            class="bg-[#F4A124] text-white px-6 py-2 rounded hover:bg-[#d88b1c] mb-4 inline-block">
            + Add New Product
        </a>

        <table id="productsTable" class="min-w-[1000px] border-collapse border border-gray-200 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                    <th class="p-3 border">Image</th>
                    <th class="p-3 border">Name</th>
                    <th class="p-3 border">Category</th>
                    <th class="p-3 border">Price</th>
                    <th class="p-3 border">Description</th>
                    <th class="p-3 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="p-3 border">
                        <img src="../<?php echo htmlspecialchars($row['image']); ?>" alt="Product"
                            class="w-32 h-20 object-cover rounded">
                    </td>
                    <td class="p-3 border"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td class="p-3 border"><?php echo ucfirst(htmlspecialchars($row['category_name'])); ?></td>
                    <td class="p-3 border">₦<?php echo number_format($row['price'], 2); ?></td>
                    <td class="p-3 border"><?php echo htmlspecialchars(substr($row['description'], 0, 60)) . '...'; ?>
                    </td>
                    <td class="p-3 border">
                        <a href="admin_edit_product.php?id=<?php echo $row['id']; ?>"
                            class="text-blue-500 hover:underline">Edit</a> |
                        <a href="admin_delete_product.php?id=<?php echo $row['id']; ?>"
                            class="text-red-500 hover:underline">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#productsTable').DataTable({
        paging: true,
        ordering: true,
        searching: true,
        autoWidth: false
    });
});
</script>