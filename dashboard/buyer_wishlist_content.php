<?php
//session_start();
include '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must log in to view your wishlist.";
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch Wishlist Properties
$wishlist_stmt = $conn->prepare("
    SELECT p.id, p.title, p.location, p.price, p.images 
    FROM wishlist w
    JOIN properties p ON w.property_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist = $wishlist_stmt->get_result();
?>

<div class="mt-6">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Wishlist</h2>
    <p class="text-gray-600 dark:text-gray-400">Your saved properties.</p>

    <?php if ($wishlist->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <?php while ($property = $wishlist->fetch_assoc()): ?>
                <?php
                // Extract images from the database
                $images = explode(',', $property['images']);
                $firstImage = !empty($images[0]) ? $images[0] : 'default.jpg';
                ?>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg">
                    <!-- Image Slider -->
                    <div class="relative w-full h-40 overflow-hidden">
                        <div class="slider" id="slider-<?php echo $property['id']; ?>">
                            <?php foreach ($images as $index => $image): ?>
                                <img src="../public/uploads/<?php echo htmlspecialchars($image); ?>" class="w-full h-40 object-cover rounded slider-image 
                                     <?php echo $index === 0 ? '' : 'hidden'; ?>"
                                    data-slider-id="slider-<?php echo $property['id']; ?>">
                            <?php endforeach; ?>
                        </div>
                        <button
                            class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev"
                            data-slider-id="slider-<?php echo $property['id']; ?>">‹</button>
                        <button
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next"
                            data-slider-id="slider-<?php echo $property['id']; ?>">›</button>
                    </div>

                    <h3 class="text-xl font-semibold mt-3"><?php echo htmlspecialchars($property['title']); ?></h3>
                    <p class="text-gray-500">Location: <?php echo htmlspecialchars($property['location']); ?></p>
                    <p class="text-gray-500">Price: ₦<?php echo number_format($property['price'], 2); ?></p>
                    <div class="flex justify-between mt-4">
                        <a href="property.php?id=<?php echo $property['id']; ?>" class="text-blue-500">View</a>
                        <button class="text-red-500 remove-wishlist"
                            data-property-id="<?php echo $property['id']; ?>">Remove</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500 mt-4">No properties in your wishlist.</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.slider').forEach(slider => {
            let images = slider.querySelectorAll('.slider-image');
            let index = 0;
            let sliderId = slider.getAttribute('id');

            function showImage(i) {
                images.forEach(img => img.classList.add('hidden'));
                images[i].classList.remove('hidden');
            }

            document.querySelector(`[data-slider-id="${sliderId}"].prev`).addEventListener('click',
                function() {
                    index = (index > 0) ? index - 1 : images.length - 1;
                    showImage(index);
                });

            document.querySelector(`[data-slider-id="${sliderId}"].next`).addEventListener('click',
                function() {
                    index = (index < images.length - 1) ? index + 1 : 0;
                    showImage(index);
                });
        });

        // ✅ Handle Wishlist Removal
        document.querySelectorAll('.remove-wishlist').forEach(button => {
            button.addEventListener('click', function() {
                let propertyId = this.getAttribute('data-property-id');

                fetch('remove_wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `property_id=${propertyId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.bg-white').remove();
                        } else {
                            alert("Error removing from wishlist.");
                        }
                    });
            });
        });
    });
</script>

<?php $wishlist_stmt->close(); ?>