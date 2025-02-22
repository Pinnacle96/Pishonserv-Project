<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-[#092468]">Forgot Password</h2>
        <form action="../process/forgot_password_process.php" method="POST" class="mt-4">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" name="email" required class="w-full p-3 border rounded mt-1"
                    placeholder="Enter your email">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
            </div>
            <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Send
                OTP</button>
        </form>
    </div>
</body>

</html>