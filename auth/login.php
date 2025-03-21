<?php
session_start(); // Start session at the top
include '../includes/db_connect.php';
?>

<?php include '../includes/navbar.php'; ?>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-[#092468] mb-6">Login to Your Account</h2>

        <form action="../process/login_process.php" method="POST" class="space-y-4" enctype="multipart/form-data">
            <?php echo csrf_token_input(); ?>

            <div>
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" required
                    class="w-full p-3 border rounded mt-1 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="Enter your email">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold">Password</label>
                <input type="password" name="password" required
                    class="w-full p-3 border rounded mt-1 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                    placeholder="Enter your password">
            </div>
            <button type="submit"
                class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c] focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200">
                Login
            </button>
        </form>

        <p class="text-center text-gray-600 mt-4">
            <a href="forgot_password.php" class="text-blue-500 font-semibold hover:underline">Forgot Password?</a>
        </p>
        <p class="text-center text-gray-600 mt-2">
            Don't have an account?
            <a href="register.php" class="text-blue-500 font-semibold hover:underline">Sign Up</a>
        </p>
    </div>

    <!-- SweetAlert2 Notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo addslashes($_SESSION['success']); ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#092468'
                });
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?php echo addslashes($_SESSION['error']); ?>',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#092468'
                });
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>