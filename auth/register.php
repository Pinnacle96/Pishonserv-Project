<?php
session_start();
include '../includes/db_connect.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire("Success!", "<?php echo $_SESSION['success']; ?>", "success");
    </script>
<?php unset($_SESSION['success']);
endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire("Error!", "<?php echo $_SESSION['error']; ?>", "error");
    </script>
<?php unset($_SESSION['error']);
endif; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container mx-auto px-4 py-40">
    <h2 class="text-3xl font-bold text-center text-[#092468]">Register</h2>

    <form action="../process/register_process.php" method="POST" enctype="multipart/form-data"
        class="max-w-lg mx-auto mt-6 bg-white p-6 rounded-lg shadow-lg">

        <input type="text" name="name" placeholder="First Name" required class="w-full p-3 border rounded mt-2">

        <input type="text" name="lname" placeholder="Last Name" required class="w-full p-3 border rounded mt-2">

        <input type="email" name="email" placeholder="Email" required class="w-full p-3 border rounded mt-2">

        <input type="text" name="phone" placeholder="Your Phone Number" required class="w-full p-3 border rounded mt-2">

        <input type="text" name="address" placeholder="Residential Address" required
            class="w-full p-3 border rounded mt-2">

        <input type="text" name="nin" placeholder="National Identification Number (NIN)" required
            class="w-full p-3 border rounded mt-2">

        <input type="password" name="password" placeholder="Password" required class="w-full p-3 border rounded mt-2">

        <select name="role" class="w-full p-3 border rounded mt-2" required>
            <option value="buyer">Customer</option>
            <option value="agent">Agent</option>
            <option value="owner">Property Owner</option>
            <option value="hotel_owner">Hotel Owner</option>
        </select>

        <label for="profile_image" class="block font-semibold mt-3">Profile Picture:</label>
        <input type="file" name="profile_image" id="profile_image" class="w-full p-3 border rounded">

        <button type="submit"
            class="w-full bg-[#CC9933] text-white py-3 rounded hover:bg-[#d88b1c] mt-4">Register</button>

        <p class="text-center text-gray-600 mt-2">
            Already have an account?
            <a href="login.php" class="text-blue-500 font-semibold">Sign in</a>
        </p>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
</body>

</html>