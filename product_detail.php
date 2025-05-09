<?php
session_start();

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Base path
$base_path = '/';

// Load DB and navbar
try {
    if (file_exists('includes/db_connect.php')) {
        include 'includes/db_connect.php';
    } else {
        exit("<p>Database connection file missing.</p>");
    }

    if (file_exists('includes/navbar.php')) {
        include 'includes/navbar.php';
    }
} catch (Exception $e) {
    error_log('Include error: ' . $e->getMessage());
    exit("<p>Internal error. Please try again later.</p>");
}

// Validate input
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$category_slug = isset($_GET['category']) ? $_GET['category'] : 'interior_deco';

$product = null;

if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Product Not Found'; ?> - PishonServ</title>
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        crossorigin="anonymous" />

    <style>
    body {
        background: #f5f7fa;
        color: #092468;
    }

    .hero-bg {
        background: linear-gradient(to bottom, rgba(9, 36, 104, 0.8), rgba(9, 36, 104, 0.5)),
            url('public/images/hero6.jpg');
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

    .animate-hero-title,
    .animate-hero-text,
    .animate-section-title,
    .animate-card {
        opacity: 0;
        animation: fadeInUp 0.6s ease-out forwards;
    }

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

    .product-card:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 20px rgba(9, 36, 104, 0.2);
    }

    .btn-primary,
    .btn-secondary {
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #F4A124;
    }

    .btn-primary:hover {
        background: #d88b1c;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #092468;
    }

    .btn-secondary:hover {
        background: #071a4d;
        transform: translateY(-2px);
    }

    .color-swatch {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: 0.3s;
    }

    .color-swatch.active,
    .color-swatch:hover {
        border-color: #F4A124;
    }

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

    #product-image {
        transition: transform 0.3s ease;
    }

    #product-image.zoomed {
        transform: scale(1.07);
    }
    </style>
</head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
<?php if (isset($_SESSION['success'])): ?>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '<?php echo $_SESSION['success']; ?>'
});
<?php unset($_SESSION['success']);
    endif; ?>

<?php if (isset($_SESSION['error'])): ?>
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '<?php echo $_SESSION['error']; ?>'
});
<?php unset($_SESSION['error']);
    endif; ?>
</script>

