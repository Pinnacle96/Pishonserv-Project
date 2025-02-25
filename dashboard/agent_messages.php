<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../auth/login.php");
    exit();
}
$agent_id = $_SESSION['user_id'];

// Fetch messages where the agent is the receiver
$stmt = $conn->prepare("SELECT m.id, m.message, m.created_at, u.name AS sender_name, m.sender_role, m.status 
                        FROM messages m 
                        JOIN users u ON m.sender_id = u.id 
                        WHERE m.receiver_id = ? 
                        ORDER BY m.created_at DESC");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php $page_content = __DIR__ . "/agent_messages_content.php"; include 'dashboard_layout.php'; ?>