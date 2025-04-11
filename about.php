<?php
session_start();
include 'includes/db_connect.php';
include 'includes/navbar.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - PishonServ Real Estate</title>
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">

    <style>
    body {
        background: #f5f7fa;
        color: #092468;
    }

    /* Hero Section */
    .hero-bg {
        background: linear-gradient(to bottom, rgba(9, 36, 104, 0.8), rgba(9, 36, 104, 0.5)), url('public/images/hero1.jpg');
        background-size: cover;
        background-position: center;
    }

    .hero-content {
        min-height: 100%;
        /* Ensures it fills the hero section */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
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

    .animate-hero-title {
        animation: scaleIn 0.8s ease-out forwards;
    }

    .animate-hero-text {
        animation: fadeInUp 0.8s ease-out 0.2s forwards;
    }

    .animate-section-title {
        animation: scaleIn 0.6s ease-out forwards;
    }

    .animate-card {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    /* Card Hover */
    .team-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .team-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(9, 36, 104, 0.2);
    }

    /* Button Styling */
    .btn-primary {
        background-color: #F4A124;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #d88b1c;
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(244, 161, 36, 0.3);
    }

    /* Social Icons */
    .social-icon {
        color: #092468;
        transition: color 0.3s ease;
    }

    .social-icon:hover {
        color: #F4A124;
    }

    /* Navbar Spacing */
    .content-start {
        padding-top: 5rem;
        /* Matches navbar height */
    }
    </style>
</head>

<body class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative w-full min-h-[400px] sm:min-h-[500px] hero-bg content-start overflow-hidden">
        <div class="relative z-10 hero-content text-center text-white px-6 py-40">
            <h1 class="text-3xl sm:text-5xl font-bold animate-hero-title ">About PishonServ</h1>
            <p class="text-sm sm:text-lg mt-4 max-w-2xl animate-hero-text">
                Connecting you to your dream property with trust, innovation, and excellence.
            </p>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="container mx-auto py-16 px-4">
        <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center animate-section-title">Who We Are</h2>
        <p class="text-gray-600 text-lg text-center mt-4 mb-12 max-w-3xl mx-auto animate-card">
            <strong>Pishonserv</strong> is your premier destination for finding the perfect property to rent or buy.
            Whether you’re looking for a short-term rental or a long-term investment, we specialize in offering a
            diverse range of high-quality properties that cater to all your needs.
            <br>
            <strong>Quality Listings:</strong> Each property in our portfolio is vetted to meet our high standards of
            quality, comfort, and aesthetic appeal.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-6 md:px-10">
            <div class="bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.2s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Our Mission</h3>
                <p class="text-gray-600">
                    To empower our clients with transparent, efficient access to real estate opportunities, ensuring
                    satisfaction at every step.
                </p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.4s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Our Vision</h3>
                <p class="text-gray-600">
                    To revolutionize real estate in Nigeria and beyond, making it accessible and enjoyable for everyone.
                </p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="container mx-auto py-16 px-4 bg-gray-100">
        <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center animate-section-title">Meet Our Team</h2>
        <p class="text-gray-600 text-lg text-center mt-4 mb-12 max-w-2xl mx-auto animate-card">
            Our passionate team of experts is dedicated to guiding you through your real estate journey.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 px-6 md:px-10">
            <div class="team-card bg-white p-6 rounded-lg shadow-md text-center animate-card"
                style="animation-delay: 0.2s;">
                <img src="public/teams/team1.jpg" alt="Jane Doe" class="w-24 h-24 rounded-full mx-auto mb-4"
                    onerror="this.src='public/uploads/default.png'">
                <h3 class="text-xl font-semibold text-[#092468]">Jane Doe</h3>
                <p class="text-gray-600 mb-3">Founder & CEO</p>
                <div class="flex justify-center space-x-4">
                    <a href="https://twitter.com/janedoe" target="_blank" class="social-icon">
                        <i class="fab fa-twitter text-lg"></i>
                    </a>
                    <a href="https://linkedin.com/in/janedoe" target="_blank" class="social-icon">
                        <i class="fab fa-linkedin-in text-lg"></i>
                    </a>
                    <a href="https://instagram.com/janedoe" target="_blank" class="social-icon">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                </div>
            </div>
            <div class="team-card bg-white p-6 rounded-lg shadow-md text-center animate-card"
                style="animation-delay: 0.4s;">
                <img src="public/teams/team2.jpg" alt="John Smith" class="w-24 h-24 rounded-full mx-auto mb-4"
                    onerror="this.src='public/uploads/default.png'">
                <h3 class="text-xl font-semibold text-[#092468]">John Smith</h3>
                <p class="text-gray-600 mb-3">Chief Operations Officer</p>
                <div class="flex justify-center space-x-4">
                    <a href="https://twitter.com/johnsmith" target="_blank" class="social-icon">
                        <i class="fab fa-twitter text-lg"></i>
                    </a>
                    <a href="https://linkedin.com/in/johnsmith" target="_blank" class="social-icon">
                        <i class="fab fa-linkedin-in text-lg"></i>
                    </a>
                    <a href="https://instagram.com/johnsmith" target="_blank" class="social-icon">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                </div>
            </div>
            <div class="team-card bg-white p-6 rounded-lg shadow-md text-center animate-card"
                style="animation-delay: 0.6s;">
                <img src="public/teams/team3.jpg" alt="Emily Johnson" class="w-24 h-24 rounded-full mx-auto mb-4"
                    onerror="this.src='public/uploads/default.png'">
                <h3 class="text-xl font-semibold text-[#092468]">Emily Johnson</h3>
                <p class="text-gray-600 mb-3">Head of Sales</p>
                <div class="flex justify-center space-x-4">
                    <a href="https://twitter.com/emilyjohnson" target="_blank" class="social-icon">
                        <i class="fab fa-twitter text-lg"></i>
                    </a>
                    <a href="https://linkedin.com/in/emilyjohnson" target="_blank" class="social-icon">
                        <i class="fab fa-linkedin-in text-lg"></i>
                    </a>
                    <a href="https://instagram.com/emilyjohnson" target="_blank" class="social-icon">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Call-to-Action Section -->
    <section class="relative text-white text-center py-16 bg-cover bg-center"
        style="background-image: url('public/images/hero3.jpg');">
        <div class="absolute inset-0 bg-[#092468] bg-opacity-70"></div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold animate-section-title">Join Our Community</h2>
            <p class="text-lg mt-4 max-w-2xl mx-auto animate-card">
                Explore properties, list your own, or connect with us today!
            </p>
            <a href="/pishonserv.com/index.php"
                class="mt-6 inline-block btn-primary text-white px-6 py-3 rounded-lg font-semibold animate-card"
                style="animation-delay: 0.2s;">
                Back to Home
            </a>
        </div>
    </section>


    <?php include 'includes/footer.php'; ?>
</body>

</html>