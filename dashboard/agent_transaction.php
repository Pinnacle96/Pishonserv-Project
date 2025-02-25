<?php
session_start();
include '../includes/db_connect.php';

// Restrict access to agents, owners, and hotel owners
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['agent', 'owner', 'hotel_owner'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch transactions
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$query = "SELECT * FROM transactions WHERE user_id = ? ";
if ($filter_type === 'credit') {
    $query .= "AND type = 'credit' ";
} elseif ($filter_type === 'debit') {
    $query .= "AND type = 'debit' ";
}
$query .= "ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle PDF export
if (isset($_GET['export_pdf'])) {
    require('../vendor/autoload.php');

    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(190, 10, 'Transaction History', 0, 1, 'C');
            $this->Ln(10);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(190, 10, "₦" . number_format($row['amount'], 2) . " | " . ucfirst($row['type']) . " | " . ucfirst($row['status']), 0, 1);
    }

    $pdf->Output();
    exit();
}

// Load page content
$page_content = __DIR__ . "/agent_transaction_content.php";
include 'dashboard_layout.php';
?>