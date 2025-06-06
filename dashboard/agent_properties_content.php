<!-- Include SwiperJS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">My Properties</h2>
    <a href="agent_add_property.php"
        class="bg-[#F4A124] text-white px-4 py-2 rounded hover:bg-[#d88b1c] mt-4 inline-block">
        + Add New Property
    </a>

    <div class="bg-white dark:bg-gray-800 mt-6 p-6 rounded shadow-md">
        <h3 class="text-xl font-bold mb-4">Your Listings</h3>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-300">
                        <th class="p-3 border">Images</th>
                        <th class="p-3 border">Title</th>
                        <th class="p-3 border">Price</th>
                        <th class="p-3 border">Type</th>
                        <th class="p-3 border">Status</th>
                        <th class="p-3 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="p-3 border">
                                <div class="w-32 h-24 overflow-hidden">
                                    <div class="swiper mySwiper w-full h-full rounded" data-id="<?php echo $row['id']; ?>">
                                        <div class="swiper-wrapper">
                                            <?php
                                            // Debug: Output raw images string
                                            echo "<!-- Raw images: " . htmlspecialchars($row['images']) . " -->";

                                            // Trim and explode images string, filter out empty values
                                            $image_list = array_filter(explode(",", trim($row['images'])));
                                            if (empty($image_list)) {
                                                echo "<div class='swiper-slide'><img src='../public/uploads/default.jpg' class='w-full h-24 object-cover rounded' alt='No Image'></div>";
                                            } else {
                                                foreach ($image_list as $image) {
                                                    $image = trim($image); // Remove any whitespace
                                                    $imagePath = "../public/uploads/" . $image;
                                                    // Debug: Check if file exists
                                                    $fileExists = file_exists($imagePath) ? "Yes" : "No";
                                                    echo "<!-- Image: $image, Exists: $fileExists -->";
                                            ?>
                                                    <div class="swiper-slide">
                                                        <img src="<?php echo $imagePath; ?>"
                                                            class="w-full h-24 object-cover rounded" alt="Property Image"
                                                            onerror="this.src='../public/uploads/default.jpg'">
                                                    </div>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="swiper-pagination"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 border"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="p-3 border">₦<?php echo number_format($row['price'], 2); ?></td>
                            <td class="p-3 border"><?php echo ucfirst($row['listing_type']); ?></td>
                            <td class="p-3 border">
                                <?php if ($row['admin_approved'] == 0): ?>
                                    <span class="text-yellow-500 font-semibold">Pending Approval</span>
                                <?php else: ?>
                                    <span class="text-green-500 font-semibold"><?php echo ucfirst($row['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 border">
                                <a href="agent_edit_property.php?id=<?php echo $row['id']; ?>"
                                    class="text-blue-500">Edit</a> |
                                <a href="agent_delete_property.php?id=<?php echo $row['id']; ?>"
                                    class="text-red-500">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Initialize Swiper -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Swiper for each instance
        document.querySelectorAll('.mySwiper').forEach(function(swiperElement) {
            new Swiper(swiperElement, {
                loop: true,
                pagination: {
                    el: swiperElement.querySelector('.swiper-pagination'),
                    clickable: true
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                },
                slidesPerView: 1,
            });
        });
    });
</script>