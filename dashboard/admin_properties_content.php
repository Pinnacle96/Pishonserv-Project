<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Manage Properties</h2>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md overflow-x-auto">
        <h3 class="text-xl font-bold mb-4">All Property Listings</h3>
        <a href="admin_add_property.php"
            class="bg-[#F4A124] text-white px-6 py-2 rounded hover:bg-[#d88b1c] mt-4 inline-block">
            + Add New Property
        </a>

        <table class="w-full min-w-max border-collapse border border-gray-200 dark:border-gray-700">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                    <th class="p-3 border">Images</th>
                    <th class="p-3 border">Title</th>
                    <th class="p-3 border">Owner</th>
                    <th class="p-3 border">Type</th>
                    <th class="p-3 border">Listing Type</th>
                    <th class="p-3 border">Status</th>
                    <th class="p-3 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="p-3 border">
                        <!-- Image Slider -->
                        <div class="relative w-40 h-24 overflow-hidden">
                            <div class="image-slider" id="slider-<?php echo $row['id']; ?>">
                                <?php 
                                $images = explode(',', $row['images']);
                                foreach ($images as $index => $image): ?>
                                <img src="../public/uploads/<?php echo $image; ?>"
                                    class="w-full h-full object-cover absolute transition-opacity duration-500"
                                    style="opacity: <?php echo $index === 0 ? '1' : '0'; ?>">
                                <?php endforeach; ?>
                            </div>
                            <button
                                class="prev absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-700 text-white px-2 py-1 rounded"
                                onclick="prevSlide(<?php echo $row['id']; ?>)">‹</button>
                            <button
                                class="next absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-700 text-white px-2 py-1 rounded"
                                onclick="nextSlide(<?php echo $row['id']; ?>)">›</button>
                        </div>
                    </td>
                    <td class="p-3 border"><?php echo $row['title']; ?></td>
                    <td class="p-3 border"><?php echo $row['owner_name']; ?></td>
                    <td class="p-3 border"><?php echo ucfirst($row['type']); ?></td>
                    <td class="p-3 border"><?php echo ucfirst($row['listing_type']); ?></td>
                    <td class="p-3 border">
                        <?php if ($row['admin_approved'] == 1): ?>
                        <span class="text-green-500">Approved</span>
                        <?php else: ?>
                        <span class="text-yellow-500">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-3 border">
                        <?php if ($row['admin_approved'] == 0): ?>
                        <a href="process_property.php?action=approve&id=<?php echo $row['id']; ?>"
                            class="text-green-500">Approve</a> |
                        <a href="process_property.php?action=reject&id=<?php echo $row['id']; ?>"
                            class="text-red-500">Reject</a> |
                        <?php endif; ?>
                        <a href="admin_edit_property.php?id=<?php echo $row['id']; ?>" class="text-blue-500">Edit</a> |
                        <a href="admin_delete_property.php?id=<?php echo $row['id']; ?>" class="text-red-500">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Image Slider Script -->
<script>
function nextSlide(propertyId) {
    let slides = document.querySelectorAll(`#slider-${propertyId} img`);
    let current = Array.from(slides).findIndex(slide => slide.style.opacity === "1");
    slides[current].style.opacity = "0";
    let next = (current + 1) % slides.length;
    slides[next].style.opacity = "1";
}

function prevSlide(propertyId) {
    let slides = document.querySelectorAll(`#slider-${propertyId} img`);
    let current = Array.from(slides).findIndex(slide => slide.style.opacity === "1");
    slides[current].style.opacity = "0";
    let prev = (current - 1 + slides.length) % slides.length;
    slides[prev].style.opacity = "1";
}
</script>