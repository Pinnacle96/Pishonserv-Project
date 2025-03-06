<?php
include '../includes/db_connect.php';

// Function to get Zoho access token from database
function getZohoAccessToken()
{
    global $conn;

    // Retrieve the latest access token from the database
    $stmt = $conn->prepare("SELECT access_token FROM zoho_tokens ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $stmt->bind_result($access_token);
    $stmt->fetch();
    $stmt->close();

    return $access_token;
}

// Function to create a new contact in Zoho CRM
function createZohoContact($name, $email, $phone, $role)
{
    $access_token = getZohoAccessToken();
    if (!$access_token) {
        return null; // No Zoho Access Token
    }

    // Zoho API Endpoint
    $url = "https://www.zohoapis.com/crm/v2/Contacts";

    // Prepare Data
    $data = [
        "data" => [[
            "Last_Name" => $name,
            "Email" => $email,
            "Phone" => $phone, // ✅ Added phone number
            "Lead_Source" => "Pishonserv Property Hub",
            "Contact_Type" => ucfirst($role) // Buyer, Agent, Owner, Hotel Owner
        ]]
    ];

    // Send Request
    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    // Parse response
    $response_data = json_decode($response, true);
    if (isset($response_data['data'][0]['id'])) {
        return $response_data['data'][0]['id']; // Return Zoho Contact ID
    } else {
        return null; // Failed
    }
}
