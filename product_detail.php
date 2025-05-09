<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include files with error handling
$base_path = '/'; // Fallback base path; adjust as needed
try {
    if (file_exists('includes/db_connect.php')) {
        include 'includes/db_connect.php';
    } else {
        error_log('Missing db_connect.php');
    }
    if (file_exists('includes/navbar.php')) {
        include 'includes/navbar.php';
    } else {
        error_log('Missing navbar.php');
    }
} catch (Exception $e) {
    error_log('Include error: ' . $e->getMessage());
}

// Dummy product data (to be replaced with database query later)
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

$products = [
    1 => [
        'name' => 'Modern Sofa',
        'description' => 'A sleek, comfortable sofa with premium fabric upholstery, ideal for contemporary living spaces. Features a modern low-profile design with durable construction.',
        'price' => '₦250,000',
        'base_image' => 'public/images/hero5.jpg'
    ],
    2 => [
        'name' => 'Wooden Dining Table',
        'description' => 'A sturdy oak dining table with a minimalist design, perfect for family gatherings.',
        'price' => '₦180,000',
        'base_image' => 'public/images/hero7.jpg'
    ],
    3 => [
        'name' => 'Ergonomic Office Chair',
        'description' => 'An adjustable chair with lumbar support, designed for comfort during long work hours.',
        'price' => '₦95,000',
        'base_image' => 'public/images/hero6.jpg'
    ],
    4 => [
        'name' => 'Ceramic Vase',
        'description' => 'A handcrafted ceramic vase, perfect for adding a touch of elegance to any room.',
        'price' => '₦25,000',
        'base_image' => 'public/images/hero5.jpg'
    ],
    5 => [
        'name' => 'Woven Rug',
        'description' => 'A durable, stylish rug with intricate patterns, ideal for cozy living spaces.',
        'price' => '₦45,000',
        'base_image' => 'public/images/5.jpg'
    ],
    6 => [
        'name' => 'Wall Art',
        'description' => 'A vibrant canvas print to elevate your walls with modern artistry.',
        'price' => '₦35,000',
        'base_image' => 'public/images/2.jpg'
    ]
];

