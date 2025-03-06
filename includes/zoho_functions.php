<?php
include '../includes/db_connect.php';
include 'zoho_config.php';

function getZohoAccessToken()
{
    global $conn; // Ensure database connection

    // Retrieve the latest stored Zoho tokens
    $stmt = $conn->prepare("SELECT access_token, refresh_token FROM zoho_tokens ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $token_data = $result->fetch_assoc();

    if (!$token_data) {
        die("Error: No Zoho Access Token Found.");
    }

    $access_token = $token_data['access_token'];
    $refresh_token = $token_data['refresh_token'];

    // Verify if the access token is still valid
    $test_url = "https://www.zohoapis.com/crm/v2/Contacts?per_page=1"; // Small test request
    $headers = ["Authorization: Zoho-oauthtoken $access_token"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $test_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // If token is valid, return it
    if ($http_code === 200) {
        return $access_token;
    }

    // If token is expired, use the refresh token to get a new access token
    $refresh_url = "https://accounts.zoho.com/oauth/v2/token";
    $refresh_data = [
        'client_id' => ZOHO_CLIENT_ID,
        'client_secret' => ZOHO_CLIENT_SECRET,
        'refresh_token' => $refresh_token,
        'grant_type' => 'refresh_token'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $refresh_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($refresh_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
    $response = curl_exec($ch);
    curl_close($ch);

    $new_token_data = json_decode($response, true);

    if (!isset($new_token_data['access_token'])) {
        die("Error: Unable to refresh Zoho Access Token. Response: " . json_encode($new_token_data));
    }

    // Store the new access token in the database
    $new_access_token = $new_token_data['access_token'];
    $stmt = $conn->prepare("UPDATE zoho_tokens SET access_token = ? WHERE refresh_token = ?");
    $stmt->bind_param("ss", $new_access_token, $refresh_token);
    $stmt->execute();

    return $new_access_token;
}

// Function to create a new contact in Zoho CRM
function createZohoContact($name, $lname, $email, $phone, $role)
{
    global $conn;

    // Get a valid Zoho Access Token (refresh if expired)
    $access_token = getZohoAccessToken();

    $zoho_url = "https://www.zohoapis.com/crm/v2/Contacts";
    $contact_data = [
        "data" => [[
            "First_Name" => $name,
            "Last_Name" => $lname,
            "Email" => $email,
            "Phone" => $phone,
            "Lead_Source" => "Pishonserv",
            "Description" => "New user registration - Role: " . ucfirst($role)
        ]]
    ];

    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zoho_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($contact_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response) {
        die("Error: No response from Zoho API. HTTP Code: $http_code");
    }

    $response_data = json_decode($response, true);

    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] == "SUCCESS") {
        return $response_data['data'][0]['details']['id']; // Zoho Contact ID
    } else {
        die("Zoho API Error: " . json_encode($response_data));
    }
}
function createZohoProperty($title, $price, $location, $type, $status, $zoho_contact_id)
{
    $access_token = getZohoAccessToken();
    $zoho_url = "https://www.zohoapis.com/crm/v2/Deals";

    $data = [
        "data" => [[
            "Deal_Name" => $title,
            "Amount" => $price,
            "Stage" => ucfirst($status),
            "Type" => ucfirst($type),
            "Contact_Name" => ["id" => $zoho_contact_id], // FIX: Use Contact_Name instead of Account_Name
            "Closing_Date" => date("Y-m-d", strtotime("+30 days"))
        ]]
    ];

    echo "🔄 Sending Data to Zoho CRM:<br>";
    echo json_encode($data, JSON_PRETTY_PRINT); // DEBUGGING

    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zoho_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response) {
        die("Error: No response from Zoho API. HTTP Code: $http_code");
    }

    $response_data = json_decode($response, true);

    echo "🔄 Zoho Response:<br>";
    echo json_encode($response_data, JSON_PRETTY_PRINT); // DEBUGGING

    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] == "SUCCESS") {
        return $response_data['data'][0]['details']['id']; // Zoho Property ID
    } else {
        die("Zoho API Error: " . json_encode($response_data));
    }
}
