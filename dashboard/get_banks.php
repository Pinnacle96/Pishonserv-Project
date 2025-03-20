<?php
include '../includes/config.php'; // Ensure PAYSTACK_SECRET_KEY is defined

$paystack_url = "https://api.paystack.co/bank";
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
        "banks" => $paystack_response['data']
    ]);
} else {
    echo json_encode(["status" => false, "message" => "Failed to fetch banks"]);
}
