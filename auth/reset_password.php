<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
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

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-[#092468]">Reset Your Password</h2>
        <p class="text-gray-600 text-center mt-2">Enter a new password to secure your account.</p>

        <!-- Display Success or Error Messages -->
        <!-- </?php if (isset($_SESSION['error'])): ?>
        <script>
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "</?php echo $_SESSION['error']; unset($_SESSION['error']); ?>",
            confirmButtonText: "OK"
        });
        </script>
        </?php endif; ?> -->

        <form action="../process/reset_password_process.php" method="POST" class="mt-6 space-y-4">
            <div class="relative">
                <input type="password" name="new_password" required
                    class="w-full p-3 border rounded-lg pl-10 focus:ring-2 focus:ring-[#092468]"
                    placeholder="Enter new password">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
            </div>

            <button type="submit"
                class="w-full bg-[#F4A124] text-white py-3 rounded-lg font-bold hover:bg-[#d88b1c] transition">
                Reset Password
            </button>
        </form>

        <p class="text-center text-gray-600 mt-4">Remember your password?
            <a href="login.php" class="text-blue-500 font-semibold">Login</a>
        </p>
    </div>
</body>

</html>