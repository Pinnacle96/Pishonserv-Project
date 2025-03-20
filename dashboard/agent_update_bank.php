<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];

    $stmt = $conn->prepare("UPDATE wallets SET bank_name = ?, account_number = ?, account_name = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $bank_name, $account_number, $account_name, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Bank details updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update bank details.";
    }
    header("Location: agent_earnings.php");
    exit();
}
