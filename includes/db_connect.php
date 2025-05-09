<?php
// ✅ Secure session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.use_strict_mode', 1);
    session_start();

    // Regenerate once per session
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// ✅ Database connection live server
// $host = "localhost";
// $username = "u561302917_Pishonserv";
// $password = "Pishonserv@255";
// $database = "u561302917_Pishonserv";

$host = "localhost";
$username = "root";
$password = "";
$database = "u561302917_Pishonserv";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('csrf_token_input')) {
    function csrf_token_input()
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
}

// ✅ Site Status Gatekeeper
$settings_query = $conn->query("SELECT site_status FROM system_settings LIMIT 1");
$settings = $settings_query->fetch_assoc() ?? ['site_status' => 'active'];
$site_status = $settings['site_status'];

$current_page = basename($_SERVER['PHP_SELF']);
$is_logging_in = in_array($current_page, ['login.php', 'login_process.php']);

if (!$is_logging_in && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin')) {
    if ($site_status === 'maintenance') {
        header("Location: /pishonserv.com/maintenance.php");
        exit();
    }

    if ($site_status === 'inactive') {
        header("Location: /pishonserv.com/site_closed.php");
        exit();
    }
}
