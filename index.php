<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db_connect.php';
include 'includes/navbar.php';
include 'includes/config.php'; // LOCATIONIQ_API_KEY

function getLocationCoordinates($property_id, $location, $apiKey, $conn)
{
    // Normalize location for consistency
    $location = trim(strtolower($location));
    if (empty($location) || empty($property_id)) {
        return null;
    }

    // Check properties table for existing coordinates
    $stmt = $conn->prepare("SELECT latitude, longitude FROM properties WHERE id = ? AND location = ?");
    $stmt->bind_param("is", $property_id, $location);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        if (!empty($row['latitude']) && !empty($row['longitude'])) {
            return ['lat' => $row['latitude'], 'lon' => $row['longitude']];
        }
    } else {
        $stmt->close();
    }

    // Query LocationIQ if coordinates are missing
    $location = urlencode($location);
    $url = "https://us1.locationiq.com/v1/search.php?key=$apiKey&q=$location&format=json";
    $context = stream_context_create(array('http' => array('header' => "User-Agent: PHP")));
    $response = @file_get_contents($url, false, $context);
    if ($response === FALSE) {
        // Log error for debugging
        $error = error_get_last();
        error_log(date('Y-m-d H:i:s') . " [LocationIQ] Failed for $location: " . ($error['message'] ?? 'Unknown error'));
        return null;
    }
    $data = json_decode($response, true);
    if (isset($data[0])) {
        $lat = $data[0]['lat'];
        $lon = $data[0]['lon'];
        // Update properties table with coordinates
        $stmt = $conn->prepare("UPDATE properties SET latitude = ?, longitude = ? WHERE id = ? AND location = ?");
        $stmt->bind_param("ddis", $lat, $lon, $property_id, $location);
        $stmt->execute();
        $stmt->close();
        return ['lat' => $lat, 'lon' => $lon];
    }
    return null;
}
?>

<!-- Hero Section with Slider -->
<section class="relative w-full min-h-[500px] sm:min-h-[650px] pt-20 md:pt-20 lg:pt-24 overflow-hidden">
    <div id="hero-slider" class="absolute inset-0 w-full h-full">
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-100"
            style="background-image: url('public/images/hero1.jpg');"></div>
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-0"
            style="background-image: url('public/images/hero2.jpg');"></div>
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-0"
            style="background-image: url('public/images/hero3.jpg');"></div>
    </div>
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <div class="relative z-10 flex flex-col items-center justify-center h-full text-center text-white px-6 pt-10">
        <h1 class="text-3xl sm:text-5xl font-bold">Find Your Dream Property</h1>
        <p class="text-sm sm:text-lg mt-2">Browse the best real estate deals for Buy, Rent, Shortlet, and more.</p>

        <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg mt-4 sm:mt-6 w-full max-w-5xl mx-4">
            <div class="flex flex-wrap justify-center sm:justify-between border-b pb-2 gap-2 sm:gap-0">
                <button
                    class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2 border-b-4 border-[#CC9933] bg-[#FFF3E0] focus:outline-none active"
                    data-category="buy">Buy</button>
                <button
                    class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2 border-b-4 border-transparent hover:border-[#CC9933] hover:bg-[#FFF3E0] focus:outline-none"
                    data-category="rent">Rent</button>
                <button
                    class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2 border-b-4 border-transparent hover:border-[#CC9933] hover:bg-[#FFF3E0] focus:outline-none"
                    data-category="shortlet">Shortlet</button>
                <button
                    class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2 border-b-4 border-transparent hover:border-[#CC9933] hover:bg-[#FFF3E0] focus:outline-none"
                    data-category="hotel">Hotel</button>
                <button
                    class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2 border-b-4 border-transparent hover:border-[#CC9933] hover:bg-[#FFF3E0] focus:outline-none"
                    data-category="land">Land</button>
                <button
                    class="tab-button text-[#092468] font-semibold px-2 sm:px-4 py-1 sm:py-2 border-b-4 border-transparent hover:border-[#CC9933] hover:bg-[#FFF3E0] focus:outline-none"
                    data-category="project">Project</button>
            </div>

            <form id="searchForm" action="properties.php" method="GET"
                class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 mt-2 sm:mt-4 w-full text-gray-900">

                <input type="hidden" name="category" id="search-category" value="buy">

                <select name="type"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]"
                    required>
                    <option value="">Property Type</option>
                    <?php
                    $property_types = ['apartment', 'office', 'event_center', 'hotel', 'short_stay', 'house', 'villa', 'condo', 'townhouse', 'duplex', 'penthouse', 'studio', 'bungalow', 'commercial', 'warehouse', 'retail', 'land', 'farmhouse', 'mixed_use'];
                    foreach ($property_types as $type_option) {
                        $selected = (isset($_GET['type']) && $_GET['type'] === $type_option) ? 'selected' : '';
                        echo "<option value='$type_option' $selected>" . ucwords(str_replace('_', ' ', $type_option)) . "</option>";
                    }
                    ?>
                </select>

                <select name="location"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]">
                    <option value="">Select Location</option>
                    <?php
                    $locations = ['lagos', 'abuja', 'port-harcourt'];
                    foreach ($locations as $loc) {
                        $selected = (isset($_GET['location']) && $_GET['location'] === $loc) ? 'selected' : '';
                        echo "<option value='$loc' $selected>" . ucwords(str_replace('-', ' ', $loc)) . "</option>";
                    }
                    ?>
                </select>

                <select name="bedroom"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]">
                    <option value="">Bedrooms</option>
                    <option value="1"
                        <?php echo (isset($_GET['bedroom']) && $_GET['bedroom'] === '1') ? 'selected' : ''; ?>>1 Bedroom
                    </option>
                    <option value="2"
                        <?php echo (isset($_GET['bedroom']) && $_GET['bedroom'] === '2') ? 'selected' : ''; ?>>2
                        Bedrooms</option>
                    <option value="3"
                        <?php echo (isset($_GET['bedroom']) && $_GET['bedroom'] === '3') ? 'selected' : ''; ?>>3+
                        Bedrooms</option>
                </select>

                <input type="number" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? '') ?>"
                    placeholder="Min Price" min="0"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]">

                <input type="number" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? '') ?>"
                    placeholder="Max Price" min="0"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]">

                <button type="submit"
                    class="bg-[#CC9933] text-white px-4 py-2 sm:py-3 rounded hover:bg-[#d88b1c] text-sm sm:text-base w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-[#CC9933]">Search</button>

            </form>

        </div>
    </div>
