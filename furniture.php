<?php
session_start();
include 'includes/db_connect.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniture - PishonServ Real Estate</title>
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

    /* Card Hover */
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
            <h1 class="text-3xl sm:text-5xl font-bold animate-hero-title">Furniture at PishonServ</h1>
            <p class="text-sm sm:text-lg mt-4 max-w-2xl animate-hero-text">
                Discover our curated collection of high-quality furniture to complement your dream home.
            </p>
        </div>
    </section>

    <!-- Furniture Products Section -->
    <section class="container mx-auto py-16 px-4">
        <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center animate-section-title">Our Furniture Collection</h2>
        <p class="text-gray-600 text-lg text-center mt-4 mb-12 max-w-3xl mx-auto animate-card">
            Browse our selection of stylish and durable furniture pieces, perfect for any home or office space. View details or add to your cart to start shopping.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 px-6 md:px-10">
            <div class="product-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.2s;">
                <img src="public/images/furniture1.jpg" alt="Modern Sofa" class="w-full h-48 object-cover rounded-md mb-4" loading="lazy" onerror="this.src='public/images/1.jpg'">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Modern Sofa</h3>
                <p class="text-gray-600 mb-2">A sleek, comfortable sofa with premium fabric upholstery, ideal for contemporary living spaces.</p>
                <p class="text-[#092468] font-semibold mb-4">Price: ₦250,000</p>
                <div class="flex space-x-4">
                    <a href="product_detail.php?id=1" class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">View Details</a>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="1">
                        <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg font-semibold">Add to Cart</button>
                    </form>
                </div>
            </div>
            <div class="product-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.4s;">
                <img src="public/images/furniture2.jpg" alt="Wooden Dining Table" class="w-full h-48 object-cover rounded-md mb-4" loading="lazy" onerror="this.src='public/images/2.jpg'">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Wooden Dining Table</h3>
                <p class="text-gray-600 mb-2">A sturdy oak dining table with a minimalist design, perfect for family gatherings.</p>
                <p class="text-[#092468] font-semibold mb-4">Price: ₦180,000</p>
                <div class="flex space-x-4">
                    <a href="product_detail.php?id=2" class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">View Details</a>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="2">
                        <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg font-semibold">Add to Cart</button>
                    </form>
                </div>
            </div>
            <div class="product-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.6s;">
                <img src="public/images/furniture3.jpg" alt="Ergonomic Office Chair" class="w-full h-48 object-cover rounded-md mb-4" loading="lazy" onerror="this.src='public/images/3.jpg'">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Ergonomic Office Chair</h3>
                <p class="text-gray-600 mb-2">An adjustable chair with lumbar support, designed for comfort during long work hours.</p>
                <p class="text-[#092468] font-semibold mb-4">Price: ₦95,000</p>
                <div class="flex space-x-4">
                    <a href="product_detail.php?id=3" class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">View Details</a>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="3">
                        <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg font-semibold">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Call-to-Action Section -->
    <section class="relative text-white text-center py-16 bg-cover bg-center"
        style="background-image: url('public/images/hero3.jpg');">
        <div class="absolute inset-0 bg-[#092468] bg-opacity-70"></div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold animate-section-title">Furnish Your Dream Space</h2>
            <p class="text-lg mt-4 max-w-2xl mx-auto animate-card">
                Explore our collection, add your favorite pieces to your cart, and create the perfect home.
            </p>
            <a href="cart.php"
                class="mt-6 inline-block btn-primary text-white px-6 py-3 rounded-lg font-semibold animate-card"
                style="animation-delay: 0.2s;">
                View Cart
            </a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Page-Specific JavaScript Error Handling -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure Zoho SalesIQ loads
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
                    console.error('Zoho SalesIQ widget failed to load on Furniture page.');
                }
            }, 5000);
        });
    </script>
</body>
</html>