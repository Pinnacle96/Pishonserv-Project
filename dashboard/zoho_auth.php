<?php
session_start();

define('ZOHO_CLIENT_ID', '1000.2KD1I4HCI92RHHQRIS75XYH6DACN6F');
define('ZOHO_CLIENT_SECRET', 'a72667839b8925812680e1584ec09a03958786cd28');
define('ZOHO_REDIRECT_URI', 'http://127.0.0.1/pishonserv.com/dashboard/zoho_callback.php');

$auth_url = "https://accounts.zoho.com/oauth/v2/auth?" . http_build_query([
    "response_type" => "code",
    "client_id" => ZOHO_CLIENT_ID,
    "scope" => "ZohoCRM.modules.ALL",
    "redirect_uri" => ZOHO_REDIRECT_URI,
    "access_type" => "offline"
]);

header("Location: $auth_url");
exit();
