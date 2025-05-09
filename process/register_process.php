<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

define('BASE_PATH', realpath(__DIR__ . '/../'));

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

try {
    include '../includes/db_connect.php';
    include '../includes/zoho_functions.php';
    require '../vendor/autoload.php';

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method.");
    }

    $_SESSION['form_data'] = [
        'name' => trim($_POST['name'] ?? ''),
        'lname' => trim($_POST['lname'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'nin' => trim($_POST['nin'] ?? ''),
        'role' => $_POST['role'] ?? '',
        'agree_mou' => isset($_POST['agree_mou'])
    ];

    $name = trim($_POST['name'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $nin = trim($_POST['nin'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $valid_roles = ['buyer', 'agent', 'owner', 'hotel_owner', 'developer', 'admin', 'superadmin'];
if (!in_array($role, $valid_roles)) {
    throw new Exception("Invalid role selected.");
}

    $otp = rand(100000, 999999);
    $otp_expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    $image_name = "default.png";
    $mou_file = null;
    $mou_path = null;

    if (empty($name) || empty($lname) || empty($email) || empty($phone) || empty($address) || empty($state) || empty($city) || empty($nin) || empty($password) || empty($role)) {
        throw new Exception("All required fields must be filled.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    if (!preg_match('/^[0-9]{11}$/', $nin)) {
        throw new Exception("NIN must be exactly 11 digits.");
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Database execute error: " . $stmt->error);
    }
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already registered.");
    }
    $stmt->close();

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            throw new Exception("Profile image must be JPG, JPEG, or PNG.");
        }
        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            throw new Exception("Profile image must be less than 2MB.");
        }
        $image_name = uniqid() . "." . $ext;
        $upload_dir = BASE_PATH . '/public/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $upload_path = $upload_dir . $image_name;
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            throw new Exception("Failed to upload profile image.");
        }
    }

    $logoPath = realpath(__DIR__ . '/../public/images/logo.png');
    $logoSrc = $logoPath && file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : '../public/images/logo.png';

    if (in_array($role, ['agent', 'owner', 'hotel_owner', 'developer'])) {
        if (!isset($_POST['agree_mou']) || empty(trim($_POST['signed_name'] ?? ''))) {
            throw new Exception("You must agree to the MOU and provide a signed name.");
        }

        $signed_name = trim($_POST['signed_name']);
        $mou_file = "mou_" . time() . ".pdf";
        $mou_dir = BASE_PATH . '/documents/mou/';
        $mou_path = $mou_dir . $mou_file;

        if (!is_dir($mou_dir)) {
            mkdir($mou_dir, 0755, true);
        }

        ob_start();
        include '../includes/mou_template.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        file_put_contents($mou_path, $dompdf->output());
    }

    $password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, lname, email, phone, address, state, city, nin, password, role, otp, otp_expires_at, profile_image, mou_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    $stmt->bind_param("ssssssssssssss", $name, $lname, $email, $phone, $address, $state, $city, $nin, $password, $role, $otp, $otp_expires_at, $image_name, $mou_file);
    if (!$stmt->execute()) {
        throw new Exception("Database insert error: " . $stmt->error);
    }
    $user_id = $stmt->insert_id;
    $stmt->close();

    try {
        $zoho_lead_id = createZohoLead($name, $lname, $email, $phone, $role);
        if ($zoho_lead_id) {
            $stmt = $conn->prepare("UPDATE users SET zoho_lead_id = ? WHERE id = ?");
            $stmt->bind_param("si", $zoho_lead_id, $user_id);
            $stmt->execute();
            $stmt->close();

            // Auto convert to contact after lead creation
            $zoho_contact_id = convertZohoLeadToContact($zoho_lead_id, $email);
            if ($zoho_contact_id) {
                $stmt = $conn->prepare("UPDATE users SET zoho_contact_id = ? WHERE id = ?");
                $stmt->bind_param("si", $zoho_contact_id, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        error_log("Zoho sync failed: " . $e->getMessage());
    }

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtppro.zoho.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pishonserv@pishonserv.com';
    $mail->Password = 'Serv@4321@Ikeja';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');

    // Send OTP email (to user only)
    try {
        $mail->clearAllRecipients();
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'PISHONSERV - Verify Your Email Address';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
                <div style='text-align: center;'>
                    <img src='{$logoSrc}' alt='PISHONSERV Logo' style='width: 150px; margin-bottom: 20px;'>
                </div>
                <div style='background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h2 style='color: #092468; font-size: 24px; margin-bottom: 20px;'>Welcome to PISHONSERV, {$name}!</h2>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        Thank you for joining our platform. To complete your registration, please verify your email address using the One-Time Password (OTP) below:
                    </p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <span style='display: inline-block; background-color: #CC9933; color: #ffffff; font-size: 28px; font-weight: bold; padding: 10px 20px; border-radius: 5px;'>{$otp}</span>
                    </div>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        This OTP is valid for <strong>10 minutes</strong>. Please enter it on the verification page to activate your account.
                    </p>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        If you did not initiate this registration, please disregard this email or contact our support team at <a href='mailto:support@pishonserv.com' style='color: #092468;'>support@pishonserv.com</a>.
                    </p>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #777; font-size: 14px;'>
                    <p>© " . date('Y') . " PISHONSERV. All rights reserved.</p>
                    <p><a href='https://pishonserv.com' style='color: #092468;'>Visit our website</a> | <a href='mailto:support@pishonserv.com' style='color: #092468;'>Contact Support</a></p>
                </div>
            </div>";
        $mail->AltBody = "Hello {$name},\n\nThank you for registering with PISHONSERV. Your OTP is: {$otp}\n\nThis OTP is valid for 10 minutes. Please enter it on the verification page to activate your account.\n\nIf you did not initiate this registration, please contact support@pishonserv.com.\n\nRegards,\nPISHONSERV Team";

        $mail->send();
        error_log("OTP email sent successfully to {$email}");
    } catch (Exception $e) {
        error_log("OTP email error: " . $e->getMessage());
        throw new Exception("Unable to send OTP email: " . $e->getMessage());
    }

    // Send MOU emails (separate for user and superadmin)
    if ($mou_file && file_exists($mou_path)) {
        // User MOU email
        try {
            // Create new PHPMailer instance to avoid SMTP reuse issues
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtppro.zoho.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pishonserv@pishonserv.com';
            $mail->Password = 'Serv@4321@Ikeja';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid user email address: {$email}");
            }

            $mail->addAddress($email, $name);
            $mail->addAttachment($mou_path, $mou_file);

            $mail->isHTML(true);
            $mail->Subject = 'PISHONSERV - Your Memorandum of Understanding (MOU)';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
                <div style='text-align: center;'>
                    <img src='{$logoSrc}' alt='PISHONSERV Logo' style='width: 150px; margin-bottom: 20px;'>
                </div>
                <div style='background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h2 style='color: #092468; font-size: 24px; margin-bottom: 20px;'>Dear {$name},</h2>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        Congratulations on registering as a <strong>" . ucfirst($role) . "</strong> with PISHONSERV!
                    </p>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        Attached to this email is your signed Memorandum of Understanding (MOU), outlining your responsibilities to maintain integrity, transparency, and accuracy on our platform.
                    </p>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        Please review the document carefully and keep it for your records. You can also download it from your account dashboard at any time.
                    </p>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        If you have any questions, please contact our support team at <a href='mailto:support@pishonserv.com' style='color: #092468;'>support@pishonserv.com</a>.
                    </p>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #777; font-size: 14px;'>
                    <p>© " . date('Y') . " PISHONSERV. All rights reserved.</p>
                    <p><a href='https://pishonserv.com' style='color: #092468;'>Visit our website</a> | <a href='mailto:support@pishonserv.com' style='color: #092468;'>Contact Support</a></p>
                </div>
            </div>";
            $mail->AltBody = "Dear {$name},\n\nCongratulations on registering as a " . ucfirst($role) . " with PISHONSERV!\n\nAttached is your signed Memorandum of Understanding (MOU). Please review it carefully and keep it for your records. You can also download it from your account dashboard.\n\nFor questions, contact support@pishonserv.com.\n\nRegards,\nPISHONSERV Team";

            error_log("Attempting to send MOU email to user: {$email}");
            $mail->send();
            error_log("MOU email sent successfully to {$email}");
        } catch (Exception $e) {
            error_log("User MOU email error for {$email}: " . $e->getMessage());
            // Notify superadmin of failure
            try {
                $failMail = new PHPMailer(true);
                $failMail->isSMTP();
                $failMail->Host = 'smtppro.zoho.com';
                $failMail->SMTPAuth = true;
                $failMail->Username = 'pishonserv@pishonserv.com';
                $failMail->Password = 'Serv@4321@Ikeja';
                $failMail->SMTPSecure = 'ssl';
                $failMail->Port = 465;
                $failMail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');
                $failMail->addAddress('pishonserv@pishonserv.com', 'Super Admin');
                $failMail->isHTML(true);
                $failMail->Subject = 'PISHONSERV - Failed to Send User MOU Email';
                $failMail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #092468;'>MOU Email Delivery Failure</h2>
                    <p>Failed to send MOU email to user:</p>
                    <p><strong>Name:</strong> {$name} {$lname}<br>
                       <strong>Email:</strong> {$email}<br>
                       <strong>Role:</strong> " . ucfirst($role) . "</p>
                    <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                    <p>Please investigate and ensure the user receives the MOU.</p>
                </div>";
                $failMail->send();
                error_log("Notified superadmin of user MOU email failure for {$email}");
            } catch (Exception $failEx) {
                error_log("Failed to notify superadmin of user MOU email failure: " . $failEx->getMessage());
            }
        }

        // Superadmin MOU notification
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtppro.zoho.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pishonserv@pishonserv.com';
            $mail->Password = 'Serv@4321@Ikeja';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');

            $mail->addAddress('pishonserv@pishonserv.com', 'Super Admin');
            $mail->addAttachment($mou_path, $mou_file);

            $mail->isHTML(true);
            $mail->Subject = 'PISHONSERV - New MOU Submission';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
                <div style='text-align: center;'>
                    <img src='{$logoSrc}' alt='PISHONSERV Logo' style='width: 150px; margin-bottom: 20px;'>
                </div>
                <div style='background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h2 style='color: #092468; font-size: 24px; margin-bottom: 20px;'>New MOU Submission</h2>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        A new user has registered and submitted an MOU:
                    </p>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        <strong>Name:</strong> {$name} {$lname}<br>
                        <strong>Email:</strong> {$email}<br>
                        <strong>Role:</strong> " . ucfirst($role) . "<br>
                        <strong>Submission Date:</strong> " . date('F d, Y H:i:s') . "
                    </p>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        The signed MOU document is attached for your review.
                    </p>
                    <p style='color: #333; line-height: 1.6; font-size: 16px;'>
                        Please verify the details and take appropriate action as needed.
                    </p>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #777; font-size: 14px;'>
                    <p>© " . date('Y') . " PISHONSERV. All rights reserved.</p>
                </div>
            </div>";
            $mail->AltBody = "New MOU Submission\n\nName: {$name} {$lname}\nEmail: {$email}\nRole: " . ucfirst($role) . "\nSubmission Date: " . date('F d, Y H:i:s') . "\n\nThe signed MOU document is attached for review.\n\nPlease verify the details and take appropriate action.";

            error_log("Attempting to send MOU notification to superadmin");
            $mail->send();
            error_log("MOU notification sent successfully to superadmin");
        } catch (Exception $e) {
            error_log("Superadmin MOU email error: " . $e->getMessage());
        }
    }
    // Set session data
    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['profile_image'] = $image_name;

    unset($_SESSION['form_data']);
    $_SESSION['success'] = "Registration successful! Check your email for OTP verification." . ($mou_file ? " Your MOU has been sent to your email." : "");
    header("Location: ../auth/verify-otp.php");
    exit();
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header("Location: ../auth/register.php");
    exit();
}
?>