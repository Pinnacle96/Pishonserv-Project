<?php
session_start();
include '../includes/db_connect.php';

// Debug: Check if session exists
if (!isset($_SESSION['user_id'])) {
    echo "Error: User not logged in. Please log in and try again.";
    exit();
}

// Save user ID in session (for retrieval after Zoho redirect)
$_SESSION['zoho_user_id'] = $_SESSION['user_id'];

// Zoho OAuth URL
$zoho_auth_url = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
    "client_id" => "1000.2KD1I4HCI92RHHQRIS75XYH6DACN6F",
    "response_type" => "code",
    "redirect_uri" => "http://127.0.0.1/pishonserv.com/dashboard/zoho_callback.php",
    "scope" => "ZohoCRM.modules.ALL",
    "access_type" => "offline"
]);

// Debug: Confirm session before redirection
echo "Redirecting to Zoho... User ID: " . $_SESSION['zoho_user_id'];
header("Location: $zoho_auth_url");
exit();
