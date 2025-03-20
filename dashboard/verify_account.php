<?php
include '../includes/config.php'; // Ensure PAYSTACK_SECRET_KEY is defined

if (!isset($_GET['account_number'])) {
    echo json_encode(["status" => false, "message" => "No account number provided"]);
    exit();
}

$account_number = $_GET['account_number'];
$bank_code = $_GET['bank_code'] ?? ''; // Bank code must be sent from frontend

if (!$bank_code) {
    echo json_encode(["status" => false, "message" => "Bank code is required"]);
    exit();
}

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

if (isset($paystack_response['status']) && $paystack_response['status'] === true) {
    echo json_encode([
        "status" => true,
        "bank_name" => $_GET['bank_name'],  // Sent from frontend
        "account_name" => $paystack_response['data']['account_name']
    ]);
} else {
    echo json_encode(["status" => false, "message" => "Invalid account details"]);
}
