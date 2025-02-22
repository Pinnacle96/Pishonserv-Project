<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">My Properties</h2>
    <a href="agent_add_property.php"
        class="bg-[#F4A124] text-white px-4 py-2 rounded hover:bg-[#d88b1c] mt-4 inline-block">
        + Add New Property
    </a>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Your Listings</h3>
        <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                    <th class="p-3 border">Image</th>
                    <th class="p-3 border">Title</th>
                    <th class="p-3 border">Price</th>
                    <th class="p-3 border">Status</th>
                    <th class="p-3 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="p-3 border"><img src="../public/images/<?php echo $row['image']; ?>"
                            class="w-16 h-16 rounded"></td>
                    <td class="p-3 border"><?php echo $row['title']; ?></td>
                    <td class="p-3 border">₦<?php echo number_format($row['price'], 2); ?></td>
                    <td class="p-3 border"><?php echo ucfirst($row['type']); ?></td>
                    <td class="p-3 border">
                        <a href="agent_edit_property.php?id=<?php echo $row['id']; ?>" class="text-blue-500">Edit</a> |
                        <a href="agent_delete_property.php?id=<?php echo $row['id']; ?>" class="text-red-500">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>