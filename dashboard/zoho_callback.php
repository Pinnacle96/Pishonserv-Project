<?php
session_start();
include '../includes/db_connect.php';

// Debug: Check if session is still available after Zoho redirects
var_dump($_SESSION); // Show session data for debugging

if (!isset($_SESSION['zoho_user_id'])) {
    die("Error: User not logged in. Please log in and try again.");
}

$user_id = $_SESSION['zoho_user_id']; // Retrieve stored user ID

// Capture the Authorization Code
if (!isset($_GET['code'])) {
    die("Error: No authorization code provided.");
}

$auth_code = $_GET['code'];

// Exchange Authorization Code for Access Token & Refresh Token
$token_url = "https://accounts.zoho.com/oauth/v2/token";
$data = [
    'client_id' => "1000.2KD1I4HCI92RHHQRIS75XYH6DACN6F",
    'client_secret' => "a72667839b8925812680e1584ec09a03958786cd28",
    'code' => $auth_code,
    'grant_type' => 'authorization_code',
    'redirect_uri' => "http://127.0.0.1/pishonserv.com/dashboard/zoho_callback.php"
];

$options = [
    CURLOPT_URL => $token_url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"]
];

$ch = curl_init();
curl_setopt_array($ch, $options);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (isset($token_data['access_token'])) {
    $access_token = $token_data['access_token'];
    $refresh_token = $token_data['refresh_token'];

    // Store tokens in database
    $stmt = $conn->prepare("INSERT INTO zoho_tokens (user_id, access_token, refresh_token) 
                            VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE access_token = ?, refresh_token = ?");
    $stmt->bind_param("issss", $user_id, $access_token, $refresh_token, $access_token, $refresh_token);
    $stmt->execute();

    $_SESSION['success'] = "Zoho CRM Integration Successful!";
    header("Location: superadmin_dashboard.php");
    exit();
} else {
    die("Error: Unable to retrieve access token. " . json_encode($token_data));
}
