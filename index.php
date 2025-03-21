<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db_connect.php';
include 'includes/navbar.php';
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

            <form action="properties.php" method="GET"
                class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4 mt-2 sm:mt-4 w-full text-gray-900">
                <input type="hidden" name="category" id="search-category" value="buy">
                <select name="type"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]">
                    <option value="">Property Type</option>
                    <option value="apartment"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'apartment' ? 'selected' : ''; ?>>Apartment
                    </option>
                    <option value="office"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'office' ? 'selected' : ''; ?>>Office
                    </option>
                    <option value="event_center"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'event_center' ? 'selected' : ''; ?>>Event
                        Center</option>
                    <option value="hotel"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'hotel' ? 'selected' : ''; ?>>Hotel
                    </option>
                    <option value="short_stay"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'short_stay' ? 'selected' : ''; ?>>Short
                        Stay</option>
                    <option value="house"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'house' ? 'selected' : ''; ?>>House
                    </option>
                    <option value="villa"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'villa' ? 'selected' : ''; ?>>Villa
                    </option>
                    <option value="condo"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'condo' ? 'selected' : ''; ?>>Condo
                    </option>
                    <option value="townhouse"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'townhouse' ? 'selected' : ''; ?>>Townhouse
                    </option>
                    <option value="duplex"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'duplex' ? 'selected' : ''; ?>>Duplex
                    </option>
                    <option value="penthouse"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'penthouse' ? 'selected' : ''; ?>>Penthouse
                    </option>
                    <option value="studio"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'studio' ? 'selected' : ''; ?>>Studio
                    </option>
                    <option value="bungalow"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'bungalow' ? 'selected' : ''; ?>>Bungalow
                    </option>
                    <option value="commercial"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'commercial' ? 'selected' : ''; ?>>
                        Commercial</option>
                    <option value="warehouse"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'warehouse' ? 'selected' : ''; ?>>Warehouse
                    </option>
                    <option value="retail"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'retail' ? 'selected' : ''; ?>>Retail
                    </option>
                    <option value="land"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'land' ? 'selected' : ''; ?>>Land</option>
                    <option value="farmhouse"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'farmhouse' ? 'selected' : ''; ?>>Farmhouse
                    </option>
                    <option value="mixed_use"
                        <?php echo isset($_GET['type']) && $_GET['type'] === 'mixed_use' ? 'selected' : ''; ?>>Mixed Use
                    </option>
                </select>
                <select name="location"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]">
                    <option value="">Select Location</option>
                    <option value="lagos"
                        <?php echo isset($_GET['location']) && $_GET['location'] === 'lagos' ? 'selected' : ''; ?>>Lagos
                    </option>
                    <option value="abuja"
                        <?php echo isset($_GET['location']) && $_GET['location'] === 'abuja' ? 'selected' : ''; ?>>Abuja
                    </option>
                    <option value="port-harcourt"
                        <?php echo isset($_GET['location']) && $_GET['location'] === 'port-harcourt' ? 'selected' : ''; ?>>
                        Port Harcourt</option>
                </select>
                <select name="bedroom"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]">
                    <option value="">Bedrooms</option>
                    <option value="1"
                        <?php echo isset($_GET['bedroom']) && $_GET['bedroom'] === '1' ? 'selected' : ''; ?>>1 Bedroom
                    </option>
                    <option value="2"
                        <?php echo isset($_GET['bedroom']) && $_GET['bedroom'] === '2' ? 'selected' : ''; ?>>2 Bedrooms
                    </option>
                    <option value="3"
                        <?php echo isset($_GET['bedroom']) && $_GET['bedroom'] === '3' ? 'selected' : ''; ?>>3+ Bedrooms
                    </option>
                </select>
                <input type="number" name="min_price"
                    value="<?php echo isset($_GET['min_price']) && $_GET['min_price'] !== '' ? htmlspecialchars($_GET['min_price']) : ''; ?>"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]"
                    placeholder="Min Price" min="0">
                <input type="number" name="max_price"
                    value="<?php echo isset($_GET['max_price']) && $_GET['max_price'] !== '' ? htmlspecialchars($_GET['max_price']) : ''; ?>"
                    class="p-2 sm:p-3 border rounded text-sm sm:text-base w-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#CC9933]"
                    placeholder="Max Price" min="0">
                <button type="submit"
                    class="bg-[#CC9933] text-white px-4 py-2 sm:py-3 rounded hover:bg-[#d88b1c] text-sm sm:text-base w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-[#CC9933]">Search</button>
            </form>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const categoryInput = document.getElementById('search-category');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-[#FFF3E0]');
                    btn.classList.add('border-transparent');
                    btn.classList.remove('border-[#CC9933]');
                });

                this.classList.add('active', 'bg-[#FFF3E0]', 'border-[#CC9933]');
                this.classList.remove('border-transparent');

                const category = this.getAttribute('data-category');
                categoryInput.value = category;
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
    });
