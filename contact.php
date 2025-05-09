<?php
// session_start();
// include 'includes/db_connect.php';
include 'includes/navbar.php';

// PHPMailer setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // For Composer installation
// For manual installation, uncomment these instead:
// require 'vendor/PHPMailer/src/Exception.php';
// require 'vendor/PHPMailer/src/PHPMailer.php';
// require 'vendor/PHPMailer/src/SMTP.php';

$message = ''; // For success/error feedback

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $body = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtppro.zoho.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pishonserv@pishonserv.com'; // Your Zoho email
        $mail->Password = 'Serv@4321@Ikeja'; // Your Zoho password
        $mail->SMTPSecure = 'ssl'; // SSL for port 465
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('pishonserv@pishonserv.com', 'PishonServ Contact Form'); // Fixed From address
        $mail->addAddress('pishonserv@pishonserv.com', 'PishonServ Support'); // Recipient
        $mail->addReplyTo($email, $name); // User's email as Reply-To

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "<h3>Contact Form Submission</h3>
                          <p><strong>Name:</strong> $name</p>
                          <p><strong>Email:</strong> $email</p>
                          <p><strong>Message:</strong><br>$body</p>";
        $mail->AltBody = "Name: $name\nEmail: $email\nMessage: $body";

        $mail->send();
        $message = '<p class="text-green-500 text-center">Message sent successfully!</p>';
    } catch (Exception $e) {
        $message = '<p class="text-red-500 text-center">Failed to send message. Error: ' . $mail->ErrorInfo . '</p>';
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - PishonServ Real Estate</title>
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>public/images/favicon.png">
    <style>
        body {
            background: #f5f7fa;
            color: #092468;
        }

        .hero-bg {
            background: linear-gradient(to bottom, rgba(9, 36, 104, 0.8), rgba(9, 36, 104, 0.5)), url('public/images/hero8.jpg');
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

        .form-input {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus {
            border-color: #F4A124;
            box-shadow: 0 0 0 3px rgba(244, 161, 36, 0.2);
        }

        .btn-primary {
            background-color: #F4A124;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #d88b1c;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(244, 161, 36, 0.3);
        }

        .contact-info-item {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(9, 36, 104, 0.1);
        }

        .content-start {
            padding-top: 5rem;
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative w-full min-h-[400px] sm:min-h-[500px] hero-bg content-start overflow-hidden">
        <div class="relative z-10 hero-content text-center text-white px-6 py-40">
            <h1 class="text-3xl sm:text-5xl font-bold animate-hero-title">Contact Us</h1>
            <p class="text-sm sm:text-lg mt-4 max-w-2xl animate-hero-text">
                We’re here to assist you with all your real estate needs. Get in touch today!
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="container mx-auto py-16 px-4">
        <h2 class="text-4xl md:text-5xl font-bold text-[#092468] text-center animate-section-title">Let’s Connect</h2>
        <p class="text-gray-600 text-lg text-center mt-4 mb-12 max-w-3xl mx-auto animate-card">
            Whether you have questions, need support, or want to list a property, our team is ready to help.
        </p>
        <?php if (!empty($message)) echo $message; ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 px-6 md:px-10">
            <!-- Contact Form -->
            <div class="bg-white p-8 rounded-lg shadow-md animate-card" style="animation-delay: 0.2s;">
                <h3 class="text-2xl font-semibold text-[#092468] mb-6">Send Us a Message</h3>
                <form action="" method="POST" class="space-y-6">
                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                        <input type="text" id="name" name="name" required
                            class="form-input w-full p-3 border rounded-lg text-gray-900 focus:outline-none"
                            placeholder="Your Name">
                    </div>
                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                            class="form-input w-full p-3 border rounded-lg text-gray-900 focus:outline-none"
                            placeholder="Your Email">
                    </div>
                    <div>
                        <label for="subject" class="block text-gray-700 font-medium mb-2">Subject</label>
                        <input type="text" id="subject" name="subject" required
                            class="form-input w-full p-3 border rounded-lg text-gray-900 focus:outline-none"
                            placeholder="Subject">
                    </div>
                    <div>
                        <label for="message" class="block text-gray-700 font-medium mb-2">Message</label>
                        <textarea id="message" name="message" required
                            class="form-input w-full p-3 border rounded-lg text-gray-900 focus:outline-none" rows="5"
                            placeholder="Your Message"></textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full text-white px-6 py-3 rounded-lg font-semibold">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="space-y-8 animate-card" style="animation-delay: 0.4s;">
                <h3 class="text-2xl font-semibold text-[#092468] mb-6">Our Contact Details</h3>
                <div class="contact-info-item bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
                    <i class="fas fa-envelope text-[#F4A124] text-2xl"></i>
                    <div>
                        <h4 class="text-lg font-semibold text-[#092468]">Email</h4>
                        <p class="text-gray-600">
                            <a href="mailto:inquiry@pishonserv.com" class="hover:text-[#F4A124] transition">
                                inquiry@pishonserv.com
                            </a>
                        </p>
                    </div>
                </div>
                <div class="contact-info-item bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
                    <i class="fas fa-phone text-[#F4A124] text-2xl"></i>
                    <div>
                        <h4 class="text-lg font-semibold text-[#092468]">Phone</h4>
                        <p class="text-gray-600">+2348111973369</p>
                    </div>
                </div>
                <div class="contact-info-item bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
                    <i class="fas fa-map-marker-alt text-[#F4A124] text-2xl"></i>
                    <div>
                        <h4 class="text-lg font-semibold text-[#092468]">Address</h4>
                        <p class="text-gray-600">Nomadian Tech Hub 3rd Floor 152 Obafemi Awolowo way, opposite Airport
                            hotel near Allen Junction Bus Stop. IKeja</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
</body>

</html>