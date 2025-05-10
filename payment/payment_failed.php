<?php
session_start();
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$base_path = '/';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - PishonServ</title>
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center py-10 px-4">
    <div class="bg-white p-10 rounded-lg shadow-md max-w-md w-full text-center">
        <div class="mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-red-500 mx-auto" viewBox="0 0 24 24"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5a.75.75 0 00-1.5 0v6a.75.75 0 001.5 0V7zm0 8.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"
                    clip-rule="evenodd" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Payment Failed</h2>
        <p class="text-gray-600 mb-4">We're sorry, but your payment could not be processed. Please try again or use
            another payment method.</p>

        <?php if ($order_id): ?>
            <p class="text-sm text-gray-500 mb-6">Order ID: <strong>#<?php echo $order_id; ?></strong></p>
        <?php endif; ?>

        <a href="<?php echo $base_path; ?>../cart.php"
            class="inline-block bg-[#092468] text-white px-6 py-2 rounded hover:bg-[#071a4d] transition-all">Return to
            Cart</a>
    </div>
</body>

</html>