<body class="min-h-screen">
    <!-- Hero -->
    <section class="hero-bg content-start relative w-full min-h-[400px] overflow-hidden">
        <div class="hero-content relative z-10 text-white text-center px-6 py-40">
            <h1 class="text-3xl sm:text-5xl font-bold animate-hero-title">
                <?php echo $product ? htmlspecialchars($product['name']) : 'Product Not Found'; ?>
            </h1>
            <p class="text-sm sm:text-lg mt-4 max-w-2xl animate-hero-text">
                <?php echo $product ? 'Elevate your space with this premium piece.' : 'Sorry, this product is not available.'; ?>
            </p>
        </div>
    </section>

    <!-- Product Details -->
    <section class="container mx-auto py-16 px-4">
        <?php if ($product): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-6 md:px-10">
            <div class="product-card bg-white p-6 rounded-lg shadow-md animate-card">
                <img id="product-image" src="<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                    class="w-full h-96 object-cover rounded-md mb-4 filter-grey" loading="lazy"
                    onerror="this.src='https://via.placeholder.com/600x400'">

                <div class="flex flex-wrap gap-4 mt-4">
                    <?php
                        $colors = ['grey', 'beige', 'navy', 'black', 'white', 'red', 'green', 'blue', 'brown', 'cream'];
                        $colorHex = [
                            'grey' => '#6B7280',
                            'beige' => '#F5F5DC',
                            'navy' => '#1E3A8A',
                            'black' => '#000',
                            'white' => '#FFF',
                            'red' => '#EF4444',
                            'green' => '#10B981',
                            'blue' => '#3B82F6',
                            'brown' => '#8B4513',
                            'cream' => '#FFFDD0'
                        ];
                        foreach ($colors as $i => $color): ?>
                    <div class="color-swatch <?php echo $color === 'grey' ? 'active' : ''; ?>"
                        data-color="<?php echo $color; ?>"
                        style="background-color: <?php echo $colorHex[$color]; ?><?php echo $color === 'white' ? '; border: 1px solid #ccc;' : ''; ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="product-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.2s;">
                <h2 class="text-2xl font-bold text-[#092468] mb-2"><?php echo htmlspecialchars($product['name']); ?>
                </h2>
                <p class="text-[#092468] font-semibold mb-4">Price: ₦<?php echo number_format($product['price'], 2); ?>
                </p>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($product['description']); ?></p>
                <div class="flex space-x-4">
                    <a href="<?php echo $category_slug === 'furniture' ? 'furniture.php' : 'interior_deco.php'; ?>"
                        class=" btn-primary text-white px-4 py-2 rounded-lg font-semibold">Back to Products</a>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="color" id="selected-color" value="grey">
                        <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg font-semibold">
                            Add to Cart
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center animate-card">
            <h2 class="text-2xl font-bold text-[#092468] mb-4">Product Not Found</h2>
            <p class="text-gray-600 mb-4">Sorry, the product you are looking for does not exist.</p>
            <a href="interior_deco.php" class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Back to
                Products</a>
        </div>
        <?php endif; ?>
    </section>

    <!-- CTA -->
    <section class="relative text-white text-center py-16 bg-cover bg-center"
        style="background-image: url('public/images/hero3.jpg');">
        <div class="absolute inset-0 bg-[#092468] bg-opacity-70"></div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold animate-section-title">Complete Your Home</h2>
            <p class="text-lg mt-4 max-w-2xl mx-auto animate-card">
                Add this piece to your cart or explore our full collection to furnish your dream space.
            </p>
            <a href="<?php echo $category_slug === 'furniture' ? 'furniture.php' : 'interior_deco.php'; ?>"
                class="mt-6 inline-block btn-primary text-white px-6 py-3 rounded-lg font-semibold animate-card"
                style="animation-delay: 0.2s;">
                Explore More
            </a>
        </div>
    </section>

    <?php
    try {
        if (file_exists('includes/footer.php')) include 'includes/footer.php';
    } catch (Exception $e) {
        error_log('Footer error: ' . $e->getMessage());
    }
    ?>

    <!-- Page-Specific JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Color swatch filter + zoom
        const swatches = document.querySelectorAll('.color-swatch');
        const productImage = document.getElementById('product-image');

        swatches.forEach(swatch => {
            swatch.addEventListener('click', function() {
                // Remove all active states
                swatches.forEach(s => s.classList.remove('active'));
                this.classList.add('active');

                // Remove all previous filter classes
                const allFilters = [
                    'filter-grey', 'filter-beige', 'filter-navy', 'filter-black',
                    'filter-white',
                    'filter-red', 'filter-green', 'filter-blue', 'filter-brown',
                    'filter-cream'
                ];
                productImage.classList.remove(...allFilters);

                // Add new filter + zoom
                const newFilter = 'filter-' + this.dataset.color;
                productImage.classList.add(newFilter, 'zoomed');
                document.getElementById('selected-color').value = this.dataset.color;


                setTimeout(() => productImage.classList.remove('zoomed'), 500);
            });
        });

        // Zoho SalesIQ fallback
        if (!window.$zoho || !window.$zoho.salesiq) {
            console.warn('Zoho SalesIQ not initialized. Loading fallback...');
            window.$zoho = window.$zoho || {};
            window.$zoho.salesiq = window.$zoho.salesiq || {
                ready: function() {}
            };
            const zohoScript = document.createElement('script');
            zohoScript.id = 'zsiqscript';
            zohoScript.src =
                'https://salesiq.zohopublic.com/widget?wc=siqbf4b21531e2ec082c78d765292863df4a9787c4f0ba205509de7585b7a8d3e78';
            zohoScript.async = true;
            document.body.appendChild(zohoScript);
        }

        // Check if Zoho loaded
        setTimeout(function() {
            if (!document.querySelector('.zsiq_floatmain')) {
                console.error('Zoho SalesIQ widget failed to load on Product Detail page.');
            }
        }, 5000);
    });
    </script>

</body>

</html>