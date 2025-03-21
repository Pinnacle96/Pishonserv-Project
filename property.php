<?php
session_start();
include 'includes/db_connect.php';

// ✅ Validate and Sanitize Input
$property_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$property_id) {
    die("Invalid property.");
}

// ✅ Fetch Property Details & Owner Information
$query = "SELECT p.*, u.name AS agent_name, u.profile_image AS agent_image, u.phone AS agent_phone
          FROM properties p
          JOIN users u ON p.owner_id = u.id
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$property) {
    die("Property not found.");
}

// ✅ Handle Images Safely
$images = !empty($property['images']) ? explode(',', $property['images']) : ['default.jpg'];

// ✅ Check if the Property is in User's Wishlist
$in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?");
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $in_wishlist = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// ✅ Function to Format Description (if not already HTML)
function formatDescription($description)
{
    // If description is plain text, split into paragraphs and format
    $paragraphs = explode("\n\n", trim($description)); // Split by double newline (paragraphs)
    $formatted = '<div class="mt-4 text-gray-700">';
    // $formatted .= '<h2 class="text-2xl font-semibold text-[#092468]">Welcome to Your Dream Home!</h2>';

    // First paragraph (intro)
    if (!empty($paragraphs[0])) {
        $formatted .= '<p class="mt-2">' . htmlspecialchars($paragraphs[0]) . '</p>';
    }

    $features = explode("\n", trim($description)); // Split by single newline for list items
    return $formatted;
}
?>

