<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db_connect.php';
include 'includes/navbar.php';
include 'includes/config.php'; // LOCATIONIQ_API_KEY

function getLocationCoordinates($location, $apiKey)
{
    $location = urlencode($location);
    $url = "https://us1.locationiq.com/v1/search.php?key=$apiKey&q=$location&format=json";
    $context = stream_context_create(array('http' => array('header' => "User-Agent: PHP")));
    $response = file_get_contents($url, false, $context);
    if ($response === FALSE) return null;
    $data = json_decode($response, true);
    return isset($data[0]) ? ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']] : null;
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
            $mapUrl = getLocationCoordinates($property['location'], LOCATIONIQ_API_KEY);
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

<!-- Testimonials Section -->
<section class="container mx-auto py-12 sm:py-16 px-4 sm:px-6">
    <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-[#092468] text-center">What Our Clients Say</h2>
    <p class="text-gray-600 dark:text-gray-300 text-base sm:text-lg text-center mt-2 mb-8 sm:mb-12 max-w-2xl mx-auto">
        Hear from happy homeowners and renters who found their perfect property.
    </p>

    <div class="mt-8 relative max-w-4xl mx-auto">
        <div id="testimonial-slider" class="relative outline-none" role="region" aria-label="Testimonials Carousel"
            tabindex="0">
            <div id="testimonial-track" class="w-full">
                <!-- Testimonial 1 -->
                <div class="testimonial-slide hidden max-w-md mx-auto" data-index="0">
                    <div
                        class="bg-white dark:bg-gray-800 p-4 sm:p-6 shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <img src="/public/uploads/avatar1.jpg" alt="John Doe"
                                class="w-12 h-12 rounded-full mr-3 object-cover"
                                onerror="this.src='/public/uploads/default.png'">
                            <div>
                                <h4 class="text-[#092468] dark:text-[#CC9933] font-bold">John Doe</h4>
                                <div class="flex text-[#CC9933] text-sm" role="img" aria-label="5 star rating">
                                    <span class="sr-only">5 stars</span>
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"Great experience! Found my perfect home easily with
                            the help of their amazing team."</p>
                    </div>
                </div>
                <!-- Testimonial 2 -->
                <div class="testimonial-slide hidden max-w-md mx-auto" data-index="1">
                    <div
                        class="bg-white dark:bg-gray-800 p-4 sm:p-6 shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <img src="/public/uploads/avatar2.jpg" alt="Sarah Williams"
                                class="w-12 h-12 rounded-full mr-3 object-cover"
                                onerror="this.src='/public/uploads/default.png'">
                            <div>
                                <h4 class="text-[#092468] dark:text-[#CC9933] font-bold">Sarah Williams</h4>
                                <div class="flex text-[#CC9933] text-sm" role="img" aria-label="4 star rating">
                                    <span class="sr-only">4 stars</span>
                                    ★★★★☆
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"Excellent service and a smooth transaction process.
                            Highly recommend their platform!"</p>
                    </div>
                </div>
                <!-- Testimonial 3 -->
                <div class="testimonial-slide hidden max-w-md mx-auto" data-index="2">
                    <div
                        class="bg-white dark:bg-gray-800 p-4 sm:p-6 shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <img src="/public/uploads/avatar3.jpg" alt="Mark Johnson"
                                class="w-12 h-12 rounded-full mr-3 object-cover"
                                onerror="this.src='/public/uploads/default.png'">
                            <div>
                                <h4 class="text-[#092468] dark:text-[#CC9933] font-bold">Mark Johnson</h4>
                                <div class="flex text-[#CC9933] text-sm" role="img" aria-label="5 star rating">
                                    <span class="sr-only">5 stars</span>
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"Professional team and great support throughout.
                            Definitely my go-to for real estate!"</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <button id="prev" aria-label="Previous Testimonial"
                class="absolute left-2 sm:-left-10 top-1/2 transform -translate-y-1/2 bg-[#092468] text-white px-3 py-2 rounded-full hover:bg-[#051B47] focus:outline-none focus:ring-2 focus:ring-[#CC9933] transition">
                ←
            </button>
            <button id="next" aria-label="Next Testimonial"
                class="absolute right-2 sm:-right-10 top-1/2 transform -translate-y-1/2 bg-[#092468] text-white px-3 py-2 rounded-full hover:bg-[#051B47] focus:outline-none focus:ring-2 focus:ring-[#CC9933] transition">
                →
            </button>
        </div>

        <!-- Navigation Dots -->
        <div class="flex justify-center mt-6 space-x-2" id="testimonial-dots">
            <button
                class="w-3 h-3 bg-[#092468] rounded-full focus:outline-none focus:ring-2 focus:ring-[#CC9933] active"
                data-slide="0" aria-label="Slide 1"></button>
            <button
                class="w-3 h-3 bg-gray-300 rounded-full hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC9933]"
                data-slide="1" aria-label="Slide 2"></button>
            <button
                class="w-3 h-3 bg-gray-300 rounded-full hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC9933]"
                data-slide="2" aria-label="Slide 3"></button>
        </div>
    </div>
</section>

<style>
.testimonial-slide.active {
    display: block;
    opacity: 1;
    transition: opacity 0.5s ease-in-out;
}

.testimonial-slide {
    display: none;
    opacity: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.testimonial-slide');
    const prevBtn = document.getElementById('prev');
    const nextBtn = document.getElementById('next');
    const dots = document.querySelectorAll('#testimonial-dots button');
    let currentIndex = 0;
    const totalSlides = slides.length;
    let autoPlay;

    if (totalSlides === 0) {
        document.getElementById('testimonial-slider').innerHTML =
            '<p class="text-center text-gray-600">No testimonials available.</p>';
        return;
    }

    function updateSlider() {
        slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === currentIndex);
        });
        dots.forEach((dot, index) => {
            dot.classList.toggle('bg-[#092468]', index === currentIndex);
            dot.classList.toggle('bg-gray-300', index !== currentIndex);
            dot.classList.toggle('active', index === currentIndex);
        });
    }

    function goToSlide(index) {
        currentIndex = (index + totalSlides) % totalSlides;
        updateSlider();
        resetAutoPlay();
    }

    function startAutoPlay() {
        autoPlay = setInterval(() => goToSlide(currentIndex + 1), 5000);
    }

    function resetAutoPlay() {
        clearInterval(autoPlay);
        startAutoPlay();
    }

    prevBtn.addEventListener('click', () => goToSlide(currentIndex - 1));
    nextBtn.addEventListener('click', () => goToSlide(currentIndex + 1));

    dots.forEach(dot => {
        dot.addEventListener('click', () => goToSlide(parseInt(dot.getAttribute('data-slide'))));
    });

    const slider = document.getElementById('testimonial-slider');
    slider.addEventListener('mouseenter', () => clearInterval(autoPlay));
    slider.addEventListener('mouseleave', startAutoPlay);

    slider.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            goToSlide(currentIndex - 1);
            e.preventDefault();
        }
        if (e.key === 'ArrowRight') {
            goToSlide(currentIndex + 1);
            e.preventDefault();
        }
    });

    dots.forEach(dot => {
        dot.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                goToSlide(parseInt(dot.getAttribute('data-slide')));
                e.preventDefault();
            }
        });
    });

    // Show the first slide initially
    slides[0].classList.add('active');
    startAutoPlay();
    updateSlider();
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

</html>