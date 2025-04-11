<?php
include 'db_connect.php';
include 'zoho_config.php';

function getZohoAccessToken()
{
    global $conn;
    $stmt = $conn->prepare("SELECT access_token, refresh_token FROM zoho_tokens ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $token_data = $result->fetch_assoc();

    if (!$token_data) {
        die("Error: No Zoho Access Token Found.");
    }

    $access_token = $token_data['access_token'];
    $refresh_token = $token_data['refresh_token'];

    $test_url = "https://www.zohoapis.com/crm/v2/Leads?per_page=1";
    $headers = ["Authorization: Zoho-oauthtoken $access_token"];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $test_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        return $access_token;
    }

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

    $new_access_token = $new_token_data['access_token'];
    $stmt = $conn->prepare("UPDATE zoho_tokens SET access_token = ? WHERE refresh_token = ?");
    $stmt->bind_param("ss", $new_access_token, $refresh_token);
    $stmt->execute();

    return $new_access_token;
}

function createZohoLead($name, $lname, $email, $phone, $role)
{
    global $conn;

    $access_token = getZohoAccessToken();
    $zoho_url = "https://www.zohoapis.com/crm/v2/Leads";

    $lead_data = [
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($lead_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response) {
        die("Error: No response from Zoho API. HTTP Code: $http_code");
    }

    $response_data = json_decode($response, true);

    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] === "SUCCESS") {
        $zoho_lead_id = $response_data['data'][0]['details']['id'];

        // Update the user's record with the Zoho Lead ID
        $stmt = $conn->prepare("UPDATE users SET zoho_lead_id = ? WHERE email = ?");
        $stmt->bind_param("ss", $zoho_lead_id, $email);
        $stmt->execute();

        return $zoho_lead_id;
    } else {
        die("Zoho API Error: " . json_encode($response_data));
    }
}

function createZohoProperty($title, $price, $location, $listing_type, $status, $type, $zoho_lead_id, $user_id, $property_id)
{
    global $conn;

    $access_token = getZohoAccessToken();

    // Map listing_type to Offer_Type
    $offerTypeMap = [
        'for_sale' => 'Sale',
        'for_rent' => 'Rent',
        'short_let' => 'Short Let',
        'hotel' => 'Hotel'
    ];
    $offerType = $offerTypeMap[$listing_type] ?? 'Sale';

    // Step 1: Create in Products
    $product_url = "https://www.zohoapis.com/crm/v2/Products";
    $product_data = [
        "data" => [[
            "Property_Name" => $title,
            "Unit_Price" => $price,
            "Location" => $location,
            "Offer_Type" => $offerType,
            "Availability_Status" => ucfirst($status),
            "Vendor_Contact_Name" => ["id" => $zoho_lead_id],
            "Property_Active" => true,
            "Listing_Date" => date("Y-m-d")
        ]]
    ];

    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $product_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($product_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $product_response = curl_exec($ch);
    curl_close($ch);

    $product_result = json_decode($product_response, true);
    if (!isset($product_result['data'][0]['details']['id'])) {
        die("❌ Zoho Product creation failed: " . $product_response);
    }

    $zoho_product_id = $product_result['data'][0]['details']['id'];

    // Step 2: Create a Deal
    $deal_url = "https://www.zohoapis.com/crm/v2/Deals";
    $deal_data = [
        "data" => [[
            "Deal_Name" => $title,
            "Amount" => $price,
            "Stage" => ucfirst($status),
            "Type" => ucfirst($type),
            "Contact_Name" => ["id" => $zoho_lead_id],
            "Closing_Date" => date("Y-m-d", strtotime("+30 days"))
        ]]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $deal_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($deal_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $deal_response = curl_exec($ch);
    curl_close($ch);

    $deal_result = json_decode($deal_response, true);
    if (!isset($deal_result['data'][0]['details']['id'])) {
        die("❌ Zoho Deal creation failed: " . $deal_response);
    }

    $zoho_deal_id = $deal_result['data'][0]['details']['id'];

    // Step 3: Update the property in DB with both IDs
    $stmt = $conn->prepare("UPDATE properties SET zoho_product_id = ?, zoho_property_id = ? WHERE id = ?");
    $stmt->bind_param("ssi", $zoho_product_id, $zoho_deal_id, $property_id);
    $stmt->execute();

    return true;
}

function createZohoBooking($user_id, $property_id, $status, $check_in_date, $check_out_date, $days_booked, $total_amount)
{
    global $conn;

    $access_token = getZohoAccessToken();

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

    // Fetch User & Property Info
    $stmt = $conn->prepare("SELECT u.zoho_lead_id, p.title FROM users u 
                            JOIN properties p ON p.id = ? 
                            WHERE u.id = ?");
    $stmt->bind_param("ii", $property_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data || !$data['zoho_lead_id']) {
        error_log("❌ Error: User does not have a Zoho Lead ID. Sync user first.");
        return false;
    }

    $zoho_lead_id = $data['zoho_lead_id'];
    $property_title = $data['title'];

    $access_token = getZohoAccessToken();
    $url = "https://www.zohoapis.com/crm/v2/Payments";

    $payment_data = [
        "data" => [[
            "Amount" => $amount,
            "Payment_Status" => "Completed",
            "Transaction_ID" => $transaction_id,
            "Vendor_Contact_Name" => ["id" => $zoho_lead_id],
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

    error_log("🔄 Zoho Payment Response: " . $response);

    if (!$response) {
        error_log("❌ Error: No response from Zoho API. HTTP Code: $http_code");
        return false;
    }

    $response_data = json_decode($response, true);
    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] === "SUCCESS") {
        return $response_data['data'][0]['details']['id'];
    } else {
        error_log("❌ Zoho API Error: " . json_encode($response_data));
        return false;
    }
}

function updateZohoBookingStatus($user_id, $property_id, $status)
{
    global $conn;

    $access_token = getZohoAccessToken();

    $stmt = $conn->prepare("SELECT u.zoho_lead_id, p.zoho_property_id 
                            FROM bookings b
                            JOIN properties p ON b.property_id = p.id
                            JOIN users u ON b.user_id = u.id
                            WHERE b.user_id = ? AND b.property_id = ?");
    $stmt->bind_param("ii", $user_id, $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data || !$data['zoho_lead_id'] || !$data['zoho_property_id']) {
        error_log("❌ Error: Zoho IDs not found for User: $user_id, Property: $property_id.");
        return false;
    }

    $zoho_lead_id = $data['zoho_lead_id'];
    $zoho_property_id = $data['zoho_property_id'];

    $zoho_url = "https://www.zohoapis.com/crm/v2/Deals/$zoho_property_id";

    $update_data = [
        "data" => [[
            "Stage" => ucfirst($status),
            "Contact_Name" => ["id" => $zoho_lead_id]
        ]]
    ];

    $headers = [
        "Authorization: Zoho-oauthtoken " . $access_token,
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zoho_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    error_log("🔄 Zoho Booking Update Response: " . $response);

    if (!$response) {
        error_log("❌ Error: No response from Zoho API. HTTP Code: $http_code");
        return false;
    }

    $response_data = json_decode($response, true);

    if (isset($response_data['data'][0]['code']) && $response_data['data'][0]['code'] === "SUCCESS") {
        error_log("✅ Zoho Booking Successfully Updated for User: $user_id, Property: $property_id");
        return true;
    } else {
        error_log("❌ Zoho API Error: " . json_encode($response_data));
        return false;
    }
}
