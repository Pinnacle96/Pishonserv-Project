<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

// Zoho API Credentials
define('ZOHO_CLIENT_ID', '1000.2KD1I4HCI92RHHQRIS75XYH6DACN6F');
define('ZOHO_CLIENT_SECRET', 'a72667839b8925812680e1584ec09a03958786cd28');
define('ZOHO_REDIRECT_URI', 'https://dev.pishonserv.com/dashboard/zoho_callback.php');

// Check if the authorization code is received from Zoho
if (!isset($_GET['code'])) {
    die("Error: No authorization code provided.");
}

$auth_code = $_GET['code'];

// Exchange Authorization Code for Access Token
$token_url = "https://accounts.zoho.com/oauth/v2/token";
$data = [
    'client_id' => ZOHO_CLIENT_ID,
    'client_secret' => ZOHO_CLIENT_SECRET,
    'code' => $auth_code,
    'grant_type' => 'authorization_code',
    'redirect_uri' => ZOHO_REDIRECT_URI
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch); // Capture any cURL errors
curl_close($ch);

// Debugging: Check if Zoho API responded
if ($response === false) {
    die("cURL Error: " . $curl_error);
}

if ($http_code !== 200) {
    die("Error: Zoho API returned HTTP Code $http_code. Response: $response");
}

// Convert JSON response to array
$token_data = json_decode($response, true);

// Debug: Print API response (ONLY for testing)
echo "<pre>";
print_r($token_data);
echo "</pre>";

// Ensure we received tokens
if (!isset($token_data['access_token']) || !isset($token_data['refresh_token'])) {
    die("Error: Missing access_token or refresh_token from Zoho API.");
}

$access_token = $token_data['access_token'];
$refresh_token = $token_data['refresh_token'];
$user_id = $_SESSION['user_id'];

// Store tokens in database
$stmt = $conn->prepare("
    INSERT INTO zoho_tokens (user_id, access_token, refresh_token, created_at) 
    VALUES (?, ?, ?, NOW()) 
    ON DUPLICATE KEY UPDATE access_token = ?, refresh_token = ?, created_at = NOW()
");

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("issss", $user_id, $access_token, $refresh_token, $access_token, $refresh_token);

if ($stmt->execute()) {
    $_SESSION['success'] = "Zoho CRM Integration Successful!";
    header("Location: superadmin_dashboard.php");
    exit();
} else {
    die("Database Insertion Failed: " . $stmt->error);
}
