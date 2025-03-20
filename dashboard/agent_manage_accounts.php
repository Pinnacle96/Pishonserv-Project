<?php
session_start();
include '../includes/config.php';
include '../includes/db_connect.php';

$agent_id = $_SESSION['user_id'];


if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $stmt = $conn->prepare("DELETE FROM wallets WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Bank account deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete account.";
    }
}


// ✅ Fetch Existing Bank Accounts
$stmt = $conn->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$bank_accounts = $stmt->get_result();

// ✅ Handle Account Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_account'])) {
    $bank_code = $_POST['bank_code'];  // Bank Code from Dropdown
    $account_number = $_POST['account_number'];

    // ✅ Step 1: Verify Bank Account with Paystack
    $paystack_url = "https://api.paystack.co/bank/resolve?account_number={$account_number}&bank_code={$bank_code}";

    $headers = [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paystack_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $paystack_response = json_decode($response, true);

    if (!$paystack_response['status']) {
        $_SESSION['error'] = "Bank verification failed: " . $paystack_response['message'];
        header("Location: agent_manage_accounts.php");
        exit();
    }

    $account_name = $paystack_response['data']['account_name'];

    // ✅ Step 2: Register Account with Paystack for Withdrawals
    $paystack_url = "https://api.paystack.co/transferrecipient";
    $paystack_data = [
        "type" => "nuban",
        "name" => $account_name,
        "account_number" => $account_number,
        "bank_code" => $bank_code,
        "currency" => "NGN"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paystack_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paystack_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $paystack_response = json_decode($response, true);

    if (!$paystack_response['status']) {
        $_SESSION['error'] = "Bank verification failed: " . $paystack_response['message'];
        header("Location: agent_manage_accounts.php");
        exit();
    }

    $recipient_code = $paystack_response['data']['recipient_code'];
    $bank_name = $_POST['bank_name'];  // Bank Name from Hidden Input in Form

    // ✅ Step 3: Store Account in Database
    $stmt = $conn->prepare("INSERT INTO wallets (user_id, bank_name, account_number, account_name, paystack_recipient_code) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $agent_id, $bank_name, $account_number, $account_name, $recipient_code);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Bank account added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add bank account.";
    }

    header("Location: agent_manage_accounts.php");
    exit();
}

// ✅ Fetch List of Banks Dynamically
function fetchBanks()
{
    global $headers;
    $paystack_url = "https://api.paystack.co/bank";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $paystack_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    $paystack_response = json_decode($response, true);
    return $paystack_response['status'] ? $paystack_response['data'] : [];
}

// Load the agent content
$page_content = __DIR__ . "/agent_manage_accounts_content.php";
include 'dashboard_layout.php';
