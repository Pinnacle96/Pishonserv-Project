<?php
session_start();

// ✅ Check if an error message exists
$error_message = $_SESSION['error'] ?? "An error occurred during payment processing.";
unset($_SESSION['error']); // Clear error after displaying
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - Real Estate</title>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center max-w-md">
        <h2 class="text-[#092468] text-2xl font-bold">Payment Failed</h2>
        <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($error_message); ?></p>

        <div class="mt-4">
            <button onclick="redirectDashboard()"
                class="bg-[#CC9933] text-white px-4 py-2 rounded-md hover:bg-[#d88b1c] transition">
                Return to Dashboard
            </button>
            <button onclick="contactSupport()"
                class="ml-2 bg-gray-700 text-white px-4 py-2 rounded-md hover:bg-gray-800 transition">
                Contact Support
            </button>
        </div>
    </div>

    <script>
        // ✅ SweetAlert Popup
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'error',
                title: 'Payment Failed',
                text: "<?php echo addslashes($error_message); ?>",
                confirmButtonColor: "#CC9933"
            });
        });

        // ✅ Redirect to Dashboard
        function redirectDashboard() {
            window.location.href = "../dashboard/buyer_dashboard.php";
        }

        // ✅ Contact Support Page (Change URL if needed)
        function contactSupport() {
            window.location.href = "../support.php";
        }
    </script>
</body>

</html>