</script>
<!-- Featured Properties Section -->
<section class="container mx-auto py-12 px-4 mt-2">
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
            $listingType = isset($property['listing_type']) && !empty($property['listing_type']) ? ucfirst(str_replace('_', ' ', $property['listing_type'])) : 'Unknown';

            // Dynamic status colors
            $statusClass = "bg-gray-500";
            if ($property['status'] == 'available') $statusClass = "bg-green-500";
            if ($property['status'] == 'booked') $statusClass = "bg-yellow-500";
            if ($property['status'] == 'sold') $statusClass = "bg-red-500";
            if ($property['status'] == 'rented') $statusClass = "bg-blue-500";

            // Ensure agent details exist
            $agentImage = !empty($property['agent_image']) ? "public/uploads/{$property['agent_image']}" : "public/uploads/default.png";
            $agentName = !empty($property['agent_name']) ? htmlspecialchars($property['agent_name']) : "Unknown Agent";

            // Wishlist check
            $isInWishlist = false;
            if (isset($_SESSION['user_id'])) {
                $checkWishlist = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?");
                $checkWishlist->bind_param("ii", $_SESSION['user_id'], $property['id']);
                $checkWishlist->execute();
                $isInWishlist = $checkWishlist->get_result()->num_rows > 0;
                $checkWishlist->close();
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
                echo "<img src='public/uploads/{$image}' class='w-full h-64 object-cover slider-image {$hiddenClass}' alt='Property Image'>";
            }
            echo "      </div>
                        <!-- Slider Controls -->
                        <button class='absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev hover:bg-gray-600 transition' data-slider='slider-{$property['id']}'>‹</button>
                        <button class='absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next hover:bg-gray-600 transition' data-slider='slider-{$property['id']}'>›</button>
                    </div>

                    <!-- Property Details -->
                    <div class='p-4'>
                        <p class='text-[#CC9933] font-semibold text-lg'>₦" . number_format($property['price'], 2) . "</p>
                        <h3 class='text-[#092468] text-xl font-bold'>{$property['title']}</h3>
                        <p class='text-gray-600'>" . htmlspecialchars($property['location']) . "</p>

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
                            <button class='wishlist-btn " . ($isInWishlist ? "text-red-500" : "text-gray-500") . " hover:text-red-500 transition text-2xl' 
                                    data-property-id='{$property['id']}' 
                                    data-in-wishlist='" . ($isInWishlist ? "1" : "0") . "'>
                                " . ($isInWishlist ? "❤️" : "🤍") . "
                            </button>
                        </div>

                        <!-- View Details Button -->
                        <a href='property.php?id={$property['id']}' class='mt-4 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c] transition'>View Details</a>
                    </div>
                </div>
            ";
        }
        $conn->close();
        ?>
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
<section class="container mx-auto py-16 px-4">
    <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center">What Our Clients Say</h2>
    <p class="text-gray-600 dark:text-gray-300 text-lg text-center mt-2 mb-12 max-w-2xl mx-auto">Hear from happy
        homeowners and renters who found their perfect property.</p>

    <div class="mt-8 relative max-w-4xl mx-auto">
        <div id="testimonial-slider" class="overflow-hidden relative" role="region" aria-label="Testimonials Carousel">
            <div class="flex transition-transform duration-700 ease-in-out" id="testimonial-track">
                <!-- Testimonial 1 -->
                <div class="testimonial-slide w-full p-4 flex-shrink-0">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <img src="public/uploads/avatar1.jpg" alt="John Doe" class="w-12 h-12 rounded-full mr-3"
                                onerror="this.src='public/uploads/default.png'">
                            <div>
                                <h4 class="text-[#092468] dark:text-[#CC9933] font-bold">John Doe</h4>
                                <div class="flex text-[#CC9933] text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"Great experience! Found my perfect home easily with
                            the help of their amazing team."</p>
                    </div>
                </div>
                <!-- Testimonial 2 -->
                <div class="testimonial-slide w-full p-4 flex-shrink-0">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <img src="public/uploads/avatar2.jpg" alt="Sarah Williams"
                                class="w-12 h-12 rounded-full mr-3" onerror="this.src='public/uploads/default.png'">
                            <div>
                                <h4 class="text-[#092468] dark:text-[#CC9933] font-bold">Sarah Williams</h4>
                                <div class="flex text-[#CC9933] text-sm">★★★★☆</div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"Excellent service and a smooth transaction process.
                            Highly recommend their platform!"</p>
                    </div>
                </div>
                <!-- Testimonial 3 -->
                <div class="testimonial-slide w-full p-4 flex-shrink-0">
                    <div
                        class="bg-white dark:bg-gray-800 p-6 shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <img src="public/uploads/avatar3.jpg" alt="Mark Johnson" class="w-12 h-12 rounded-full mr-3"
                                onerror="this.src='public/uploads/default.png'">
                            <div>
                                <h4 class="text-[#092468] dark:text-[#CC9933] font-bold">Mark Johnson</h4>
                                <div class="flex text-[#CC9933] text-sm">★★★★★</div>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">"Professional team and great support throughout.
                            Definitely my go-to for real estate!"</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <button id="prev"
                class="absolute left-0 md:-left-12 top-1/2 transform -translate-y-1/2 bg-[#092468] text-white px-3 py-2 rounded-full hover:bg-[#051B47] focus:outline-none focus:ring-2 focus:ring-[#CC9933] transition"
                aria-label="Previous Testimonial">←</button>
            <button id="next"
                class="absolute right-0 md:-right-12 top-1/2 transform -translate-y-1/2 bg-[#092468] text-white px-3 py-2 rounded-full hover:bg-[#051B47] focus:outline-none focus:ring-2 focus:ring-[#CC9933] transition"
                aria-label="Next Testimonial">→</button>
        </div>

        <!-- Navigation Dots -->
        <div class="flex justify-center mt-6 space-x-2" id="testimonial-dots">
            <span class="w-3 h-3 bg-[#092468] rounded-full cursor-pointer active" data-slide="0"></span>
            <span class="w-3 h-3 bg-gray-300 rounded-full cursor-pointer" data-slide="1"></span>
            <span class="w-3 h-3 bg-gray-300 rounded-full cursor-pointer" data-slide="2"></span>
        </div>
    </div>
