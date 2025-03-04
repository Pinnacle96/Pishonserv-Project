<?php
session_start();
include 'includes/db_connect.php';

$property_id = $_GET['id'] ?? null;

if (!$property_id) {
    die("Invalid property.");
}

// Fetch property details & owner details
$query = "SELECT p.*, u.name AS agent_name, u.profile_image AS agent_image, u.phone AS agent_phone
          FROM properties p
          JOIN users u ON p.owner_id = u.id
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    die("Property not found.");
}

// Extract images
$images = explode(',', $property['images']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - Property Details</title>
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
</head>

<body>

    <?php include 'includes/navbar.php'; ?>

    <!-- Property Details -->
    <section class="container mx-auto pt-40 py-12 px-4 md:px-10 lg:px-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Image Slider -->
            <div class="relative w-full h-[400px] overflow-hidden">
                <div class="slider" id="property-slider">
                    <?php foreach ($images as $index => $image): ?>
                    <img src="public/uploads/<?php echo $image; ?>"
                        class="w-full h-[400px] object-cover slider-image <?php echo $index === 0 ? '' : 'hidden'; ?>">
                    <?php endforeach; ?>
                </div>
                <button
                    class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev">‹</button>
                <button
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next">›</button>
            </div>

            <!-- Property Details -->
            <div>
                <h1 class="text-3xl font-bold text-[#092468]"><?php echo htmlspecialchars($property['title']); ?></h1>
                <p class="text-xl text-[#CC9933] font-semibold mt-2">
                    ₦<?php echo number_format($property['price'], 2); ?></p>
                <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($property['location']); ?></p>

                <!-- Status & Type -->
                <span class="mt-2 inline-block bg-[#092468] text-white px-3 py-1 rounded">
                    <?php echo ucfirst($property['status']); ?>
                </span>
                <span class="mt-2 inline-block bg-gray-500 text-white px-3 py-1 rounded">
                    <?php echo ucfirst($property['listing_type']); ?>
                </span>
                <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($property['description']); ?></p>

                <!-- Features -->
                <div class="mt-4 flex flex-wrap text-gray-500 text-sm">
                    <span class="mr-2">🛏️ <?php echo $property['bedrooms']; ?> Beds</span>
                    <span class="mr-2">🛁 <?php echo $property['bathrooms']; ?> Baths</span>
                    <span class="mr-2">📏 <?php echo $property['size']; ?> sqft</span>
                    <span class="mr-2">🚗 <?php echo $property['garage']; ?> Garage</span>
                </div>

                <!-- Agent Details -->
                <div class="mt-6 flex items-center">
                    <img src="public/uploads/<?php echo $property['agent_image'] ?: 'default.png'; ?>"
                        class="w-12 h-12 rounded-full mr-3">
                    <div>
                        <p class="text-lg font-bold"><?php echo htmlspecialchars($property['agent_name']); ?></p>
                        <p class="text-gray-600"><?php echo $property['agent_phone']; ?></p>
                    </div>
                </div>

                <!-- Wishlist & Inquiry -->
                <div class="mt-6 flex space-x-4">
                    <button class="wishlist-btn text-gray-500 hover:text-red-500 transition"
                        data-property-id="<?php echo $property['id']; ?>">
                        ❤️ Wishlist
                    </button>
                    <a href="contact-agent.php?id=<?php echo $property['owner_id']; ?>"
                        class="bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]">
                        Contact Agent
                    </a>
                </div>

                <!-- Checkout Button -->
                <?php if ($property['status'] == 'available'): ?>
                <a href="dashboard/checkout.php?property_id=<?php echo $property['id']; ?>"
                    class="mt-4 block text-center bg-[#092468] text-white px-4 py-2 rounded hover:bg-[#d88b1c]">
                    Buy / Rent Property
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Similar Properties -->
    <section class="container mx-auto py-12 px-4 md:px-10 lg:px-16">
        <h2 class="text-2xl font-bold text-[#092468] text-center">Similar Properties</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
            <?php
            // Fetch similar properties
            $query = "SELECT * FROM properties WHERE admin_approved = 1 AND status = 'available' AND id != ? ORDER BY created_at DESC LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $property_id);
            $stmt->execute();
            $similar_properties = $stmt->get_result();

            while ($similar = $similar_properties->fetch_assoc()) {
                // Extract first image from comma-separated list
                $imagesArray = explode(',', $similar['images']);
                $firstImage = !empty($imagesArray[0]) ? $imagesArray[0] : 'default.jpg';

                echo "
            <div class='border rounded-lg shadow-lg bg-white hover:shadow-xl transition'>
                <!-- Property Image -->
                <img src='public/uploads/" . htmlspecialchars($firstImage) . "' class='w-full h-48 object-cover rounded-t-lg'>

                <!-- Property Details -->
                <div class='p-4'>
                    <h3 class='text-[#092468] text-xl font-bold'>{$similar['title']}</h3>
                    <p class='text-[#CC9933] font-semibold'>₦" . number_format($similar['price'], 2) . "</p>
                    <p class='text-gray-600'>{$similar['location']}</p>

                    <!-- View Details Button -->
                    <a href='property.php?id={$similar['id']}' class='mt-2 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]'>
                        View Details
                    </a>
                </div>
            </div>
            ";
            }
            ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let images = document.querySelectorAll('.slider-image');
        let index = 0;

        function showImage(i) {
            images.forEach(img => img.classList.add('hidden'));
            images[i].classList.remove('hidden');
        }

        document.querySelector('.prev').addEventListener('click', function() {
            index = (index > 0) ? index - 1 : images.length - 1;
            showImage(index);
        });

        document.querySelector('.next').addEventListener('click', function() {
            index = (index < images.length - 1) ? index + 1 : 0;
            showImage(index);
        });
    });
    </script>

</body>

</html>