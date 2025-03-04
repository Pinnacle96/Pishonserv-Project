<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db_connect.php';
include 'includes/navbar.php';
?>

<!-- Hero Section with Slider -->
<section class="relative w-full h-[400px] sm:h-[600px] pt-80 sm:pt-24">
    <div id="hero-slider" class="absolute inset-0 w-full h-full">
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-100"
            style="background-image: url('public/images/hero1.jpg');"></div>
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-0"
            style="background-image: url('public/images/hero2.jpg');"></div>
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-0"
            style="background-image: url('public/images/hero3.jpg');"></div>
    </div>
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <div class="relative z-10 flex flex-col items-center justify-center h-full text-center text-white px-6">
        <h1 class="text-3xl sm:text-5xl font-bold">Find Your Dream Property</h1>
        <p class="text-sm sm:text-lg mt-2">Browse the best real estate deals for Buy, Rent, Shortlet, and more.</p>

        <!-- Search Bar -->
        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg mt-4 sm:mt-6 w-full max-w-5xl mx-4">
            <!-- Search Tabs -->
            <div class="flex flex-wrap justify-center sm:justify-between border-b pb-2">
                <button
                    class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2 focus:border-b-4 border-[#CC9933]"
                    data-category="buy">Buy</button>
                <button class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2"
                    data-category="rent">Rent</button>
                <button class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2"
                    data-category="shortlet">Shortlet</button>
                <button class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2"
                    data-category="hotel">Hotel</button>
                <button class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2"
                    data-category="land">Land</button>
                <button class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2"
                    data-category="project">Project</button>
            </div>

            <!-- Search Form -->
            <form action="properties.php" method="GET"
                class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 mt-2 sm:mt-4">
                <input type="hidden" name="category" id="search-category" value="buy">

                <select name="type" class="p-2 sm:p-3 border rounded text-sm sm:text-base">
                    <option value="">Property Type</option>
                    <option value="house">House</option>
                    <option value="apartment">Apartment</option>
                    <option value="land">Land</option>
                </select>

                <select name="location" class="p-2 sm:p-3 border rounded text-sm sm:text-base">
                    <option value="">Select Location</option>
                    <option value="lagos">Lagos</option>
                    <option value="abuja">Abuja</option>
                    <option value="port-harcourt">Port Harcourt</option>
                </select>

                <select name="bedroom" class="p-2 sm:p-3 border rounded text-sm sm:text-base">
                    <option value="">Bedrooms</option>
                    <option value="1">1 Bedroom</option>
                    <option value="2">2 Bedrooms</option>
                    <option value="3">3+ Bedrooms</option>
                </select>

                <input type="number" name="min_price" class="p-2 sm:p-3 border rounded text-sm sm:text-base"
                    placeholder="Min Price">
                <input type="number" name="max_price" class="p-2 sm:p-3 border rounded text-sm sm:text-base"
                    placeholder="Max Price">

                <button type="submit"
                    class="bg-[#CC9933] text-white px-4 py-2 sm:py-3 rounded hover:bg-[#d88b1c] text-sm sm:text-base">Search</button>
            </form>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section class="container mx-auto py-12 px-4">
    <h2 class="text-4xl font-bold text-[#092468] text-center">Featured Properties</h2>
    <p class="text-gray-600 text-center">Check out some of the best listings</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8 px-6 md:px-10 lg:px-16">
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
</section>

<!-- Image Slider Script -->
<script>
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

<!-- How It Works Section -->
<section class="container mx-auto py-12 px-4">
    <h2 class="text-4xl font-bold text-[#092468] text-center">How It Works</h2>
    <p class="text-gray-600 text-center mb-8">A simple way to buy, rent, or list properties</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 px-6 md:px-10 lg:px-16">
        <!-- Step 1: Search & Explore -->
        <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-2xl transition">
            <div class="flex justify-center">
                <img src="public/icons/search.png" class="w-16 h-16" alt="Search Icon">
            </div>
            <h3 class="text-xl font-bold text-[#092468] mt-4">Search & Explore</h3>
            <p class="text-gray-600 mt-2">Use our advanced filters to find your ideal property.</p>
        </div>

        <!-- Step 2: Connect with Agents -->
        <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-2xl transition">
            <div class="flex justify-center">
                <img src="public/icons/agent.png" class="w-16 h-16" alt="Agent Icon">
            </div>
            <h3 class="text-xl font-bold text-[#092468] mt-4">Connect with Agents</h3>
            <p class="text-gray-600 mt-2">Speak directly with professional real estate agents.</p>
        </div>

        <!-- Step 3: Secure Payment -->
        <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-2xl transition">
            <div class="flex justify-center">
                <img src="public/icons/payment.png" class="w-16 h-16" alt="Payment Icon">
            </div>
            <h3 class="text-xl font-bold text-[#092468] mt-4">Make Secure Payments</h3>
            <p class="text-gray-600 mt-2">Complete your transaction safely with our trusted system.</p>
        </div>
    </div>
</section>


<!-- Testimonials Section -->
<section class="container mx-auto py-12 px-4">
    <h2 class="text-4xl font-bold text-[#092468] text-center">What Our Clients Say</h2>
    <p class="text-gray-600 text-center">Hear from happy homeowners</p>

    <div class="mt-8 relative max-w-3xl mx-auto">
        <div id="testimonial-slider" class="overflow-hidden relative">
            <div class="flex transition-transform duration-700 ease-in-out" id="testimonial-track">
                <div class="testimonial-slide w-full p-4 flex-shrink-0">
                    <div class="bg-white p-6 shadow-lg rounded-lg">
                        <p class="text-gray-600">"Great experience! Found my perfect home easily."</p>
                        <h4 class="text-[#092468] font-bold mt-2">- John Doe</h4>
                    </div>
                </div>
                <div class="testimonial-slide w-full p-4 flex-shrink-0">
                    <div class="bg-white p-6 shadow-lg rounded-lg">
                        <p class="text-gray-600">"Excellent service and smooth transaction process!"</p>
                        <h4 class="text-[#092468] font-bold mt-2">- Sarah Williams</h4>
                    </div>
                </div>
                <div class="testimonial-slide w-full p-4 flex-shrink-0">
                    <div class="bg-white p-6 shadow-lg rounded-lg">
                        <p class="text-gray-600">"Highly recommended! Professional team and great support."</p>
                        <h4 class="text-[#092468] font-bold mt-2">- Mark Johnson</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <button id="prev"
            class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-[#092468] text-white px-3 py-2 rounded-full hover:bg-[#051B47]">&larr;</button>
        <button id="next"
            class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-[#092468] text-white px-3 py-2 rounded-full hover:bg-[#051B47]">&rarr;</button>
    </div>
</section>


<!-- Call-to-Action -->
<section class="relative text-white text-center py-16 bg-cover bg-center"
    style="background-image: url('public/images/hero3.jpg');">
    <!-- Dark Overlay for Readability -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <!-- Content -->
    <div class="relative z-10">
        <h2 class="text-4xl font-bold">List Your Property Today</h2>
        <p class="text-lg mt-2">Reach thousands of potential buyers & renters.</p>
        <a href="create-listing.php"
            class="mt-4 inline-block bg-[#CC9933] text-white px-6 py-3 rounded hover:bg-[#d88b1c]">Create Listing</a>
    </div>
</section>


<?php include 'includes/footer.php'; ?>
}