</section>

<script>
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (!input.value) {
                input.disabled = true; // Prevent empty fields from submitting
            }
        });
    });

    // Tab Button Logic
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active',
                'bg-[#FFF3E0]', 'border-[#CC9933]'));
            this.classList.add('active', 'bg-[#FFF3E0]', 'border-[#CC9933]');
            document.getElementById('search-category').value = this.getAttribute('data-category');
        });
    });

    // Hero slider functionality (unchanged)
    const slides = document.querySelectorAll('.slide');
    let currentSlide = 0;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.toggle('opacity-100', i === index);
            slide.classList.toggle('opacity-0', i !== index);
        });
    }

    setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }, 5000);
</script>

<section class="container mx-auto py-12 px-4 mt-2">
    <h2 class="text-4xl font-bold text-[#092468] text-center">Featured Properties</h2>
    <p class="text-gray-600 text-center">Check out some of the best listings</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8 px-6 md:px-10 lg:px-16">
        <?php
        $query = "SELECT p.*, u.name AS agent_name, u.profile_image AS agent_image 
              FROM properties p 
              JOIN users u ON p.owner_id = u.id
              WHERE p.admin_approved = 1 
              ORDER BY p.created_at DESC LIMIT 6";
        $result = $conn->query($query);

        while ($property = $result->fetch_assoc()) {
            $images = explode(',', $property['images']);
            $firstImage = !empty($images[0]) ? $images[0] : 'default.jpg';

            $status = ucfirst($property['status'] ?? 'Unknown');
            $listingType = ucfirst(str_replace('_', ' ', $property['listing_type'] ?? 'Unknown'));

            $statusClass = "bg-gray-500";
            if ($property['status'] == 'available') $statusClass = "bg-green-500";
            if ($property['status'] == 'booked') $statusClass = "bg-yellow-500";
            if ($property['status'] == 'sold') $statusClass = "bg-red-500";
            if ($property['status'] == 'rented') $statusClass = "bg-blue-500";

            $agentImage = !empty($property['agent_image']) ? "public/uploads/{$property['agent_image']}" : "public/uploads/default.png";
            $agentName = htmlspecialchars($property['agent_name'] ?? "Unknown Agent");

            // Booking Details Check
            $bookingInfo = "";
            if ($property['status'] == 'booked') {
                $bookingStmt = $conn->prepare("SELECT check_in_date, check_out_date FROM bookings WHERE property_id = ? ORDER BY id DESC");
                $bookingStmt->bind_param("i", $property['id']);
                $bookingStmt->execute();
                $bookingRes = $bookingStmt->get_result();
                if ($bookingRes->num_rows > 0) {
                    $bookingInfo .= "<div class='mt-2 text-xs text-red-500'>";
                    while ($booking = $bookingRes->fetch_assoc()) {
                        $start_date = !empty($booking['check_in_date']) ? date('M d, Y', strtotime($booking['check_in_date'])) : '';
                        $end_date = !empty($booking['check_out_date']) ? date('M d, Y', strtotime($booking['check_out_date'])) : '';
                        if ($start_date && $end_date) {
                            $bookingInfo .= "<p>Booked: {$start_date} to {$end_date}</p>";
                        }
                    }
                    $bookingInfo .= "</div>";
                }
                $bookingStmt->close();
            }

            // Wishlist Check
            $isInWishlist = false;
            if (isset($_SESSION['user_id'])) {
                $checkWishlist = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?");
                $checkWishlist->bind_param("ii", $_SESSION['user_id'], $property['id']);
                $checkWishlist->execute();
                $isInWishlist = $checkWishlist->get_result()->num_rows > 0;
                $checkWishlist->close();
            }

            // Map from LocationIQ
            $mapUrl = getLocationCoordinates($property['id'], $property['location'], LOCATIONIQ_API_KEY, $conn);
            $mapImage = $mapUrl ? "<img src='https://maps.locationiq.com/v3/staticmap?key=" . LOCATIONIQ_API_KEY . "&center={$mapUrl['lat']},{$mapUrl['lon']}&zoom=15&size=600x400' alt='Property Map' loading='lazy' class='mt-2'>" : "";

            echo "
        <div class='border rounded-lg shadow-lg bg-white hover:shadow-xl transition relative'>
            <span class='absolute top-4 left-4 text-white text-sm font-bold px-3 py-1 rounded {$statusClass} z-10'>{$status}</span>
            <span class='absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded z-10'>{$listingType}</span>

            <div class='relative w-full h-64 overflow-hidden'>
                <div class='slider' id='slider-{$property['id']}'>";
            foreach ($images as $index => $image) {
                $hiddenClass = ($index == 0) ? '' : 'hidden';
                echo "<img src='public/uploads/{$image}' class='w-full h-64 object-cover slider-image {$hiddenClass}' alt='Property Image'>";
            }
            echo "  </div>
                <button class='absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev hover:bg-gray-600 transition' data-slider='slider-{$property['id']}'>‹</button>
                <button class='absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next hover:bg-gray-600 transition' data-slider='slider-{$property['id']}'>›</button>
            </div>

            <div class='p-4'>
                <p class='text-[#CC9933] font-semibold text-lg'>₦" . number_format($property['price'], 2) . "</p>
                <h3 class='text-[#092468] text-xl font-bold'>{$property['title']}</h3>
                <p class='text-gray-600'>" . htmlspecialchars($property['location']) . "</p>
                {$bookingInfo}

                <div class='mt-2 flex flex-wrap text-gray-500 text-sm'>
                    <span class='mr-2'>🛏️ {$property['bedrooms']} Beds</span>
                    <span class='mr-2'>🛁 {$property['bathrooms']} Baths</span>
                    <span class='mr-2'>📏 {$property['size']} sqft</span>
                    <span class='mr-2'>🚗 {$property['garage']} Garage</span>
                </div>

                {$mapImage}

                <div class='flex justify-between items-center mt-4'>
                    <a href='agent_profile.php?id={$property['owner_id']}' class='flex items-center'>
                        <img src='{$agentImage}' class='w-10 h-10 rounded-full mr-3' alt='Agent'>
                        <span class='text-sm text-gray-700'>{$agentName}</span>
                    </a>
                    <button class='wishlist-btn " . ($isInWishlist ? "text-red-500" : "text-gray-500") . " hover:text-red-500 transition text-2xl' 
                            data-property-id='{$property['id']}' 
                            data-in-wishlist='" . ($isInWishlist ? "1" : "0") . "'>
                        " . ($isInWishlist ? "❤️" : "🤍") . "
                    </button>
                </div>

                <a href='property.php?id={$property['id']}' class='mt-4 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c] transition'>View Details</a>
            </div>
        </div>";
        }
        ?>
    </div>

    <!-- View More Button -->
    <div class="text-center mt-10">
        <a href="properties.php"
            class="inline-block bg-[#092468] text-white px-6 py-3 rounded hover:bg-[#051b47] transition">
            View More Properties →
        </a>
    </div>

