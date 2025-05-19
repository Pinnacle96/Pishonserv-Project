<?php
session_start();
include 'includes/db_connect.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - PishonServ Real Estate</title>
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            background: #f5f7fa;
            color: #092468;
        }

        /* Hero Section */
        .hero-bg {
            background: linear-gradient(to bottom, rgba(9, 36, 104, 0.8), rgba(9, 36, 104, 0.5)), url('public/images/hero6.jpg');
            background-size: cover;
            background-position: center;
        }

        .hero-content {
            min-height: 100%;
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
        .job-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .job-card:hover {
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

        /* Navbar Spacing */
        .content-start {
            padding-top: 5rem;
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative w-full min-h-[400px] sm:min-h-[500px] hero-bg content-start overflow-hidden">
        <div class="relative z-10 hero-content text-center text-white px-6 py-40">
            <h1 class="text-3xl sm:text-5xl font-bold animate-hero-title">Careers at PishonServ</h1>
            <p class="text-sm sm:text-lg mt-4 max-w-2xl animate-hero-text">
                Join our team and help connect people to their dream properties with innovation and excellence.
            </p>
        </div>
    </section>


    <!-- Job Openings Section -->
    <section class="container mx-auto py-16 px-4">
        <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center animate-section-title">Current Job Openings
        </h2>
        <p class="text-gray-600 text-lg text-center mt-4 mb-12 max-w-3xl mx-auto animate-card">
            Explore exciting career opportunities at <strong>PishonServ</strong>. We are looking for passionate
            individuals to join our mission of revolutionizing real estate.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 px-6 md:px-10">
            <div class="job-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.2s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Real Estate Sales Agent</h3>
                <p class="text-gray-600 mb-4">
                    <strong>Location:</strong> Lagos, Nigeria<br>
                    <strong>Type:</strong> Full-Time<br>
                    <strong>Description:</strong> We are seeking a dynamic Real Estate Sales Agent to join our team. You
                    will work closely with clients to help them buy, sell, or rent properties, providing expert guidance
                    and exceptional service.
                </p>
                <a href="mailto:careers@pishonserv.com?subject=Application%20for%20Real%20Estate%20Sales%20Agent"
                    class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Apply Now</a>
            </div>
            <div class="job-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.4s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Marketing Coordinator</h3>
                <p class="text-gray-600 mb-4">
                    <strong>Location:</strong> Lagos, Nigeria<br>
                    <strong>Type:</strong> Full-Time<br>
                    <strong>Description:</strong> Join our marketing team to create and execute campaigns that promote
                    our properties and brand. Ideal candidates are creative, organized, and experienced in digital
                    marketing.
                </p>
                <a href="mailto:careers@pishonserv.com?subject=Application%20for%20Marketing%20Coordinator"
                    class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Apply Now</a>
            </div>
            <div class="job-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.4s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Marketing Coordinator</h3>
                <p class="text-gray-600 mb-4">
                    <strong>Location:</strong> Lagos, Nigeria<br>
                    <strong>Type:</strong> Full-Time<br>
                    <strong>Description:</strong> Join our marketing team to create and execute campaigns that promote
                    our properties and brand. Ideal candidates are creative, organized, and experienced in digital
                    marketing.
                </p>
                <a href="mailto:careers@pishonserv.com?subject=Application%20for%20Marketing%20Coordinator"
                    class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Apply Now</a>
            </div>
            <div class="job-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.4s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Marketing Coordinator</h3>
                <p class="text-gray-600 mb-4">
                    <strong>Location:</strong> Lagos, Nigeria<br>
                    <strong>Type:</strong> Full-Time<br>
                    <strong>Description:</strong> Join our marketing team to create and execute campaigns that promote
                    our properties and brand. Ideal candidates are creative, organized, and experienced in digital
                    marketing.
                </p>
                <a href="mailto:careers@pishonserv.com?subject=Application%20for%20Marketing%20Coordinator"
                    class="btn-primary text-white px-4 py-2 rounded-lg font-semibold">Apply Now</a>
            </div>
        </div>
    </section>
    <!-- Why Work With Us Section -->
    <section class="container mx-auto py-16 px-4 bg-gray-100">
        <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center animate-section-title">Why Work With Us
        </h2>
        <p class="text-gray-600 text-lg text-center mt-4 mb-12 max-w-3xl mx-auto animate-card">
            At <strong>PishonServ</strong>, we foster a culture of growth, collaboration, and innovation. Join us to
            make a meaningful impact in the real estate industry.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 px-6 md:px-10">
            <div class="job-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.2s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Growth Opportunities</h3>
                <p class="text-gray-600">
                    We invest in your professional development with training programs and career advancement
                    opportunities to help you reach your full potential.
                </p>
            </div>
            <div class="job-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.4s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Collaborative Environment</h3>
                <p class="text-gray-600">
                    Work alongside a passionate team in a supportive and inclusive workplace where your ideas are valued
                    and collaboration drives success.
                </p>
            </div>
            <div class="job-card bg-white p-6 rounded-lg shadow-md animate-card" style="animation-delay: 0.6s;">
                <h3 class="text-xl font-semibold text-[#092468] mb-2">Impactful Work</h3>
                <p class="text-gray-600">
                    Be part of a mission to transform real estate, helping clients find their dream properties and
                    making a lasting difference in communities.
                </p>
            </div>
        </div>
    </section>


    <!-- Call-to-Action Section -->
    <section class="relative text-white text-center py-16 bg-cover bg-center"
        style="background-image: url('public/images/hero3.jpg');">
        <div class="absolute inset-0 bg-[#092468] bg-opacity-70"></div>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold animate-section-title">Ready to Make a Difference?</h2>
            <p class="text-lg mt-4 max-w-2xl mx-auto animate-card">
                Apply today and become part of our mission to transform real estate.
            </p>
            <a href="mailto:careers@pishonserv.com?subject=General%20Career%20Inquiry"
                class="mt-6 inline-block btn-primary text-white px-6 py-3 rounded-lg font-semibold animate-card"
                style="animation-delay: 0.2s;">
                Contact Us
            </a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Page-Specific JavaScript Error Handling -->
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure Zoho SalesIQ loads
            if (!window.$zoho || !window.$zoho.salesiq) {
                console.warn('Zoho SalesIQ not initialized. Loading fallback...');
                window.$zoho = window.$zoho || {};
                window.$zoho.salesiq = window.$zoho.salesiq || { ready: function() {} };
                var zohoScript = document.createElement('script');
                zohoScript.id = 'zsiqscript';
                zohoScript.src = 'https://salesiq.zohopublic.com/widget?wc=siqbf4b21531e2ec082c78d765292863df4a9787c4f0ba205509de7585b7a8d3e78';
                zohoScript.async = true;
                document.body.appendChild(zohoScript);
            }

            // Timeout to check if Zoho loaded
            setTimeout(function() {
                if (!document.querySelector('.zsiq_floatmain')) {
                    console.error('Zoho SalesIQ widget failed to load on Careers page.');
                }
            }, 5000);
        });
    </script> -->
</body>

</html>