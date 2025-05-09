<?php
session_start();
include 'includes/db_connect.php';
include 'includes/navbar.php';
include 'includes/config.php'; // LOCATIONIQ_API_KEY

// Pagination Config
$properties_per_page = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $properties_per_page;

// Fetch & Sanitize Search Filters
$category  = isset($_GET['category'])  ? trim($_GET['category'])  : '';
$type      = isset($_GET['type'])      ? trim($_GET['type'])      : '';
$location  = isset($_GET['location'])  ? trim($_GET['location'])  : '';
$bedroom   = isset($_GET['bedroom']) && $_GET['bedroom'] !== '' ? (int)$_GET['bedroom'] : null;
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

// CATEGORY MAP for Database Values
$category_map = [
    'buy' => 'for_sale',
    'rent' => 'for_rent',
    'shortlet' => 'short_let',
    'hotel' => 'hotel',
    'land' => 'land',
    'project' => 'project'
];

if (isset($category_map[$category])) {
    $category = $category_map[$category];
}

// Build Filters Dynamically
$filters = " WHERE p.admin_approved = 1";
$params = [];
$types = '';

if ($category) {
    $filters .= " AND p.listing_type = ?";
    $params[] = $category;
    $types .= 's';
}
if ($type) {
    $filters .= " AND p.type = ?";
    $params[] = $type;
    $types .= 's';
}
if ($location) {
    $filters .= " AND p.location LIKE ?";
    $params[] = "%$location%";
    $types .= 's';
}
if (!is_null($bedroom)) {
    $filters .= " AND p.bedrooms >= ?";
    $params[] = $bedroom;
    $types .= 'i';
}
if (!is_null($min_price)) {
    $filters .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}
