<?php include 'includes/db_connect.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section with Slider -->
<section class="relative w-full h-screen pt-20 sm:pt-24">
    <!-- Slider Container -->
    <div id="hero-slider" class="absolute inset-0 w-full h-full">
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-100"
            style="background-image: url('public/images/hero1.jpg');"></div>
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-0"
            style="background-image: url('public/images/hero2.jpg');"></div>
        <div class="slide bg-cover bg-center w-full h-full absolute transition-opacity duration-1000 opacity-0"
            style="background-image: url('public/images/hero3.jpg');"></div>
    </div>

    <!-- Dark Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <!-- Hero Content -->
    <div class="relative z-10 flex flex-col items-center justify-center h-full text-center text-white px-6">
        <h1 class="text-4xl sm:text-5xl font-bold">Find Your Dream Property</h1>
        <p class="text-lg mt-2">Browse the best real estate deals for Buy, Rent, Shortlet, and more.</p>

        <!-- Search Bar -->
        <div class="bg-white p-6 rounded-lg shadow-lg mt-6 w-full max-w-5xl">
            <!-- Search Tabs -->
            <div class="flex flex-wrap justify-center sm:justify-between border-b pb-2">
                <button
                    class="tab-button text-[#092468] font-semibold px-3 sm:px-4 py-2 focus:border-b-4 border-[#CC9933]"
                    data-category="buy">Buy</button>
                <button class="tab-button text-[#092468] font-semibold px-3 sm:px-4 py-2"
                    data-category="rent">Rent</button>
                <button class="tab-button text-[#092468] font-semibold px-3 sm:px-4 py-2"
                    data-category="shortlet">Shortlet</button>
                <button class="tab-button text-[#092468] font-semibold px-3 sm:px-4 py-2"
                    data-category="hotel">Hotel</button>
                <button class="tab-button text-[#092468] font-semibold px-3 sm:px-4 py-2"
                    data-category="land">Land</button>
                <button class="tab-button text-[#092468] font-semibold px-3 sm:px-4 py-2"
                    data-category="project">Project</button>
            </div>

            <!-- Search Form -->
            <form action="properties.php" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                <input type="hidden" name="category" id="search-category" value="buy">

                <select name="type" class="p-3 border rounded">
                    <option value="">Property Type</option>
                    <option value="house">House</option>
                    <option value="apartment">Apartment</option>
                    <option value="land">Land</option>
                </select>

                <select name="location" class="p-3 border rounded">
                    <option value="">Select Location</option>
                    <option value="lagos">Lagos</option>
                    <option value="abuja">Abuja</option>
                    <option value="port-harcourt">Port Harcourt</option>
                </select>

                <select name="bedroom" class="p-3 border rounded">
                    <option value="">Bedrooms</option>
                    <option value="1">1 Bedroom</option>
                    <option value="2">2 Bedrooms</option>
                    <option value="3">3+ Bedrooms</option>
                </select>

                <input type="number" name="min_price" class="p-3 border rounded" placeholder="Min Price">
                <input type="number" name="max_price" class="p-3 border rounded" placeholder="Max Price">

                <button type="submit"
                    class="bg-[#CC9933] text-white px-4 py-3 rounded hover:bg-[#d88b1c]">Search</button>
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
        $featuredProperties = [
            ["title" => "Luxury Apartment", "location" => "Lagos, Nigeria", "price" => "₦50,000,000", "image" => "hero1.jpg", "type" => "Apartment", "status" => "For Sale", "bedrooms" => 3, "bathrooms" => 2, "size" => "1200 sqft", "garage" => 1],
            ["title" => "Modern Villa", "location" => "Abuja, Nigeria", "price" => "₦75,000,000", "image" => "hero2.jpg", "type" => "House", "status" => "For Sale", "bedrooms" => 4, "bathrooms" => 3, "size" => "1500 sqft", "garage" => 2],
            ["title" => "Beachfront Condo", "location" => "Lekki, Lagos", "price" => "₦30,000,000", "image" => "hero3.jpg", "type" => "Shortlet", "status" => "For Rent", "bedrooms" => 2, "bathrooms" => 2, "size" => "900 sqft", "garage" => 0],
            ["title" => "Affordable Duplex", "location" => "Ibadan, Nigeria", "price" => "₦20,000,000", "image" => "hero1.jpg", "type" => "House", "status" => "For Sale", "bedrooms" => 3, "bathrooms" => 2, "size" => "1000 sqft", "garage" => 1],
            ["title" => "Luxury Hotel Suite", "location" => "Victoria Island, Lagos", "price" => "₦60,000/night", "image" => "hero2.jpg", "type" => "Hotel", "status" => "For Rent", "bedrooms" => 1, "bathrooms" => 1, "size" => "500 sqft", "garage" => 0],
            ["title" => "Luxury Shortlet", "location" => "Banana Island, Lagos", "price" => "₦100,000/night", "image" => "hero3.jpg", "type" => "Shortlet", "status" => "For Rent", "bedrooms" => 3, "bathrooms" => 2, "size" => "1200 sqft", "garage" => 1]
        ];

        foreach ($featuredProperties as $property) {
            echo "
                <div class='border rounded-lg shadow-lg overflow-hidden bg-white hover:shadow-xl transition relative'>
                    <!-- Labels -->
                    <span class='absolute top-2 left-2 bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded'>{$property['type']}</span>
                    <span class='absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded'>{$property['status']}</span>

                    <!-- Property Image -->
                    <img src='public/images/{$property['image']}' class='w-full h-56 object-cover'>

                    <!-- Property Details -->
                    <div class='p-4'>
                        <p class='text-[#CC9933] font-semibold text-lg'>{$property['price']}</p>
                        <h3 class='text-[#092468] text-xl font-bold'>{$property['title']}</h3>
                        <p class='text-gray-600'>{$property['location']}</p>
                        
                        <!-- Property Features -->
                        <div class='mt-2 flex space-x-4 text-gray-500 text-sm'>
                            <span>🛏️ {$property['bedrooms']} Beds</span>
                            <span>🛁 {$property['bathrooms']} Baths</span>
                            <span>📏 {$property['size']}</span>
                            <span>🚗 {$property['garage']} Garage</span>
                        </div>

                        <!-- Agent & Wishlist Section -->
                        <div class='flex justify-between items-center mt-4'>
                            <div class='flex items-center'>
                                <img src='public/images/user.png' class='w-10 h-10 rounded-full mr-3'>
                                <span class='text-sm text-gray-700'>Agent Name</span>
                            </div>
                            <button class='text-gray-500 hover:text-red-500 transition'>
                                ❤️
                            </button>
                        </div>

                        <!-- View Details Button -->
                        <a href='property.php?id=1' class='mt-4 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]'>View Details</a>
                    </div>
                </div>
            ";
        }
        ?>
    </div>
</section>
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