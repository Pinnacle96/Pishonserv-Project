<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/archive_expired.log');

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$log_prefix = "[" . date('Y-m-d H:i:s') . "] ";

// === 1. ARCHIVE EXPIRED PROPERTIES ===
try {
    $stmt = $conn->prepare("SELECT id, title FROM properties WHERE expiry_date IS NOT NULL AND expiry_date < CURDATE() AND status != 'archived'");
    $stmt->execute();
    $result = $stmt->get_result();

    $archived_count = 0;
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $update = $conn->prepare("UPDATE properties SET status = 'archived' WHERE id = ?");
        $update->bind_param("i", $id);
        $update->execute();
        $update->close();

        error_log($log_prefix . "✅ Archived: [ID {$id}] {$row['title']}");
        $archived_count++;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log($log_prefix . "❌ Archive Error: " . $e->getMessage());
}

// === 2. SEND WARNING EMAILS 3 DAYS BEFORE EXPIRY ===
try {
    $stmt = $conn->prepare("
        SELECT p.id, p.title, p.expiry_date, u.name, u.email
        FROM properties p
        JOIN users u ON p.owner_id = u.id
        WHERE p.expiry_date = CURDATE() + INTERVAL 3 DAY
        AND p.status = 'available'
        AND u.email IS NOT NULL
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $email = $row['email'];
        $property_title = $row['title'];
        $expiry_date = $row['expiry_date'];

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtppro.zoho.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pishonserv@pishonserv.com';
        $mail->Password = 'Serv@4321@Ikeja';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('pishonserv@pishonserv.com', 'PISHONSERV');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = "Your Property Listing Will Expire Soon";

        $logoSrc = "public/images/logo.png"; // use full path

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9;'>
                <div style='text-align: center;'>
                    <img src='{$logoSrc}' alt='PISHONSERV Logo' style='width: 150px; margin-bottom: 20px;'>
                </div>
                <div style='background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h2 style='color: #092468; font-size: 22px;'>Hi {$name},</h2>
                    <p style='font-size: 16px; color: #333;'>Your listing <strong>{$property_title}</strong> is scheduled to expire on <strong>{$expiry_date}</strong>.</p>
                    <p style='font-size: 16px; color: #333;'>If the property is still available, please log in and extend the listing duration.</p>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='127.0.0.1/pishonserv/dashboard/extend_listing.php?id={$row['id']}' style='display: inline-block; background-color: #CC9933; color: white; padding: 12px 24px; border-radius: 5px; font-weight: bold; text-decoration: none;'>Extend Now</a>
                    </div>
                    <p style='font-size: 16px; color: #333;'>If no action is taken, the listing will be archived automatically after the expiry date.</p>
                </div>
                <div style='text-align: center; margin-top: 20px; color: #777; font-size: 14px;'>
                    <p>© " . date('Y') . " PISHONSERV. All rights reserved.</p>
                    <p><a href='https://pishonserv.com' style='color: #092468;'>Visit our website</a> | <a href='mailto:inquiry@pishonserv.com' style='color: #092468;'>Contact Support</a></p>
                </div>
            </div>";

        $mail->AltBody = "Hi {$name}, your property listing '{$property_title}' will expire on {$expiry_date}. Visit https://pishonserv.com to extend.";

        $mail->send();
        error_log($log_prefix . "📧 Email sent to {$email} for property ID {$row['id']}");
    }

    $stmt->close();
} catch (Exception $e) {
    error_log($log_prefix . "❌ Email Sending Error: " . $e->getMessage());
}

echo "✅ Archive and notification process completed.";