if (!is_null($max_price)) {
    $filters .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

// Copy original filters params for total query
$total_params = $params;
$total_types = $types;

// Total Count Query
$total_query = "SELECT COUNT(*) as total FROM properties p JOIN users u ON p.owner_id = u.id" . $filters;

$total_stmt = $conn->prepare($total_query);
if (!empty($total_params)) {
    $total_stmt->bind_param($total_types, ...$total_params);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_properties = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_properties / $properties_per_page);

// Main Data Query
$query = "SELECT p.*, u.name AS agent_name, u.profile_image AS agent_image
          FROM properties p
          JOIN users u ON p.owner_id = u.id" . $filters . " ORDER BY p.created_at DESC LIMIT ?, ?";

$params[] = $offset;
$params[] = $properties_per_page;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

function getLocationCoordinates($location, $api_key)
{
    $url = "https://api.locationiq.com/v1/autocomplete.php?key=" . $api_key . "&q=" . urlencode($location) . "&limit=1";
    $response = @file_get_contents($url);
    if ($response === false) {
        error_log("Failed to fetch coordinates for: $location - API unreachable");
        return null;
    }
    $data = json_decode($response, true);
    if (!empty($data)) {
        error_log("Coordinates for $location: lat={$data[0]['lat']}, lon={$data[0]['lon']}");
        return ['lat' => $data[0]['lat'], 'lon' => $data[0]['lon']];
    }
    error_log("No coordinates found for: $location - API response: " . print_r($data, true));
    return null;
}
// Preserve query string without page
$query_string = $_GET;
unset($query_string['page']);
$base_url = 'properties.php?' . http_build_query($query_string);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings - PishonServ</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .map-container {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .map-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .property-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(9, 36, 104, 0.2);
        }

        .status-badge,
        .listing-type-badge {
            position: absolute;
            top: 4px;
            z-index: 10;
            color: white;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .status-badge {
            left: 4px;
        }

        .listing-type-badge {
            right: 4px;
            background-color: #ef4444;
        }
    </style>
</head>

<body class="bg-[#f5f7fa] text-[#092468] min-h-screen">
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
        <button id="filter-toggle"
            class="md:hidden w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c] mb-4">
            Show Filters
        </button>

        <!-- Sidebar Filters -->
        <aside class="hidden md:block col-span-1 bg-white p-6 rounded-lg shadow" id="filters">
            <h2 class="text-lg font-semibold text-[#092468] mb-4">Filter Results</h2>
            <form action="properties.php" method="GET" class="space-y-4">

                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category ?? ''); ?>">

                <select name="type" class="w-full p-3 border rounded text-sm md:text-base">
                    <option value="">Property Type</option>
                    <?php
                    $types = [
                        'apartment',
                        'office',
                        'event_center',
                        'hotel',
                        'short_stay',
                        'house',
                        'villa',
                        'condo',
                        'townhouse',
                        'duplex',
                        'penthouse',
                        'studio',
                        'bungalow',
                        'commercial',
                        'warehouse',
                        'retail',
                        'land',
                        'farmhouse',
                        'mixed_use'
                    ];
                    foreach ($types as $t) {
                        $selected = ($type === $t) ? 'selected' : '';
                        echo "<option value='$t' $selected>" . ucfirst(str_replace('_', ' ', $t)) . "</option>";
                    }
                    ?>
                </select>

                <input type="text" name="location" value="<?php echo htmlspecialchars($location ?? ''); ?>"
                    class="w-full p-3 border rounded text-sm md:text-base" placeholder="Location">

                <select name="bedroom" class="w-full p-3 border rounded text-sm md:text-base">
                    <option value="">Bedrooms</option>
                    <option value="1" <?php echo ($bedroom === 1) ? 'selected' : ''; ?>>1 Bedroom</option>
                    <option value="2" <?php echo ($bedroom === 2) ? 'selected' : ''; ?>>2 Bedrooms</option>
                    <option value="3" <?php echo ($bedroom === 3) ? 'selected' : ''; ?>>3+ Bedrooms</option>
                </select>

                <input type="number" name="min_price" value="<?php echo htmlspecialchars($min_price ?? ''); ?>"
                    class="w-full p-3 border rounded text-sm md:text-base" placeholder="Min Price" step="0.01">

                <input type="number" name="max_price" value="<?php echo htmlspecialchars($max_price ?? ''); ?>"
                    class="w-full p-3 border rounded text-sm md:text-base" placeholder="Max Price" step="0.01">

                <button type="submit"
                    class="w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c] text-sm md:text-base">
                    Apply Filters
                </button>

            </form>
        </aside>

        <!-- Property Listings -->
        <section class="col-span-1 md:col-span-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                if ($result->num_rows > 0) {
                    while ($property = $result->fetch_assoc()) {
                        $images = explode(',', $property['images']);
                        $firstImage = !empty($images[0]) ? "public/uploads/{$images[0]}" : 'public/uploads/default.jpg';
                        $status = ucfirst($property['status'] ?? 'unknown');
                        $listingType = ucfirst(str_replace(['for_sale', 'for_rent', 'short_let'], ['Sale', 'Rent', 'Short Let'], $property['listing_type'] ?? 'unknown'));

                        $statusClass = match ($property['status']) {
                            'available' => 'bg-green-500',
                            'booked' => 'bg-yellow-500',
                            'sold' => 'bg-red-500',
                            'rented' => 'bg-blue-500',
                            default => 'bg-gray-500'
                        };

                        $agentImage = $property['agent_image'] ? "public/uploads/{$property['agent_image']}" : 'public/uploads/default.png';
                        $agentName = $property['agent_name'] ?? 'Unknown Agent';

                        $coords = getLocationCoordinates($property['location'], LOCATIONIQ_API_KEY);
                        $mapUrl = $coords ? "https://maps.locationiq.com/v3/staticmap?key=" . LOCATIONIQ_API_KEY . "&center={$coords['lat']},{$coords['lon']}&zoom=15&size=300x200&markers=size:mid|color:red|{$coords['lat']},{$coords['lon']}" : null;

                        $isInWishlist = false;
                        if (isset($_SESSION['user_id'])) {
                            $wishlistStmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?");
                            $wishlistStmt->bind_param('ii', $_SESSION['user_id'], $property['id']);
                            $wishlistStmt->execute();
                            $isInWishlist = $wishlistStmt->get_result()->num_rows > 0;
                            $wishlistStmt->close();
                        }

                        // Fetch all booking dates
                        // $booking_info = "";
                        // if ($property['status'] === 'booked') {
                        //     $bookingStmt = $conn->prepare("SELECT check_in_date, check_out_date FROM bookings WHERE property_id = ? ORDER BY id DESC");
                        //     $bookingStmt->bind_param('i', $property['id']);
                        //     $bookingStmt->execute();
                        //     $bookingResult = $bookingStmt->get_result();

                        //     if ($bookingResult->num_rows > 0) {
                        //         while ($bookingData = $bookingResult->fetch_assoc()) {
                        //             $start_date = !empty($bookingData['check_in_date']) ? date('M d, Y', strtotime($bookingData['check_in_date'])) : null;
                        //             $end_date = !empty($bookingData['check_out_date']) ? date('M d, Y', strtotime($bookingData['check_out_date'])) : null;

                        //             if ($start_date && $end_date) {
                        //                 $booking_info .= "<p class='text-sm text-red-500 mt-1'>Booked from $start_date to $end_date</p>";
                        //             } elseif ($start_date) {
                        //                 $booking_info .= "<p class='text-sm text-red-500 mt-1'>Booked from $start_date</p>";
                        //             } elseif ($end_date) {
                        //                 $booking_info .= "<p class='text-sm text-red-500 mt-1'>Available until $end_date</p>";
                        //             }
                        //         }
                        //     } else {
                        //         $booking_info = "<p class='text-sm text-red-500 mt-1'>Currently Booked</p>";
                        //     }
                        //     $bookingStmt->close();
                        // }

                        echo "
                <div class='property-card border rounded-lg shadow-lg bg-white'>
                    <div class='relative w-full h-64 overflow-hidden'>
                        <span class='status-badge $statusClass'>$status</span>
                        <span class='listing-type-badge'>$listingType</span>
                        <div class='slider' id='slider-{$property['id']}'>";
                        foreach ($images as $index => $image) {
                            $hiddenClass = $index === 0 ? '' : 'hidden';
                            echo "<img src='public/uploads/$image' class='w-full h-64 object-cover slider-image $hiddenClass' alt='Property Image'>";
                        }
                        echo "</div>
                        <button class='absolute left-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full prev' data-slider='slider-{$property['id']}'>‹</button>
                        <button class='absolute right-2 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full next' data-slider='slider-{$property['id']}'>›</button>
                    </div>
                    <div class='p-4'>
                        <p class='text-[#CC9933] font-semibold text-lg'>₦" . number_format($property['price'], 2) . "</p>
                        <h3 class='text-[#092468] text-xl font-bold'>{$property['title']} ({$property['type']})</h3>
                        <p class='text-gray-600'>{$property['location']}</p>
                        $booking_info
                        <div class='mt-2 flex flex-wrap text-gray-500 text-sm'>
                            <span class='mr-2'>🛏️ {$property['bedrooms']} Beds</span>
                            <span class='mr-2'>🛁 {$property['bathrooms']} Baths</span>
                            <span class='mr-2'>📏 {$property['size']}</span>
                            <span class='mr-2'>🚗 {$property['garage']} Garage</span>
                        </div>
                        <div class='map-container mt-4'>
                            " . ($mapUrl ? "<img src='$mapUrl' alt='Property Map' loading='lazy'>" : "<p class='text-gray-500'>Map unavailable</p>") . "
                        </div>
                        <div class='flex justify-between items-center mt-4'>
                            <div class='flex items-center'>
                                <a href='agent_profile.php?id={$property['owner_id']}' class='flex items-center'>
    <img src='$agentImage' class='w-10 h-10 rounded-full mr-3' alt='Agent'>
    <span class='text-sm text-gray-700'>$agentName</span>
</a>
                            </div>
                            <button class='wishlist-btn " . ($isInWishlist ? 'text-red-500' : 'text-gray-500') . " hover:text-red-500 transition' 
                                    data-property-id='{$property['id']}' 
                                    data-in-wishlist='" . ($isInWishlist ? '1' : '0') . "'>
                                " . ($isInWishlist ? '❤️' : '🤍') . "
                            </button>
                        </div>
                        <a href='property.php?id={$property['id']}' class='mt-4 block text-center bg-[#CC9933] text-white px-4 py-2 rounded hover:bg-[#d88b1c]'>View Details</a>
                    </div>
                </div>";
                    }
                } else {
                    echo "<p class='text-center text-gray-600 col-span-full'>No properties found matching your criteria.</p>";
                }

                $stmt->close();
                $total_stmt->close();
                ?>
            </div>

            <!-- Pagination -->
            <div class="mt-10 flex justify-center gap-2">
    <?php if ($page > 1): ?>
        <a href="<?php echo $base_url . '&page=' . ($page - 1); ?>"
            class="px-4 py-2 bg-gray-200 rounded-l-lg hover:bg-gray-300 text-sm md:text-base">← Previous</a>
    <?php endif; ?>
    <?php if ($page < $total_pages): ?>
        <a href="<?php echo $base_url . '&page=' . ($page + 1); ?>"
            class="px-4 py-2 bg-[#CC9933] text-white hover:bg-[#d88b1c] rounded-r-lg text-sm md:text-base">Next →</a>
    <?php endif; ?>
