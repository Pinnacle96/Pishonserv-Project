<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .maintenance-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 1.5rem;
            padding: 2.5rem;
            max-width: 32rem;
            width: 90%;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .btn-hover {
            background-color: #F4A124;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            display: inline-block;
            text-decoration: none;
            transition: transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-hover:hover {
            transform: translateY(-3px);
            background-color: #d88b1c;
            box-shadow: 0 4px 12px rgba(244, 161, 36, 0.3);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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

        .animate-heading {
            animation: scaleIn 0.6s ease-out forwards;
        }

        .animate-text {
            animation: fadeInUp 0.6s ease-out 0.2s forwards;
        }

        .animate-button {
            animation: fadeInUp 0.5s ease-out 0.4s forwards;
        }

        .animate-login {
            animation: fadeInUp 0.6s ease-out 0.6s forwards;
        }
    </style>
</head>

<body class="flex items-center justify-center">
    <div class="maintenance-container text-center">
        <h1 class="animate-heading text-4xl md:text-5xl font-bold text-[#092468] mb-4 flex items-center justify-center">
            <span class="mr-2">🚧</span> Site Under Maintenance <span class="ml-2">🚧</span>
        </h1>
        <p class="animate-text text-gray-600 text-lg md:text-xl mb-6">
            We're performing scheduled maintenance to improve your experience. Please check back later!
        </p>
        <a href="/pishonserv.com/index.php" class="animate-button btn-hover">
            Back to Home
        </a>
        <p class="animate-login text-gray-600 mt-6">
            Superadmin?
            <a href="/pishonserv.com/auth/login.php"
                class="text-[#092468] font-semibold hover:text-[#F4A124] transition-colors duration-200">
                Log in here
            </a>
        </p>
    </div>
</body>

</html>