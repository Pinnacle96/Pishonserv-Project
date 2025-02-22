<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pishonserv</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-[#092468]">Login to Your Account</h2>

        <!-- Display Success or Error Messages -->
        <?php if (isset($_SESSION['error'])): ?>
        <script>
        Swal.fire({
            title: "Error!",
            text: "<?php echo $_SESSION['error']; unset($_SESSION['error']); ?>",
            icon: "error",
            confirmButtonText: "OK"
        });
        </script>
        <?php endif; ?>

        <form action="../process/login_process.php" method="POST" class="mt-4">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" required class="w-full p-3 border rounded mt-1"
                    placeholder="Enter your email">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Password</label>
                <input type="password" name="password" required class="w-full p-3 border rounded mt-1"
                    placeholder="Enter password">
            </div>
            <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Login</button>
        </form>

        <p class="text-center text-gray-600 mt-4">
            <a href="forgot_password.php" class="text-blue-500 font-semibold">Forgot Password?</a>
        </p>
        <p class="text-center text-gray-600 mt-2">Don't have an account?
            <a href="register.php" class="text-blue-500 font-semibold">Sign Up</a>
        </p>
    </div>
</body>

</html>