</section>

<!-- JavaScript for Testimonial Slider -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const track = document.getElementById('testimonial-track');
        const slides = document.querySelectorAll('.testimonial-slide');
        const prevBtn = document.getElementById('prev');
        const nextBtn = document.getElementById('next');
        const dots = document.querySelectorAll('#testimonial-dots span');
        let currentIndex = 0;
        const totalSlides = slides.length;

        function updateSlider() {
            const offset = -currentIndex * 100;
            track.style.transform = `translateX(${offset}%)`;
            dots.forEach(dot => dot.classList.remove('bg-[#092468]'));
            dots.forEach(dot => dot.classList.add('bg-gray-300'));
            dots[currentIndex].classList.remove('bg-gray-300');
            dots[currentIndex].classList.add('bg-[#092468]');
        }

        function goToSlide(index) {
            currentIndex = (index + totalSlides) % totalSlides;
            updateSlider();
        }

        prevBtn.addEventListener('click', () => goToSlide(currentIndex - 1));
        nextBtn.addEventListener('click', () => goToSlide(currentIndex + 1));

        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                goToSlide(parseInt(dot.getAttribute('data-slide')));
            });
        });

        // Auto-play
        let autoPlay = setInterval(() => goToSlide(currentIndex + 1), 5000);

        // Pause on hover
        document.getElementById('testimonial-slider').addEventListener('mouseenter', () => clearInterval(autoPlay));
        document.getElementById('testimonial-slider').addEventListener('mouseleave', () => {
            autoPlay = setInterval(() => goToSlide(currentIndex + 1), 5000);
        });

        // Keyboard navigation
        document.getElementById('testimonial-slider').addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') goToSlide(currentIndex - 1);
            if (e.key === 'ArrowRight') goToSlide(currentIndex + 1);
        });
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