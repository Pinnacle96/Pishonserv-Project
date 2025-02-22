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

        <form id="loginForm" class="mt-4">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Email</label>
                <input type="email" id="email" name="email" required class="w-full p-3 border rounded mt-1"
                    placeholder="Enter your email">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Password</label>
                <input type="password" id="password" name="password" required class="w-full p-3 border rounded mt-1"
                    placeholder="Enter password">
            </div>
            <button type="button" onclick="loginUser()"
                class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">Login</button>
        </form>

        <script>
        function loginUser() {
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            fetch("../process/login_process.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `email=${email}&password=${password}`
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes("success")) {
                        Swal.fire("Success!", "Login successful!", "success").then(() => {
                            window.location.href = "../dashboard.php";
                        });
                    } else {
                        Swal.fire("Error!", "Invalid email or password.", "error");
                    }
                })
                .catch(error => console.error("Error:", error));
        }
        </script>

</body>

</html>