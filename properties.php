<?php include 'includes/db_connect.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- Page Header -->
<section class="bg-gray-100 py-20 mt-20 text-center">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold text-[#092468]">Find Your Perfect Home</h1>
        <p class="text-gray-600 text-lg mt-2">Browse through our latest property listings</p>
    </div>
</section>

<!-- Main Content -->
<div class="container mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-4 gap-8 px-6 md:px-10 lg:px-16">

    <!-- Sidebar Filters -->
    <aside class="hidden md:block col-span-1 bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold text-[#092468] mb-4">Filter Results</h2>
        <form action="#" method="GET" class="space-y-4">
            <select name="type" class="w-full p-3 border rounded">
                <option value="">Property Type</option>
                <option value="apartment">Apartment</option>
                <option value="hotel">Hotel</option>
                <option value="shortlet">Shortlet</option>
                <option value="house">House</option>
                <option value="land">Land</option>
            </select>
            <input type="text" name="location" class="w-full p-3 border rounded" placeholder="Location">
            <input type="number" name="min_price" class="w-full p-3 border rounded" placeholder="Min Price">
            <input type="number" name="max_price" class="w-full p-3 border rounded" placeholder="Max Price">
            <button type="submit" class="w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c]">Apply
                Filters</button>
        </form>
    </aside>

    <!-- Property Listings -->
    <section class="col-span-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $properties = [
                ["title" => "Luxury Apartment", "location" => "Lagos, Nigeria", "price" => "₦50,000,000", "image" => "hero1.jpg", "type" => "Apartment", "status" => "For Sale", "bedrooms" => 3, "bathrooms" => 2, "size" => "1200 sqft"],
                ["title" => "Modern Villa", "location" => "Abuja, Nigeria", "price" => "₦75,000,000", "image" => "hero2.jpg", "type" => "House", "status" => "For Sale", "bedrooms" => 4, "bathrooms" => 3, "size" => "1500 sqft"],
                ["title" => "Beachfront Condo", "location" => "Lekki, Lagos", "price" => "₦30,000,000", "image" => "hero3.jpg", "type" => "Shortlet", "status" => "For Rent", "bedrooms" => 2, "bathrooms" => 2, "size" => "900 sqft"],
                ["title" => "Affordable Duplex", "location" => "Ibadan, Nigeria", "price" => "₦20,000,000", "image" => "hero1.jpg", "type" => "House", "status" => "For Sale", "bedrooms" => 3, "bathrooms" => 2, "size" => "1000 sqft"],
                ["title" => "Luxury Hotel Suite", "location" => "Victoria Island, Lagos", "price" => "₦60,000/night", "image" => "hero2.jpg", "type" => "Hotel", "status" => "For Rent", "bedrooms" => 1, "bathrooms" => 1, "size" => "500 sqft"],
                ["title" => "Cozy Bungalow", "location" => "Enugu, Nigeria", "price" => "₦18,000,000", "image" => "hero3.jpg", "type" => "House", "status" => "For Sale", "bedrooms" => 2, "bathrooms" => 2, "size" => "800 sqft"],
                ["title" => "Luxury Shortlet", "location" => "Banana Island, Lagos", "price" => "₦100,000/night", "image" => "hero1.jpg", "type" => "Shortlet", "status" => "For Rent", "bedrooms" => 3, "bathrooms" => 2, "size" => "1200 sqft"],
                ["title" => "Newly Built Duplex", "location" => "Port Harcourt, Nigeria", "price" => "₦45,000,000", "image" => "hero2.jpg", "type" => "House", "status" => "For Sale", "bedrooms" => 4, "bathrooms" => 3, "size" => "1400 sqft"],
                ["title" => "Elegant Studio Apartment", "location" => "Kano, Nigeria", "price" => "₦12,000,000", "image" => "hero3.jpg", "type" => "Apartment", "status" => "For Sale", "bedrooms" => 1, "bathrooms" => 1, "size" => "600 sqft"]
            ];

            foreach ($properties as $property) {
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
                            <div class='mt-2 flex space-x-2 text-gray-500 text-sm'>
                                <span>{$property['bedrooms']} Beds</span>
                                <span>{$property['bathrooms']} Baths</span>
                                <span>{$property['size']}</span>
                            </div>
                            <a href='property.php?id=1' class='mt-4 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]'>View Details</a>
                        </div>
                    </div>
                ";
            }
            ?>
        </div>

        <!-- Pagination -->
        <div class="mt-10 flex justify-center">
            <a href="#" class="px-4 py-2 bg-gray-200 rounded-l-lg hover:bg-gray-300">← Previous</a>
            <a href="#" class="px-4 py-2 bg-[#CC9933] text-white hover:bg-[#d88b1c]">Next →</a>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>