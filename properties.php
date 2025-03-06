<?php include 'includes/db_connect.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- Page Header -->
<section class="bg-gray-100 py-16 text-center mt-16">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-[#092468]">Find Your Perfect Home</h1>
        <p class="text-gray-600 text-lg mt-2">Browse through our latest property listings</p>
    </div>
</section>

<!-- Main Content -->
<div class="container mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-4 gap-8 px-6 md:px-10 lg:px-16">

    <!-- Filters Toggle Button (Mobile Only) -->
    <button id="filter-toggle" class="md:hidden w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c] mb-4">
        Show Filters
    </button>

    <!-- Sidebar Filters -->
    <aside class="hidden md:block col-span-1 bg-white p-6 rounded-lg shadow" id="filters">
        <h2 class="text-lg font-semibold text-[#092468] mb-4">Filter Results</h2>
        <form action="#" method="GET" class="space-y-4">
            <select name="type" class="w-full p-3 border rounded text-sm md:text-base">
                <option value="">Property Type</option>
                <option value="apartment">Apartment</option>
                <option value="hotel">Hotel</option>
                <option value="shortlet">Shortlet</option>
                <option value="house">House</option>
                <option value="land">Land</option>
            </select>
            <input type="text" name="location" class="w-full p-3 border rounded text-sm md:text-base"
                placeholder="Location">
            <input type="number" name="min_price" class="w-full p-3 border rounded text-sm md:text-base"
                placeholder="Min Price">
            <input type="number" name="max_price" class="w-full p-3 border rounded text-sm md:text-base"
                placeholder="Max Price">
            <button type="submit"
                class="w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c] text-sm md:text-base">Apply
                Filters</button>
        </form>
    </aside>

    <!-- Property Listings -->
    <section class="col-span-1 md:col-span-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Fetch featured properties
            $query = "SELECT p.*, u.name AS agent_name, u.profile_image AS agent_image 
                  FROM properties p 
                  JOIN users u ON p.owner_id = u.id
                  WHERE p.admin_approved = 1 
                  ORDER BY p.created_at DESC LIMIT 6";
            $result = $conn->query($query);

            while ($property = $result->fetch_assoc()) {
                $images = explode(',', $property['images']);
                $firstImage = !empty($images[0]) ? $images[0] : 'default.jpg';

                // Fix for status and listing_type
                $status = isset($property['status']) && !empty($property['status']) ? ucfirst($property['status']) : 'Unknown';
                $listingType = isset($property['listing_type']) && !empty($property['listing_type']) ? ucfirst($property['listing_type']) : 'Unknown';

                // Dynamic status colors
                $statusClass = "bg-gray-500";
                if ($property['status'] == 'available') $statusClass = "bg-green-500";
                if ($property['status'] == 'booked') $statusClass = "bg-yellow-500";
                if ($property['status'] == 'sold') $statusClass = "bg-red-500";
                if ($property['status'] == 'rented') $statusClass = "bg-blue-500";

                // Ensure agent details exist
                $agentImage = !empty($property['agent_image']) ? "public/uploads/{$property['agent_image']}" : "public/uploads/default.png";
                $agentName = !empty($property['agent_name']) ? $property['agent_name'] : "Unknown Agent";

                // Wishlist check
                $isInWishlist = false;
                if (isset($_SESSION['user_id'])) {
                    $checkWishlist = $conn->query("SELECT id FROM wishlist WHERE user_id = {$_SESSION['user_id']} AND property_id = {$property['id']}");
                    $isInWishlist = $checkWishlist->num_rows > 0;
                }

                echo "
                <div class='border rounded-lg shadow-lg bg-white hover:shadow-xl transition relative'>
                    <!-- Status and Listing Type -->
                    <span class='absolute top-4 left-4 text-white text-sm font-bold px-3 py-1 rounded {$statusClass} z-10'>{$status}</span>
                    <span class='absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded z-10'>{$listingType}</span>

                    <!-- Image Slider -->
                    <div class='relative w-full h-64 overflow-hidden'>
                        <div class='slider' id='slider-{$property['id']}'>";

                foreach ($images as $index => $image) {
                    $hiddenClass = ($index === 0) ? '' : 'hidden';
                    echo "<img src='public/uploads/{$image}' class='w-full h-64 object-cover slider-image {$hiddenClass}'>";
                }

                echo "      </div>
                        <!-- Slider Controls -->
                        <button class='absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev' data-slider='slider-{$property['id']}'>‹</button>
                        <button class='absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next' data-slider='slider-{$property['id']}'>›</button>
                    </div>

                    <!-- Property Details -->
                    <div class='p-4'>
                        <p class='text-[#CC9933] font-semibold text-lg'>₦" . number_format($property['price'], 2) . "</p>
                        <h3 class='text-[#092468] text-xl font-bold'>{$property['title']}</h3>
                        <p class='text-gray-600'>{$property['location']}</p>

                        <!-- Property Features -->
                        <div class='mt-2 flex flex-wrap text-gray-500 text-sm'>
                            <span class='mr-2'>🛏️ {$property['bedrooms']} Beds</span>
                            <span class='mr-2'>🛁 {$property['bathrooms']} Baths</span>
                            <span class='mr-2'>📏 {$property['size']} sqft</span>
                            <span class='mr-2'>🚗 {$property['garage']} Garage</span>
                        </div>

                        <!-- Agent & Wishlist Section -->
                        <div class='flex justify-between items-center mt-4'>
                            <div class='flex items-center'>
                                <img src='{$agentImage}' class='w-10 h-10 rounded-full mr-3' alt='Agent'>
                                <span class='text-sm text-gray-700'>{$agentName}</span>
                            </div>
                           <button class='wishlist-btn " . ($isInWishlist ? "text-red-500" : "text-gray-500") . " hover:text-red-500 transition' 
        data-property-id='{$property['id']}'
        data-in-wishlist='" . ($isInWishlist ? "1" : "0") . "'>
    " . ($isInWishlist ? "❤️" : "🤍") . "
</button>

                        </div>

                        <!-- View Details Button -->
                        <a href='property.php?id={$property['id']}' class='mt-4 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]'>View Details</a>
                    </div>
                </div>
            ";
            }
            ?>
        </div>

        <!-- Pagination -->
        <div class="mt-10 flex justify-center">
            <a href="#" class="px-4 py-2 bg-gray-200 rounded-l-lg hover:bg-gray-300 text-sm md:text-base">← Previous</a>
            <a href="#" class="px-4 py-2 bg-[#CC9933] text-white hover:bg-[#d88b1c] text-sm md:text-base">Next →</a>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterToggle = document.getElementById('filter-toggle');
        const filters = document.getElementById('filters');

        if (!filterToggle || !filters) {
            console.error('Filter toggle button or filters not found!');
            return;
        }

        console.log('Script loaded successfully. Elements found:', {
            filterToggle,
            filters
        });
        filterToggle.addEventListener('click', function() {
            console.log('Filter button clicked!');
            filters.classList.toggle('hidden');
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Image slider functionality
        document.querySelectorAll('.slider').forEach(slider => {
            let images = slider.querySelectorAll('.slider-image');
            let index = 0;

            function showImage(i) {
                images.forEach(img => img.classList.add('hidden'));
                images[i].classList.remove('hidden');
            }
            showImage(index);

            slider.closest('.relative').querySelector('.prev').addEventListener('click', function() {
                index = (index > 0) ? index - 1 : images.length - 1;
                showImage(index);
            });

            slider.closest('.relative').querySelector('.next').addEventListener('click', function() {
                index = (index < images.length - 1) ? index + 1 : 0;
                showImage(index);
            });
        });

        // Wishlist functionality
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.wishlist-btn').forEach(button => {
                button.addEventListener('click', async function() {
                    const isLoggedIn =
                        <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?> ===
                        true;
                    if (!isLoggedIn) {
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
                            console.error("Network response was not ok:", response
                                .statusText);
                            return;
                        }

                        const data = await response.json();
                        if (data.success) {
                            this.setAttribute('data-in-wishlist', data.inWishlist ?
                                '1' : '0');
                            this.innerHTML = data.inWishlist ? '❤️' : '🤍';
                            this.classList.toggle('text-red-500', data.inWishlist);
                            this.classList.toggle('text-gray-500', !data.inWishlist);
                        } else {
                            console.error("Wishlist action failed:", data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                });
            });
        });
    })
</script>