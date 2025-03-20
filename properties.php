<?php
include 'includes/db_connect.php';
include 'includes/navbar.php';

// Pagination settings
$properties_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $properties_per_page;

// Get search parameters from GET request (from hero section or filter form)
$category = isset($_GET['category']) && $_GET['category'] !== '' ? $conn->real_escape_string($_GET['category']) : '';
$type = isset($_GET['type']) && $_GET['type'] !== '' ? $conn->real_escape_string($_GET['type']) : '';
$location = isset($_GET['location']) && $_GET['location'] !== '' ? $conn->real_escape_string($_GET['location']) : '';
$bedroom = isset($_GET['bedroom']) && $_GET['bedroom'] !== '' ? (int)$_GET['bedroom'] : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : '';

// Build the SQL query with filters
$query = "SELECT p.*, u.name AS agent_name, u.profile_image AS agent_image 
          FROM properties p 
          JOIN users u ON p.owner_id = u.id 
          WHERE p.admin_approved = 1";

// Apply filters only if they have values
$conditions = [];
if ($category) {
    $category = str_replace(['buy', 'rent', 'shortlet'], ['for_sale', 'for_rent', 'short_let'], $category);
    $conditions[] = "p.listing_type = '$category'";
}
if ($type) {
    $conditions[] = "p.type = '$type'";
}
if ($location) {
    $conditions[] = "p.location LIKE '%$location%'";
}
if ($bedroom) {
    $conditions[] = "p.bedrooms >= $bedroom";
}
if ($min_price !== '') {
    $conditions[] = "p.price >= $min_price";
}
if ($max_price !== '') {
    $conditions[] = "p.price <= $max_price";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Debug: Output the query to verify (remove in production)
echo "<!-- Debug Query: $query -->";

// Get total number of properties for pagination
$total_query = "SELECT COUNT(*) as total FROM properties p WHERE p.admin_approved = 1" . (!empty($conditions) ? " AND " . implode(" AND ", $conditions) : "");
$total_result = $conn->query($total_query);
$total_properties = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_properties / $properties_per_page);