</section>

<!-- How It Works Section -->
<section class="container mx-auto py-16 px-4">
    <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center">How It Works</h2>
    <p class="text-gray-600 dark:text-gray-300 text-lg text-center mt-2 mb-12 max-w-2xl mx-auto">A simple, seamless way
        to buy, rent, or list properties with confidence.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 px-6 md:px-10 lg:px-16">
        <!-- Step 1: Search & Explore -->
        <div
            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 text-center transform hover:-translate-y-2 hover:shadow-2xl transition-all duration-300">
            <div class="flex justify-center">
                <i class="fas fa-search text-[#CC9933] text-4xl mb-4"></i>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-[#092468] dark:text-[#CC9933] mt-2">Search & Explore</h3>
            <p class="text-gray-600 dark:text-gray-300 mt-3 text-sm md:text-base">Browse thousands of listings with
                advanced filters to find your dream property.</p>
        </div>

        <!-- Step 2: Connect with Agents -->
        <div
            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 text-center transform hover:-translate-y-2 hover:shadow-2xl transition-all duration-300">
            <div class="flex justify-center">
                <i class="fas fa-user-tie text-[#CC9933] text-4xl mb-4"></i>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-[#092468] dark:text-[#CC9933] mt-2">Connect with Agents</h3>
            <p class="text-gray-600 dark:text-gray-300 mt-3 text-sm md:text-base">Get expert advice and support from
                verified real estate professionals.</p>
        </div>

        <!-- Step 3: Secure Payment -->
        <div
            class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 text-center transform hover:-translate-y-2 hover:shadow-2xl transition-all duration-300">
            <div class="flex justify-center">
                <i class="fas fa-shield-alt text-[#CC9933] text-4xl mb-4"></i>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-[#092468] dark:text-[#CC9933] mt-2">Make Secure Payments</h3>
            <p class="text-gray-600 dark:text-gray-300 mt-3 text-sm md:text-base">Finalize your deal with our secure,
                trusted payment system.</p>
        </div>
    </div>
</section>

<!-- Modern Testimonials Section -->
<!-- Modern Testimonials Section -->
<section
    class="relative py-16 sm:py-24 px-4 sm:px-6 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#092468] to-[#CC9933]"></div>
    <div class="absolute top-20 -right-20 w-64 h-64 rounded-full bg-[#092468]/5 dark:bg-[#CC9933]/10"
        style="filter: blur(80px);"></div>
    <div class="absolute bottom-10 -left-20 w-64 h-64 rounded-full bg-[#CC9933]/5 dark:bg-[#092468]/10"
        style="filter: blur(80px);"></div>

    <div class="relative max-w-7xl mx-auto">
        <div class="text-center mb-14">
            <span
                class="inline-block px-3 py-1 text-sm font-medium rounded-full bg-[#092468]/10 text-[#092468] dark:bg-[#CC9933]/20 dark:text-[#CC9933] mb-4">
                Client Voices
            </span>
            <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                Trusted by <span class="text-[#092468] dark:text-[#CC9933]">Homeowners</span> Nationwide
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                Don't just take our word for it. Here's what our community has to say about their experiences.
            </p>
        </div>

        <!-- Testimonial Carousel -->
        <div class="relative group">
            <div class="swiper testimonial-carousel max-w-4xl mx-auto">
                <div class="swiper-wrapper pb-12">
                    <!-- Testimonial 1 -->
                    <div class="swiper-slide">
                        <div
                            class="relative bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700">
                            <div class="absolute -top-5 left-6 text-6xl text-gray-100 dark:text-gray-700">"</div>
                            <div class="flex flex-col sm:flex-row items-start gap-6">
                                <img src="<?php echo $base_path; ?>public/uploads/67dc6fe3e95bd.jpg" alt="John Doe"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-[#092468] dark:border-[#CC9933] lazy-load"
                                    loading="lazy"
                                    onerror="this.src='<?php echo $base_path; ?>public/uploads/placeholder-user.jpg'">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">John Doe</h4>
                                        <span
                                            class="text-xs px-2 py-1 bg-[#092468]/10 text-[#092468] dark:bg-[#CC9933]/20 dark:text-[#CC9933] rounded-full">Home
                                            Buyer</span>
                                    </div>
                                    <div class="flex text-[#CC9933] mb-3" aria-label="5 out of 5 stars">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-300 relative">
                                        "The team went above and beyond to help me find my dream home. Their attention
                                        to detail and market knowledge saved me both time and money in the process."
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="swiper-slide">
                        <div
                            class="relative bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700">
                            <div class="absolute -top-5 left-6 text-6xl text-gray-100 dark:text-gray-700">"</div>
                            <div class="flex flex-col sm:flex-row items-start gap-6">
                                <img src="<?php echo $base_path; ?>public/uploads/67dc6fe3e95bd.jpg"
                                    alt="Sarah Williams"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-[#092468] dark:border-[#CC9933] lazy-load"
                                    loading="lazy"
                                    onerror="this.src='<?php echo $base_path; ?>public/uploads/placeholder-user.jpg'">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Sarah Williams</h4>
                                        <span
                                            class="text-xs px-2 py-1 bg-[#092468]/10 text-[#092468] dark:bg-[#CC9933]/20 dark:text-[#CC9933] rounded-full">Property
                                            Investor</span>
                                    </div>
                                    <div class="flex text-[#CC9933] mb-3" aria-label="4 out of 5 stars">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-300 relative">
                                        "As an investor, I appreciate their data-driven approach. They helped me
                                        identify properties with the best ROI potential in emerging neighborhoods."
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="swiper-slide">
                        <div
                            class="relative bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700">
                            <div class="absolute -top-5 left-6 text-6xl text-gray-100 dark:text-gray-700">"</div>
                            <div class="flex flex-col sm:flex-row items-start gap-6">
                                <img src="<?php echo $base_path; ?>public/uploads/67dc6fe3e95bd.jpg" alt="Mark Johnson"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-[#092468] dark:border-[#CC9933] lazy-load"
                                    loading="lazy"
                                    onerror="this.src='<?php echo $base_path; ?>public/uploads/placeholder-user.jpg'">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">Mark Johnson</h4>
                                        <span
                                            class="text-xs px-2 py-1 bg-[#092468]/10 text-[#092468] dark:bg-[#CC9933]/20 dark:text-[#CC9933] rounded-full">First-time
                                            Buyer</span>
                                    </div>
                                    <div class="flex text-[#CC9933] mb-3" aria-label="5 out of 5 stars">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927a1 1 0 011.902 0l1.286 3.97a1 1 0 00.95.69h4.147a1 1 0 01.588 1.81l-3.357 2.44a1 1 0 00-.364 1.118l1.286 3.97a1 1 0 01-1.54 1.118l-3.357-2.44a1 1 0 00-1.176 0l-3.357 2.44a1 1 0 01-1.54-1.118l1.286-3.97a1 1 0 00-.364-1.118L2.81 9.397a1 1 0 01.588-1.81h4.147a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-300 relative">
                                        "Being a first-time buyer was overwhelming, but my agent patiently guided me
                                        through every step. The mortgage pre-approval assistance was particularly
                                        helpful."
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Pagination -->
                <div class="swiper-pagination !relative !mt-10"></div>
            </div>

            <!-- Navigation Buttons -->
            <button
                class="testimonial-carousel-prev absolute left-0 sm:-left-12 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center text-[#092468] dark:text-[#CC9933] hover:bg-[#092468] hover:text-white dark:hover:bg-[#CC9933] dark:hover:text-gray-900 transition-all duration-300 opacity-0 group-hover:opacity-100 focus:opacity-100"
                aria-label="Previous testimonial">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </button>
            <button
                class="testimonial-carousel-next absolute right-0 sm:-right-12 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center text-[#092468] dark:text-[#CC9933] hover:bg-[#092468] hover:text-white dark:hover:bg-[#CC9933] dark:hover:text-gray-900 transition-all duration-300 opacity-0 group-hover:opacity-100 focus:opacity-100"
                aria-label="Next testimonial">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</section>

<!-- Swiper CSS and JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const carousel = document.querySelector('.testimonial-carousel');
            const pagination = document.querySelector('.swiper-pagination');
            const prevButton = document.querySelector('.testimonial-carousel-prev');
            const nextButton = document.querySelector('.testimonial-carousel-next');

            if (!carousel || !pagination || !prevButton || !nextButton) {
                console.error('Testimonial carousel elements missing:', {
                    carousel: !!carousel,
                    pagination: !!pagination,
                    prevButton: !!prevButton,
                    nextButton: !!nextButton
                });
                return;
            }

            const testimonialCarousel = new Swiper(carousel, {
                loop: true,
                spaceBetween: 30,
                centeredSlides: true,
                slidesPerView: 1,
                autoplay: {
                    delay: 6000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: pagination,
                    clickable: true,
                    renderBullet: function(index, className) {
                        return `<span class="${className} w-2.5 h-2.5 bg-gray-300 dark:bg-gray-600 hover:bg-[#092468] dark:hover:bg-[#CC9933] transition-all duration-300 mx-1 inline-block rounded-full"></span>`;
                    },
                },
                navigation: {
                    nextEl: nextButton,
                    prevEl: prevButton,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 1,
                    },
                    1024: {
                        slidesPerView: 1.2,
                    },
                },
                on: {
                    init: function() {
                        console.log('Testimonial carousel initialized successfully.');
                    },
                    slideChange: function() {
                        console.log('Switched to slide:', this.activeIndex);
                    }
                }
            });

            // Pause on hover
            carousel.addEventListener('mouseenter', () => {
                testimonialCarousel.autoplay.stop();
                console.log('Autoplay paused.');
            });
            carousel.addEventListener('mouseleave', () => {
                testimonialCarousel.autoplay.start();
                console.log('Autoplay resumed.');
            });

            // Ensure slides are visible
            const slides = carousel.querySelectorAll('.swiper-slide');
            slides.forEach(slide => {
                slide.style.display = 'block';
                slide.style.opacity = '1';
            });
        } catch (error) {
            console.error('Error initializing testimonial carousel:', error);
        }
    });
</script>
<!-- Call-to-Action -->
<section class="relative text-white text-center py-16 bg-cover bg-center"
    style="background-image: url('public/images/hero3.jpg');">
    <!-- Dark Overlay for Readability -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <!-- Content -->
    <div class="relative z-10">
        <h2 class="text-4xl font-bold">List Your Property Today</h2>
        <p class="text-lg mt-2">Reach thousands of potential buyers & renters.</p>
        <a href="<?php echo $base_path; ?>dashboard/agent_properties.php"
            class="mt-4 inline-block bg-[#CC9933] text-white px-6 py-3 rounded hover:bg-[#d88b1c]">Create Listing</a>
    </div>
</section>
<!-- Image Slider Script -->
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- JavaScript for Slider and Wishlist -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Slider Functionality
        document.querySelectorAll('.slider').forEach(slider => {
            const sliderId = slider.id;
            const images = slider.querySelectorAll('.slider-image');
            let currentIndex = 0;

            function showImage(index) {
                images.forEach((img, i) => {
                    img.classList.toggle('hidden', i !== index);
                });
            }

            slider.parentElement.querySelector('.prev').addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                showImage(currentIndex);
            });

            slider.parentElement.querySelector('.next').addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % images.length;
                showImage(currentIndex);
            });
        });

        // Wishlist functionality with SweetAlert2
        document.querySelectorAll('.wishlist-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const isLoggedIn =
                    <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
                if (!isLoggedIn) {
                    Swal.fire({
                        title: 'Login Required',
                        text: 'Please log in or register to add properties to your wishlist.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Login',
                        cancelButtonText: 'Register',
                        confirmButtonColor: '#092468', // Dark blue
                        cancelButtonColor: '#CC9933' // Gold
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'auth/login.php';
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            window.location.href = 'auth/register.php';
                        }
                    });
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
                        this.innerHTML = data.inWishlist ? '❤️' : '🤍';
                        this.classList.toggle('text-red-500', data.inWishlist);
                        this.classList.toggle('text-gray-500', !data.inWishlist);
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'Failed to update wishlist.',
                            icon: 'error',
                            confirmButtonColor: '#092468'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'An error occurred while updating the wishlist.',
                        icon: 'error',
                        confirmButtonColor: '#092468'
                    });
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
</body>