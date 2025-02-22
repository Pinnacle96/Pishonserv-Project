<?php
session_start();
include '../includes/db_connect.php';
include '../config/env.php'; // Load API keys

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if agent is already verified
$stmt = $conn->prepare("SELECT is_verified FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['is_verified']) {
    header("Location: agent_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $country = $_POST['country'];
    $id_number = $_POST['id_number'];
    $id_type = $_POST['id_type'];

    // Prepare API request
    $payload = json_encode([
        "user_id" => $user_id,
        "first_name" => $first_name,
        "last_name" => $last_name,
        "country" => $country,
        "id_number" => $id_number,
        "id_type" => $id_type,
        "partner_id" => SMILE_ID_PARTNER_ID,
        "api_key" => SMILE_ID_API_KEY
    ]);

    $ch = curl_init(SMILE_ID_ENV . "/v1/kyc/submit");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($result && isset($result['success']) && $result['success']) {
        // Update agent as verified
        $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
        $updateStmt->bind_param("i", $user_id);
        $updateStmt->execute();

        $_SESSION['success'] = "Verification successful! You can now list properties.";
        header("Location: agent_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Verification failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-[#092468]">Agent Verification</h2>
        <?php if (isset($_SESSION['error'])): ?>
        <script>
        Swal.fire("Error!", "<?php echo $_SESSION['error']; ?>", "error");
        </script>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="" method="POST" class="mt-4">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">First Name</label>
                <input type="text" name="first_name" required class="w-full p-3 border rounded mt-1">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Last Name</label>
                <input type="text" name="last_name" required class="w-full p-3 border rounded mt-1">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">Country</label>
                <select name="country" required class="w-full p-3 border rounded mt-1">
                    <option value="NG">Nigeria</option>
                    <option value="GH">Ghana</option>
                    <option value="KE">Kenya</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">ID Type</label>
                <select name="id_type" required class="w-full p-3 border rounded mt-1">
                    <option value="NIN">NIN</option>
                    <option value="Passport">Passport</option>
                    <option value="Driver's License">Driver's License</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold">ID Number</label>
                <input type="text" name="id_number" required class="w-full p-3 border rounded mt-1">
            </div>
            <button type="submit" class="bg-[#F4A124] text-white w-full py-3 rounded hover:bg-[#d88b1c]">
                Submit Verification
            </button>
        </form>
    </div>
</body>

</html>