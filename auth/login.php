<?php
session_start();
include '../includes/db_connect.php';
?>



<body class="bg-gray-100 flex flex-col min-h-screen">
    <?php include '../includes/navbar.php'; ?>

    <main class="flex-grow flex items-center justify-center">
        <div class="bg-white p-8 py-40  mt-5 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold text-center text-[#092468] mb-6 ">Login to Your Account</h2>

            <form action="../process/login_process.php" method="POST" class="space-y-4" enctype="multipart/form-data">
                <?php echo csrf_token_input(); ?>

                <div>
                    <label class="block text-gray-700 font-semibold">Email</label>
                    <input type="email" name="email" required
                        class="w-full p-3 border rounded mt-1 focus:outline-none focus:ring-2 focus:ring-[#F4A124] transition duration-200"
                        placeholder="Enter your email"
                        value="<?php echo isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : ''; ?>">
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
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo addslashes($_SESSION["success"]); ?>',
                confirmButtonColor: '#092468'
            });
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?php echo addslashes($_SESSION["error"]); ?>',
                confirmButtonColor: '#092468'
            });
            <?php 
                unset($_SESSION['error']); 
                unset($_SESSION['form_data']);
            ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
