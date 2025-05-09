<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Thank You - PishonServ</title>
    <link rel="stylesheet" href="https://cdn.tailwindcss.com">
</head>

<body class="bg-gray-100 pt-32 min-h-screen text-center px-6">
    <div class="max-w-2xl mx-auto bg-white shadow rounded p-8">
        <h1 class="text-3xl font-bold text-[#092468] mb-4">Thank You for Your Order!</h1>
        <p class="text-gray-700 text-lg mb-4">We've received your payment and your order is being processed.</p>
        <p class="text-sm text-gray-500 mb-6">You will receive an email confirmation shortly.</p>
        <a href="interior_deco.php"
            class="bg-[#F4A124] hover:bg-[#d88b1c] text-white px-6 py-2 rounded font-semibold">Continue Shopping</a>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>