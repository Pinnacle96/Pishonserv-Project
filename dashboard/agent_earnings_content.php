<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../includes/db_connect.php';
require '../vendor/autoload.php'; // For PHPMailer
// Ensure FPDF is included

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$agent_id = $_SESSION['user_id'];

// Fetch earnings details
$earnings_query = $conn->prepare("SELECT SUM(amount) AS total_earnings FROM transactions WHERE user_id = ? AND type = 'credit' AND status = 'completed'");
$earnings_query->bind_param("i", $agent_id);
$earnings_query->execute();
$earnings_result = $earnings_query->get_result();
$total_earnings = ($earnings_result->fetch_assoc())['total_earnings'] ?? 0;

// Fetch pending balance
$pending_query = $conn->prepare("SELECT SUM(amount) AS pending_balance FROM transactions WHERE user_id = ? AND type = 'credit' AND status = 'pending'");
$pending_query->bind_param("i", $agent_id);
$pending_query->execute();
$pending_result = $pending_query->get_result();
$pending_balance = ($pending_result->fetch_assoc())['pending_balance'] ?? 0;

// Fetch bank details
$bank_query = $conn->prepare("SELECT bank_name, account_number, account_name FROM wallets WHERE user_id = ?");
$bank_query->bind_param("i", $agent_id);
$bank_query->execute();
$bank_result = $bank_query->get_result();
$bank = $bank_result->fetch_assoc();

// Process withdrawal request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['withdraw'])) {
    $withdraw_amount = $_POST['amount'];

    if ($withdraw_amount > $total_earnings) {
        $_SESSION['error'] = "Insufficient balance!";
    } elseif (!$bank) {
        $_SESSION['error'] = "Bank details not found! Update your profile.";
    } else {
        // Insert withdrawal transaction
        $withdraw_query = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status, description) VALUES (?, ?, 'debit', 'completed', 'Agent Withdrawal')");
        $withdraw_query->bind_param("id", $agent_id, $withdraw_amount);
        if ($withdraw_query->execute()) {
            $_SESSION['success'] = "Withdrawal of ₦$withdraw_amount processed successfully!";

            // Send Email Notification
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'your_mailtrap_username'; // Change this
                $mail->Password = 'your_mailtrap_password'; // Change this
                $mail->SMTPSecure = 'tls';
                $mail->Port = 2525;

                $mail->setFrom('no-reply@realestate.com', 'Real Estate Platform');
                $mail->addAddress($_SESSION['email']);
                $mail->isHTML(true);
                $mail->Subject = 'Withdrawal Processed';
                $mail->Body = "<p>Your withdrawal of <strong>₦$withdraw_amount</strong> has been processed successfully.</p>";
                $mail->send();
            } catch (Exception $e) {
                $_SESSION['error'] = "Email notification failed.";
            }
        }
    }
    header("Location: agent_earnings.php");
    exit();
}

if (isset($_GET['export_pdf'])) {
    // Clean previous output
    ob_end_clean();
    
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(190, 10, 'Transaction History', 0, 1, 'C');
            $this->Ln(10);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    $transactions_query = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
    $transactions_query->bind_param("i", $agent_id);
    $transactions_query->execute();
    $transactions_result = $transactions_query->get_result();

    while ($row = $transactions_result->fetch_assoc()) {
        $pdf->Cell(190, 10, "Amount: ₦" . number_format($row['amount'], 2) . " | Type: " . ucfirst($row['type']) . " | Status: " . ucfirst($row['status']), 0, 1);
    }

    // Ensure no output is sent before generating the PDF
    ob_start();
    $pdf->Output();
    ob_end_flush();
    exit();
}

?>


<div class="p-6">
    <h2 class="text-2xl font-bold text-[#092468]">My Earnings</h2>

    <!-- Display Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
    <script>
    Swal.fire("Success!", "<?php echo $_SESSION['success']; ?>", "success");
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <script>
    Swal.fire("Error!", "<?php echo $_SESSION['error']; ?>", "error");
    </script>
    <?php unset($_SESSION['error']); endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-gray-200 p-6 rounded shadow-md">
            <h3 class="text-gray-600">Total Earnings</h3>
            <p class="text-2xl font-bold">₦<?php echo number_format($total_earnings, 2); ?></p>
        </div>
        <div class="bg-gray-200 p-6 rounded shadow-md">
            <h3 class="text-gray-600">Pending Balance</h3>
            <p class="text-2xl font-bold">₦<?php echo number_format($pending_balance, 2); ?></p>
        </div>
    </div>

    <form method="POST" class="mt-6">
        <label class="block text-gray-700">Withdraw Amount</label>
        <input type="number" name="amount" min="1000" max="<?php echo $total_earnings; ?>" required
            class="w-full p-3 border rounded mt-2">
        <button type="submit" name="withdraw"
            class="bg-[#F4A124] text-white w-full py-3 rounded mt-4 hover:bg-[#d88b1c]">Withdraw</button>
    </form>

    <!-- <a href="?export_pdf"
        class="block text-center bg-blue-500 text-white w-full py-3 rounded mt-4 hover:bg-blue-600">Export Transactions
        to PDF</a> -->
</div>