<?php
include 'db_connect.php';
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
function createZohoBooking($user_id, $property_id, $status, $check_in_date, $check_out_date, $days_booked, $total_amount)
{
    global $conn;

    $access_token = getZohoAccessToken();

    // Fetch user & property details
    $stmt = $conn->prepare("SELECT u.name, u.email, u.phone, p.title, p.price, p.location, p.type
                            FROM bookings b
                            JOIN users u ON b.user_id = u.id
                            JOIN properties p ON b.property_id = p.id
                            WHERE b.user_id = ? AND b.property_id = ?");
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        die("Error: Booking details not found.");
    }

    $zoho_url = "https://www.zohoapis.com/crm/v2/Deals";
    $data = [
        "data" => [[
            "Deal_Name" => $booking['title'],
            "Amount" => $booking['price'],
            "Stage" => ucfirst($status),
            "Type" => ucfirst($booking['type']),
            "Account_Name" => $booking['email'],
            "Closing_Date" => date("Y-m-d", strtotime("+30 days"))
        ]]
    ];

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

    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] == "SUCCESS") {
        return $response_data['data'][0]['details']['id'];
    } else {
        die("Zoho API Error: " . json_encode($response_data));
    }
}

function createZohoPayment($user_id, $property_id, $amount, $transaction_id)
{
    global $conn;

    // ✅ Fetch User & Property Info
    $stmt = $conn->prepare("SELECT u.zoho_contact_id, p.title FROM users u 
                            JOIN properties p ON p.id = ? 
                            WHERE u.id = ?");
    $stmt->bind_param("ii", $property_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data || !$data['zoho_contact_id']) {
        error_log("❌ Error: User does not have a Zoho Contact ID. Sync user first.");
        return false;
    }

    $zoho_contact_id = $data['zoho_contact_id'];
    $property_title = $data['title'];

    // ✅ Zoho API Setup
    $access_token = getZohoAccessToken();
    $url = "https://www.zohoapis.com/crm/v2/Payments";

    $payment_data = [
        "data" => [[
            "Amount" => $amount,
            "Payment_Status" => "Completed",
            "Transaction_ID" => $transaction_id,
            "Contact_Name" => ["id" => $zoho_contact_id], // Link to user in Zoho CRM
            "Property_Title" => $property_title,
            "Description" => "Payment for $property_title - Transaction ID: $transaction_id"
        ]]
    ];

    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ✅ Debugging: Log API Response
    error_log("🔄 Zoho Payment Response: " . $response);

    if (!$response) {
        error_log("❌ Error: No response from Zoho API. HTTP Code: $http_code");
        return false;
    }

    $response_data = json_decode($response, true);
    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] === "SUCCESS") {
        return $response_data['data'][0]['details']['id']; // Return Zoho Payment ID
    } else {
        error_log("❌ Zoho API Error: " . json_encode($response_data));
        return false;
    }
}
function updateZohoBookingStatus($user_id, $property_id, $status)
{
    global $conn;

    // ✅ Get a valid Zoho Access Token
    $access_token = getZohoAccessToken();

    // ✅ Fetch Zoho Contact ID & Zoho Property ID
    $stmt = $conn->prepare("SELECT u.zoho_contact_id, p.zoho_property_id 
                            FROM bookings b
                            JOIN properties p ON b.property_id = p.id
                            JOIN users u ON b.user_id = u.id
                            WHERE b.user_id = ? AND b.property_id = ?");
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data || !$data['zoho_contact_id'] || !$data['zoho_property_id']) {
        error_log("❌ Error: Zoho IDs not found for User: $user_id, Property: $property_id.");
        return false;
    }

    $zoho_contact_id = $data['zoho_contact_id'];
    $zoho_property_id = $data['zoho_property_id'];

    // ✅ Debugging: Log the IDs
    error_log("🔍 Zoho Update - Contact ID: $zoho_contact_id, Property ID: $zoho_property_id");

    // ✅ API URL for Updating Zoho Deals (Bookings)
    $zoho_url = "https://www.zohoapis.com/crm/v2/Deals/$zoho_property_id";

    // ✅ Prepare Update Data
    $update_data = [
        "data" => [[
            "Stage" => ucfirst($status), // `Confirmed`, `Cancelled`, or `Failed`
            "Contact_Name" => ["id" => $zoho_contact_id] // Link the deal to the correct user
        ]]
    ];

    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    // ✅ Send API Request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zoho_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ✅ Debugging: Log API Response
    error_log("🔄 Zoho Booking Update Response: " . $response);

    if (!$response) {
        error_log("❌ Error: No response from Zoho API. HTTP Code: $http_code");
        return false;
    }

    $response_data = json_decode($response, true);

    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] === "SUCCESS") {
        error_log("✅ Zoho Booking Successfully Updated for User: $user_id, Property: $property_id");
        return true; // Update was successful
    } else {
        error_log("❌ Zoho API Error: " . json_encode($response_data));
        return false;
    }
}
