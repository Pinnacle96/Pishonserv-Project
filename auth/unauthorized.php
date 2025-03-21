<?php
session_start();
?>
<?php include '../includes/navbar.php'; ?>






<!-- Unauthorized Message -->
<div class="container mx-auto px-4 py-60 text-center">
    <i class="fas fa-lock icon mb-6"></i>
    <h1 class="text-4xl font-bold text-[#092468] mb-4">Unauthorized Access</h1>
    <p class="text-lg text-gray-600 mb-6">
        Oops! It seems you don’t have permission to view this page.
        Please log in with the appropriate credentials or contact support if you believe this is an error.
    </p>
    <a href="../index.php"
        class="inline-block bg-[#CC9933] text-white px-6 py-3 rounded-lg hover:bg-[#d88b1c] transition text-lg">
        Back to Home
    </a>
</div>

<?php include '../includes/footer.php'; ?>

</body>

</html>