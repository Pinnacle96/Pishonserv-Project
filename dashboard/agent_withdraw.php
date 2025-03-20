<?php
session_start();
include '../includes/db_connect.php';

$agent_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['withdraw'])) {
    $withdraw_amount = $_POST['amount'];
    $bank_id = $_POST['bank_id'];

    // ✅ Fetch Wallet Balance
    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();
    $wallet = $stmt->get_result()->fetch_assoc();
    $balance = $wallet['balance'];

    // ✅ Fetch Bank Details
    $stmt = $conn->prepare("SELECT paystack_recipient_code FROM wallets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $bank_id, $agent_id);
    $stmt->execute();
    $bank = $stmt->get_result()->fetch_assoc();
    $recipient_code = $bank['paystack_recipient_code'];

    // ✅ Check for Sufficient Balance
    if ($withdraw_amount > $balance) {
        $_SESSION['error'] = "Insufficient balance!";
        header("Location: agent_earnings.php");
        exit();
    }

    // ✅ Step 1: Send Money to Agent's Bank Account via Paystack
    $paystack_url = "https://api.paystack.co/transfer";
    $paystack_data = [
        "source" => "balance",
        "amount" => $withdraw_amount * 100, // Convert to kobo
        "recipient" => $recipient_code,
        "reason" => "Agent Withdrawal"
    ];

    $headers = [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Content-Type: application/json"
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
        $_SESSION['error'] = "Withdrawal failed: " . $paystack_response['message'];
        header("Location: agent_earnings.php");
        exit();
    }

    // ✅ Step 2: Deduct Wallet Balance
    $stmt = $conn->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?");
    $stmt->bind_param("di", $withdraw_amount, $agent_id);
    $stmt->execute();

    $_SESSION['success'] = "Withdrawal of ₦$withdraw_amount processed successfully!";
    header("Location: agent_earnings.php");
    exit();
}
