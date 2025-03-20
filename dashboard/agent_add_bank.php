<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $bank_name = $_POST['bank_name'];
    $account_number = $_POST['account_number'];
    $account_name = $_POST['account_name'];

    $stmt = $conn->prepare("INSERT INTO bank_accounts (user_id, bank_name, account_number, account_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $bank_name, $account_number, $account_name);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Bank account added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add bank account.";
    }
    header("Location: agent_earnings.php");
    exit();
}
