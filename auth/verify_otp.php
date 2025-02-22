<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Verify OTP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../public/css/style.css">

    <!-- FontAwesome (For Icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- jQuery (For Navbar Mobile Toggle) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gray-100">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_SESSION['success'])): ?>
    <script>
    Swal.fire("Success!", "<?php echo $_SESSION['success']; ?>", "success");
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <script>
    Swal.fire("Error!", "<?php echo $_SESSION['error']; ?>", "error");
    </script>
    <?php unset($_SESSION['error']); endif; ?>


    <!-- Include Navbar -->
    <div class="relative z-50">
        </?php // include '../includes/navbar.php' ; ?>
    </div>

    <!-- OTP Verification Section -->
    <div class="container mx-auto px-4 py-40">
        <!-- Adjusted padding to avoid overlap -->
        <h2 class="text-3xl font-bold text-center text-[#092468]">Verify Your Account</h2>

        <form action="../process/verify_otp_process.php" method="POST"
            class="max-w-lg mx-auto mt-6 bg-white p-6 rounded-lg shadow-lg">
            <input type="text" name="otp" placeholder="Enter OTP" required class="w-full p-3 border rounded mt-2">
            <button type="submit"
                class="w-full bg-[#F4A124] text-white py-3 rounded hover:bg-[#d88b1c] mt-4">Verify</button>
        </form>
    </div>

    <!-- Include Footer -->
    </?php include '../includes/footer.php' ; ?>

    <!-- Mobile Navbar Toggle Fix -->
    <script>
    $(document).ready(function() {
        $("#mobile-menu-button").click(function() {
            $("#mobile-menu").toggleClass("hidden");
        });
    });
    </script>
</body>

</html>