// Add sorting and pagination to main query
$query .= " ORDER BY p.created_at DESC LIMIT $offset, $properties_per_page";
$result = $conn->query($query);
?>

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
        <form action="properties.php" method="GET" class="space-y-4">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            <select name="type" class="w-full p-3 border rounded text-sm md:text-base">
                <option value="">Property Type</option>
                <option value="apartment" <?php echo $type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                <option value="office" <?php echo $type === 'office' ? 'selected' : ''; ?>>Office</option>
                <option value="event_center" <?php echo $type === 'event_center' ? 'selected' : ''; ?>>Event Center
                </option>
                <option value="hotel" <?php echo $type === 'hotel' ? 'selected' : ''; ?>>Hotel</option>
                <option value="short_stay" <?php echo $type === 'short_stay' ? 'selected' : ''; ?>>Short Stay</option>
                <option value="house" <?php echo $type === 'house' ? 'selected' : ''; ?>>House</option>
                <option value="villa" <?php echo $type === 'villa' ? 'selected' : ''; ?>>Villa</option>
                <option value="condo" <?php echo $type === 'condo' ? 'selected' : ''; ?>>Condo</option>
                <option value="townhouse" <?php echo $type === 'townhouse' ? 'selected' : ''; ?>>Townhouse</option>
                <option value="duplex" <?php echo $type === 'duplex' ? 'selected' : ''; ?>>Duplex</option>
                <option value="penthouse" <?php echo $type === 'penthouse' ? 'selected' : ''; ?>>Penthouse</option>
                <option value="studio" <?php echo $type === 'studio' ? 'selected' : ''; ?>>Studio</option>
                <option value="bungalow" <?php echo $type === 'bungalow' ? 'selected' : ''; ?>>Bungalow</option>
                <option value="commercial" <?php echo $type === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                <option value="warehouse" <?php echo $type === 'warehouse' ? 'selected' : ''; ?>>Warehouse</option>
                <option value="retail" <?php echo $type === 'retail' ? 'selected' : ''; ?>>Retail</option>
                <option value="land" <?php echo $type === 'land' ? 'selected' : ''; ?>>Land</option>
                <option value="farmhouse" <?php echo $type === 'farmhouse' ? 'selected' : ''; ?>>Farmhouse</option>
                <option value="mixed_use" <?php echo $type === 'mixed_use' ? 'selected' : ''; ?>>Mixed Use</option>
            </select>
            <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>"
                class="w-full p-3 border rounded text-sm md:text-base" placeholder="Location">
            <select name="bedroom" class="w-full p-3 border rounded text-sm md:text-base">
                <option value="">Bedrooms</option>
                <option value="1" <?php echo $bedroom === 1 ? 'selected' : ''; ?>>1 Bedroom</option>
                <option value="2" <?php echo $bedroom === 2 ? 'selected' : ''; ?>>2 Bedrooms</option>
                <option value="3" <?php echo $bedroom === 3 ? 'selected' : ''; ?>>3+ Bedrooms</option>
            </select>
            <input type="number" name="min_price"
                value="<?php echo $min_price !== '' ? htmlspecialchars($min_price) : ''; ?>"
                class="w-full p-3 border rounded text-sm md:text-base" placeholder="Min Price">
            <input type="number" name="max_price"
                value="<?php echo $max_price !== '' ? htmlspecialchars($max_price) : ''; ?>"
                class="w-full p-3 border rounded text-sm md:text-base" placeholder="Max Price">
            <button type="submit"
                class="w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c] text-sm md:text-base">Apply
                Filters</button>
        </form>
    </aside>

    <!-- Property Listings -->
    <section class="col-span-1 md:col-span-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            if ($result->num_rows > 0) {
                while ($property = $result->fetch_assoc()) {
                    $images = explode(',', $property['images']);
                    $firstImage = !empty($images[0]) ? $images[0] : 'default.jpg';

                    $status = !empty($property['status']) ? ucfirst($property['status']) : 'Unknown';
                    $listingType = !empty($property['listing_type']) ? ucfirst(str_replace(['for_sale', 'for_rent', 'short_let'], ['Sale', 'Rent', 'Short Let'], $property['listing_type'])) : 'Unknown';

                    $statusClass = "bg-gray-500";
                    if ($property['status'] == 'available') $statusClass = "bg-green-500";
                    if ($property['status'] == 'booked') $statusClass = "bg-yellow-500";
                    if ($property['status'] == 'sold') $statusClass = "bg-red-500";
                    if ($property['status'] == 'rented') $statusClass = "bg-blue-500";

                    $agentImage = !empty($property['agent_image']) ? "public/uploads/{$property['agent_image']}" : "public/uploads/default.png";
                    $agentName = !empty($property['agent_name']) ? $property['agent_name'] : "Unknown Agent";

                    $isInWishlist = false;
                    if (isset($_SESSION['user_id'])) {
                        $checkWishlist = $conn->query("SELECT id FROM wishlist WHERE user_id = {$_SESSION['user_id']} AND property_id = {$property['id']}");
                        $isInWishlist = $checkWishlist->num_rows > 0;
                    }

                    echo "
                    <div class='border rounded-lg shadow-lg bg-white hover:shadow-xl transition relative'>
                        <span class='absolute top-4 left-4 text-white text-sm font-bold px-3 py-1 rounded {$statusClass} z-10'>{$status}</span>
                        <span class='absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded z-10'>{$listingType}</span>
                        <div class='relative w-full h-64 overflow-hidden'>
                            <div class='slider' id='slider-{$property['id']}'>";
                    foreach ($images as $index => $image) {
                        $hiddenClass = ($index === 0) ? '' : 'hidden';
                        echo "<img src='public/uploads/{$image}' class='w-full h-64 object-cover slider-image {$hiddenClass}' alt='Property Image'>";
                    }
                    echo "      </div>
                            <button class='absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev' data-slider='slider-{$property['id']}'>‹</button>
                            <button class='absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next' data-slider='slider-{$property['id']}'>›</button>
                        </div>
                        <div class='p-4'>
                            <p class='text-[#CC9933] font-semibold text-lg'>₦" . number_format($property['price'], 2) . "</p>
                            <h3 class='text-[#092468] text-xl font-bold'>{$property['title']} ({$property['type']})</h3>
                            <p class='text-gray-600'>{$property['location']}</p>
                            <div class='mt-2 flex flex-wrap text-gray-500 text-sm'>
                                <span class='mr-2'>🛏️ {$property['bedrooms']} Beds</span>
                                <span class='mr-2'>🛁 {$property['bathrooms']} Baths</span>
                                <span class='mr-2'>📏 {$property['size']}</span>
                                <span class='mr-2'>🚗 {$property['garage']} Garage</span>
                            </div>
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
                            <a href='property.php?id={$property['id']}' class='mt-4 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]'>View Details</a>
                        </div>
                    </div>
                    ";
                }
            } else {
                echo "<p class='text-center text-gray-600 col-span-full'>No properties found matching your criteria.</p>";
            }
            ?>
        </div>

        <!-- Pagination -->
        <div class="mt-10 flex justify-center gap-2">
            <?php if ($page > 1): ?>
                <a href="properties.php?page=<?php echo $page - 1; ?>&<?php echo http_build_query($_GET); ?>"
                    class="px-4 py-2 bg-gray-200 rounded-l-lg hover:bg-gray-300 text-sm md:text-base">← Previous</a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="properties.php?page=<?php echo $page + 1; ?>&<?php echo http_build_query($_GET); ?>"
                    class="px-4 py-2 bg-[#CC9933] text-white hover:bg-[#d88b1c] rounded-r-lg text-sm md:text-base">Next
                    →</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Include SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter toggle for mobile
        const filterToggle = document.getElementById('filter-toggle');
        const filters = document.getElementById('filters');
        if (filterToggle && filters) {
            filterToggle.addEventListener('click', function() {
                filters.classList.toggle('hidden');
                this.textContent = filters.classList.contains('hidden') ? 'Show Filters' : 'Hide Filters';
            });
        }

        // Image slider functionality
        document.querySelectorAll('.slider').forEach(slider => {
            let images = slider.querySelectorAll('.slider-image');
            let index = 0;

            function showImage(i) {
                images.forEach(img => img.classList.add('hidden'));
                images[i].classList.remove('hidden');
            }
            showImage(index);

            const parent = slider.closest('.relative');
            parent.querySelector('.prev').addEventListener('click', function() {
                index = (index > 0) ? index - 1 : images.length - 1;
                showImage(index);
            });

            parent.querySelector('.next').addEventListener('click', function() {
                index = (index < images.length - 1) ? index + 1 : 0;
                showImage(index);
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