<body class="bg-[#f5f7fa] text-[#092468] min-h-screen">

    <?php include 'includes/navbar.php'; ?>

    <!-- Property Details -->
    <section class="container mx-auto pt-40 py-12 px-4 md:px-10 lg:px-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Image Slider -->
            <div class="relative w-full h-[400px] overflow-hidden rounded-lg shadow-lg">
                <div class="slider" id="property-slider">
                    <?php foreach ($images as $index => $image): ?>
                        <img src="public/uploads/<?php echo htmlspecialchars($image); ?>"
                            class="w-full h-[400px] object-cover slider-image <?php echo $index === 0 ? '' : 'hidden'; ?>"
                            alt="Property Image">
                    <?php endforeach; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <button
                        class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev hover:bg-gray-600">‹</button>
                    <button
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next hover:bg-gray-600">›</button>
                <?php endif; ?>
            </div>

            <!-- Property Details -->
            <div class="property-details">
                <h1 class="text-3xl font-bold text-[#092468]"><?php echo htmlspecialchars($property['title']); ?></h1>
                <div class="mt-2 flex space-x-2">
                    <span class="inline-block bg-[#092468] text-white px-3 py-1 rounded text-sm">
                        <?php echo ucfirst($property['status']); ?>
                    </span>
                    <span class="inline-block bg-gray-500 text-white px-3 py-1 rounded text-sm">
                        <?php echo ucfirst(str_replace(['for_sale', 'for_rent', 'short_let'], ['Sale', 'Rent', 'Short Let'], $property['listing_type'])); ?>
                    </span>
                </div>
                <p class="text-xl text-[#CC9933] font-semibold mt-2">
                    ₦<?php echo number_format($property['price'], 2); ?> per
                    <?php echo in_array($property['listing_type'], ['short_let', 'hotel']) ? 'day' : 'unit'; ?>
                </p>
                <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($property['location']); ?></p>

                <!-- Formatted Description -->
                <?php echo formatDescription($property['description']); ?>

                <!-- Wishlist & Inquiry -->
                <div class="mt-6 flex space-x-4">
                    <button
                        class="wishlist-btn <?php echo $in_wishlist ? 'text-red-500' : 'text-gray-500'; ?> hover:text-red-500 transition text-lg"
                        data-property-id="<?php echo $property['id']; ?>"
                        data-in-wishlist="<?php echo $in_wishlist ? '1' : '0'; ?>">
                        <?php echo $in_wishlist ? '❤️ Added to Wishlist' : '🤍 Add to Wishlist'; ?>
                    </button>
                    <a href="contact-agent.php?id=<?php echo $property['owner_id']; ?>"
                        class="bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c] text-lg">
                        Contact Agent
                    </a>
                </div>

                <!-- Booking Form -->
                <?php if (in_array($property['listing_type'], ['short_let', 'hotel'])): ?>
                    <form action="book_property.php" method="POST" class="mt-6 bg-gray-100 p-4 rounded-lg">
                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                        <label class="block font-semibold text-[#092468]">Check-in Date:</label>
                        <input type="date" name="check_in_date" required class="w-full p-2 border rounded mt-1"
                            value="<?php echo isset($_SESSION['booking_data']['check_in_date']) ? $_SESSION['booking_data']['check_in_date'] : ''; ?>">
                        <label class="block font-semibold text-[#092468] mt-2">Check-out Date:</label>
                        <input type="date" name="check_out_date" required class="w-full p-2 border rounded mt-1"
                            value="<?php echo isset($_SESSION['booking_data']['check_out_date']) ? $_SESSION['booking_data']['check_out_date'] : ''; ?>">
                        <button type="submit"
                            class="mt-4 bg-[#092468] text-white px-4 py-2 rounded hover:bg-blue-700 w-full">
                            Book Now
                        </button>
                    </form>
                <?php else: ?>
                    <a href="book_property.php?property_id=<?php echo $property['id']; ?>"
                        class="mt-6 block bg-[#092468] text-white px-4 py-2 rounded hover:bg-blue-700 text-center text-lg">
                        Book Now
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
            // ✅ Fetch Similar Properties
            $query = "SELECT * FROM properties WHERE admin_approved = 1 AND status = 'available' 
                      AND id != ? AND listing_type = ? 
                      ORDER BY created_at DESC LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $property_id, $property['listing_type']);
            $stmt->execute();
            $similar_properties = $stmt->get_result();

            while ($similar = $similar_properties->fetch_assoc()) {
                $imagesArray = explode(',', $similar['images']);
                $firstImage = !empty($imagesArray[0]) ? $imagesArray[0] : 'default.jpg';

                echo "
                <div class='border rounded-lg shadow-lg bg-white hover:shadow-xl transition'>
                    <img src='public/uploads/" . htmlspecialchars($firstImage) . "' class='w-full h-48 object-cover rounded-t-lg'>
                    <div class='p-4'>
                        <h3 class='text-[#092468] text-xl font-bold'>" . htmlspecialchars($similar['title']) . "</h3>
                        <p class='text-[#CC9933] font-semibold'>₦" . number_format($similar['price'], 2) . "</p>
                        <p class='text-gray-600'>" . htmlspecialchars($similar['location']) . "</p>
                        <a href='property.php?id={$similar['id']}' class='mt-2 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]'>
                            View Details
                        </a>
                    </div>
                </div>";
            }
            $stmt->close();
            ?>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        function checkBookingLogin(propertyId) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                var redirectUrl = "book_property.php?property_id=" + propertyId;
                document.cookie = "redirect_after_login=" + redirectUrl + "; path=/";
                window.location.href = "auth/login.php";
                return false;
            <?php else: ?>
                return true;
            <?php endif; ?>
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Image slider functionality
            let images = document.querySelectorAll('.slider-image');
            let index = 0;

            function showImage(i) {
                images.forEach(img => img.classList.add('hidden'));
                images[i].classList.remove('hidden');
            }

            if (images.length > 1) {
                document.querySelector('.prev').addEventListener('click', function() {
                    index = (index > 0) ? index - 1 : images.length - 1;
                    showImage(index);
                });

                document.querySelector('.next').addEventListener('click', function() {
                    index = (index < images.length - 1) ? index + 1 : 0;
                    showImage(index);
                });
            }

            // Wishlist functionality
            document.querySelector('.wishlist-btn').addEventListener('click', async function() {
                const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
                if (!isLoggedIn) {
                    alert('Please log in or register to add properties to your wishlist.');
                    window.location.href = 'auth/login.php';
                    return;
                }

                const propertyId = this.getAttribute('data-property-id');
                const isInWishlist = this.getAttribute('data-in-wishlist') === '1';

                try {
                    const response = await fetch('wishlist_toggle.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            property_id: propertyId,
                            action: isInWishlist ? 'remove' : 'add'
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    if (data.success) {
                        this.setAttribute('data-in-wishlist', data.inWishlist ? '1' : '0');
                        this.innerHTML = data.inWishlist ? '❤️ Added to Wishlist' :
                            '🤍 Add to Wishlist';
                        this.classList.toggle('text-red-500', data.inWishlist);
                        this.classList.toggle('text-gray-500', !data.inWishlist);
                    } else {
                        alert(data.message || 'Failed to update wishlist.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the wishlist.');
                }
            });
        });
    </script>
</body>

</html>