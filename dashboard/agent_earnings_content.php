<?php
//session_start();
include '../includes/db_connect.php';
require '../vendor/autoload.php'; // PHPMailer for notifications

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$agent_id = $_SESSION['user_id'];

// ✅ Check if wallet exists for the agent
$stmt = $conn->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$wallet = $stmt->get_result()->fetch_assoc();

// ✅ If wallet does NOT exist, create an empty one
if (!$wallet) {
    $stmt = $conn->prepare("INSERT INTO wallets (user_id, balance, bank_name, account_number, account_name) VALUES (?, 0.00, '', '', '')");
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();

    // Fetch wallet again after creation
    $stmt = $conn->prepare("SELECT * FROM wallets WHERE user_id = ?");
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();
}

// ✅ Ensure balance & bank details are available
$wallet_balance = $wallet['balance'] ?? 0;
$bank_name = $wallet['bank_name'] ?? '';
$account_number = $wallet['account_number'] ?? '';
$account_name = $wallet['account_name'] ?? '';

// ✅ Fetch earnings history
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$transactions = $stmt->get_result();
?>

<div class="p-6">
    <h2 class="text-2xl font-bold text-[#092468]">My Earnings</h2>

    <!-- ✅ Display Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire("Success!", "<?php echo $_SESSION['success']; ?>", "success");
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            Swal.fire("Error!", "<?php echo $_SESSION['error']; ?>", "error");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-gray-200 p-6 rounded shadow-md">
            <h3 class="text-gray-600">Wallet Balance</h3>
            <p class="text-2xl font-bold">₦<?php echo number_format($wallet_balance, 2); ?></p>
        </div>
    </div>

    <!-- ✅ Manage Bank Accounts -->
    <a href="agent_manage_accounts.php"
        class="mt-6 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
        Manage Bank Accounts
    </a>

    <!-- ✅ Withdraw Form -->
    <?php if ($wallet_balance > 0): ?>
        <form method="POST" action="agent_withdraw.php" class="mt-6 bg-white p-6 rounded shadow-md">
            <h3 class="text-lg font-bold text-gray-700">Withdraw Funds</h3>

            <!-- ✅ Fetch Available Bank Accounts -->
            <?php
            $bank_stmt = $conn->prepare("SELECT id, bank_name, account_number FROM wallets WHERE user_id = ?");
            $bank_stmt->bind_param("i", $agent_id);
            $bank_stmt->execute();
            $bank_accounts = $bank_stmt->get_result();
            ?>

            <label class="block text-gray-700">Select Bank Account</label>
            <select name="bank_id" required class="w-full p-3 border rounded mt-2">
                <?php while ($bank = $bank_accounts->fetch_assoc()): ?>
                    <option value="<?php echo $bank['id']; ?>">
                        <?php echo $bank['bank_name'] . " - " . $bank['account_number']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label class="block text-gray-700 mt-4">Withdraw Amount</label>
            <input type="number" name="amount" min="1000" max="<?php echo $wallet_balance; ?>" required
                class="w-full p-3 border rounded mt-2">

            <button type="submit" name="withdraw"
                class="bg-[#F4A124] text-white w-full py-3 rounded mt-4 hover:bg-[#d88b1c]">
                Withdraw
            </button>
        </form>
    <?php else: ?>
        <p class="mt-6 text-gray-500">You have no funds available for withdrawal.</p>
    <?php endif; ?>
</div>