</div>
        </section>


    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Toggle Filters Mobile
            const filterToggle = document.getElementById('filter-toggle');
            const filters = document.getElementById('filters');

            if (filterToggle && filters) {
                filterToggle.addEventListener('click', () => {
                    filters.classList.toggle('hidden');
                    filterToggle.textContent = filters.classList.contains('hidden') ? 'Show Filters' :
                        'Hide Filters';
                });
            }

            // Slider Functionality
            document.querySelectorAll('.slider').forEach(slider => {
                const images = slider.querySelectorAll('.slider-image');
                let index = 0;

                function showImage(i) {
                    images.forEach(img => img.classList.add('hidden'));
                    images[i].classList.remove('hidden');
                }

                showImage(index);

                const parent = slider.closest('.relative');
                parent.querySelector('.prev').addEventListener('click', () => {
                    index = (index > 0) ? index - 1 : images.length - 1;
                    showImage(index);
                });

                parent.querySelector('.next').addEventListener('click', () => {
                    index = (index < images.length - 1) ? index + 1 : 0;
                    showImage(index);
                });
            });

            // Wishlist Toggle
            document.querySelectorAll('.wishlist-btn').forEach(button => {
                button.addEventListener('click', async function() {

                    const isLoggedIn =
                        <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
                    const currentPage = window.location.href; // Get current page url

                    if (!isLoggedIn) {
                        Swal.fire({
                            title: 'Login Required!',
                            text: 'Please log in to save properties to your wishlist.',
                            icon: 'warning',
                            confirmButtonText: 'Login Now',
                            confirmButtonColor: '#092468'
                        }).then(result => {
                            if (result.isConfirmed) {
                                window.location.href = 'auth/login.php?redirect=' +
                                    encodeURIComponent(currentPage);
                            }
                        });
                        return;
                    }

                    const propertyId = this.dataset.propertyId;
                    const isInWishlist = this.dataset.inWishlist === '1';

                    try {
                        const response = await fetch('wishlist_toggle.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            credentials: 'include', // Send cookies with request
                            body: JSON.stringify({
                                property_id: propertyId,
                                action: isInWishlist ? 'remove' : 'add'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.dataset.inWishlist = data.inWishlist ? '1' : '0';
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
                        console.error('Wishlist Error:', error);
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


</body>

</html>