if (isset($products[$product_id])) {
    $product = $products[$product_id];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Product Not Found'; ?> - PishonServ Real Estate</title>
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
    body {
        background: #f5f7fa;
        color: #092468;
    }

    /* Hero Section */
    .hero-bg {
        background: linear-gradient(to bottom, rgba(9, 36, 104, 0.8), rgba(9, 36, 104, 0.5)), url('public/images/hero6.jpg');
        background-size: cover;
        background-position: center;
    }

    .hero-content {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-hero-title {
        animation: scaleIn 0.8s ease-out forwards;
    }

    .animate-hero-text {
        animation: fadeInUp 0.8s ease-out 0.2s forwards;
    }

    .animate-section-title {
        animation: scaleIn 0.6s ease-out forwards;
    }

    .animate-card {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    /* Product Card */
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(9, 36, 104, 0.2);
    }

    /* Button Styling */
    .btn-primary {
        background-color: #F4A124;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #d88b1c;
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(244, 161, 36, 0.3);
    }

    .btn-secondary {
        background-color: #092468;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #071a4d;
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(9, 36, 104, 0.3);
    }

    /* Color Swatch Styling */
    .color-swatch {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.3s ease;
    }

    .color-swatch.active, .color-swatch:hover {
        border-color: #F4A124;
    }

    /* CSS Filters for Colors */
    .filter-grey {
        filter: grayscale(50%) brightness(0.9);
    }

    .filter-beige {
        filter: hue-rotate(30deg) brightness(1.1) saturate(0.8);
    }

    .filter-navy {
        filter: hue-rotate(220deg) brightness(0.7) saturate(1.2);
    }

    .filter-black {
        filter: brightness(0.4) grayscale(80%);
    }

    .filter-white {
        filter: brightness(1.5) saturate(0.5);
    }

    .filter-red {
        filter: hue-rotate(0deg) saturate(1.5) brightness(0.9);
    }

    .filter-green {
        filter: hue-rotate(120deg) saturate(1.2) brightness(0.9);
    }

    .filter-blue {
        filter: hue-rotate(200deg) saturate(1.3) brightness(0.8);
    }

    .filter-brown {
        filter: hue-rotate(20deg) saturate(1.0) brightness(0.7);
    }

    .filter-cream {
        filter: hue-rotate(40deg) brightness(1.2) saturate(0.7);
    }

    /* Navbar Spacing */
    .content-start {
        padding-top: 5rem;
    }
    </style>
</head>

<body class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative w-full min-h-[400px] sm:min-h-[500px] hero-bg content-start overflow-hidden">
        <div class="relative z-10 hero-content text-center text-white px-6 py-40">
            <h1 class="text-3xl sm:text-5xl font-bold animate-hero-title"><?php echo $product ? htmlspecialchars($product['name']) : 'Product Not Found'; ?></h1>
            <p class="text-sm sm:text-lg mt-4 max-w-2xl animate-hero-text">
                <?php echo $product ? 'Elevate your space with this premium piece.' : 'Sorry, this product is not available.'; ?>
            </p>
        </div>
    </section>

    <!-- Product Details Section -->
    <section class="container mx-auto py-16 px-4">
        <?php if ($product): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-6 md:px-10">
                <!-- Product Image -->
                <div class="product-card bg-white p-6 rounded-lg shadow-md animate-card">
                    <img id="product-image" src="<?php echo $product['base_image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-96 object-cover rounded-md mb-4 filter-grey" loading="lazy" onerror="this.src='https://via.placeholder.com/600x400'">
                    <!-- Color Swatches -->
                    <div class="flex flex-wrap gap-4 mt-4">
                        <div class="color-swatch bg-gray-500 active" data-color="grey" style="background-color: #6B7280;"></div>
                        <div class="color-swatch bg-beige-500" data-color="beige" style="background-color: #F5F5DC;"></div>
                        <div class="color-swatch bg-navy-500" data-color="navy" style="background-color: #1E3A8A;"></div>
                        <div class="color-swatch bg-black" data-color="black" style="background-color: #000000;"></div>
                        <div class="color-swatch bg-white" data-color="white" style="background-color: #FFFFFF; border: 1px solid #ccc;"></div>
                        <div class="color-swatch bg-red-500" data-color="red" style="background-color: #EF4444;"></div>
                        <div class="color-swatch bg-green-500" data-color="green" style="background-color: #10B981;"></div>
                        <div class="color-swatch bg-blue-500" data-color="blue" style="background-color: #3B82F6;"></div>
                        <div class="color-swatch bg-brown-500" data-color="brown" style="background-color: #8B4513;"></div>
                        <div class="color-swatch bg-cream-500" data-color="cream" style="background-color: #FFFDD0;"></div>
                    </div>
                </div>
                <!-- Product Info -->
                <div class="product-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.2s;">
                    <h2 class="text-2xl font-bold text-[#092468] mb-2"><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p class="text-[#092468] font-semibold mb-4">Price: <?php echo htmlspecialchars($product['price']); ?></p>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="flex space-x-4">
                        <a href="<?php echo in_array($product_id, [1, 2, 3]) ? 'furniture.php' : 'interior_deco.php'; ?>" class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Back to Products</a>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg font-semibold">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center animate-card">
                <h2 class="text-2xl font-bold text-[#092468] mb-4">Product Not Found</h2>
                <p class="text-gray-600 mb-4">Sorry, the product you are looking for does not exist.</p>
                <a href="furniture.php" class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Back to Products</a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Call-to-Action Section -->
    <section class="relative text-white text-center py-16 bg-cover bg-center"
        style="background-image: url('public/images/hero3.jpg');">
        <div class="absolute inset-0 bg-[#092468] bg-opacity-70"></div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold animate-section-title">Complete Your Home</h2>
            <p class="text-lg mt-4 max-w-2xl mx-auto animate-card">
                Add this piece to your cart or explore our full collection to furnish your dream space.
            </p>
            <a href="<?php echo in_array($product_id, [1, 2, 3]) ? 'furniture.php' : 'interior_deco.php'; ?>"
                class="mt-6 inline-block btn-primary text-white px-6 py-3 rounded-lg font-semibold animate-card"
                style="animation-delay: 0.2s;">
                Explore More
            </a>
        </div>
    </section>

    <?php
    // Include footer with error handling
    try {
        if (file_exists('includes/footer.php')) {
            include 'includes/footer.php';
        } else {
            error_log('Missing footer.php');
        }
    } catch (Exception $e) {
        error_log('Footer include error: ' . $e->getMessage());
    }
    ?>

    <!-- Page-Specific JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Color swatch functionality
            const swatches = document.querySelectorAll('.color-swatch');
            const productImage = document.getElementById('product-image');

            swatches.forEach(swatch => {
                swatch.addEventListener('click', function() {
                    // Remove active class from all swatches
                    swatches.forEach(s => s.classList.remove('active'));
                    // Add active class to clicked swatch
                    this.classList.add('active');
                    // Remove all filter classes
                    productImage.classList.remove('filter-grey', 'filter-beige', 'filter-navy', 'filter-black', 'filter-white', 'filter-red', 'filter-green', 'filter-blue', 'filter-brown', 'filter-cream');
                    // Add the appropriate filter class
                    productImage.classList.add(`filter-${this.dataset.color}`);
                });
            });

            // Ensure Zoho SalesIQ loads (optional, can be commented out for testing)
            if (!window.$zoho || !window.$zoho.salesiq) {
                console.warn('Zoho SalesIQ not initialized. Loading fallback...');
                window.$zoho = window.$zoho || {};
                window.$zoho.salesiq = window.$zoho.salesiq || { ready: function() {} };
                var zohoScript = document.createElement('script');
                zohoScript.id = 'zsiqscript';
                zohoScript.src = 'https://salesiq.zohopublic.com/widget?wc=siqbf4b21531e2ec082c78d765292863df4a9787c4f0ba205509de7585b7a8d3e78';
                zohoScript.async = true;
                document.body.appendChild(zohoScript);
            }

            // Timeout to check if Zoho loaded
            setTimeout(function() {
                if (!document.querySelector('.zsiq_floatmain')) {
                    console.error('Zoho SalesIQ widget failed to load on Product Detail page.');
                }
            }, 5000);
        });
    </script>
</body>
</html>