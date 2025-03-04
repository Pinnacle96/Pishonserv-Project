<?php
include '../includes/db_connect.php';

function getTotalUsers($conn) {
    $query = "SELECT COUNT(*) AS total FROM users";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

function getTotalProperties($conn) {
    $query = "SELECT COUNT(*) AS total FROM properties";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

function getTotalRevenue($conn) {
    $query = "SELECT SUM(amount) AS total FROM transactions WHERE status = 'completed'";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] ?? 0 : 0;
}

function getTransactionStats($conn) {
    $stats = ['completed' => 0, 'pending' => 0, 'failed' => 0];

    $query = "SELECT status, COUNT(*) AS count FROM transactions GROUP BY status";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $stats[$row['status']] = $row['count'];
    }

    return array_values($stats);
}

function getRecentActivities($conn) {
    // Check if table exists before querying
    $checkTable = $conn->query("SHOW TABLES LIKE 'activities'");
    if ($checkTable->num_rows == 0) {
        return ["No activity data available."];
    }

    $query = "SELECT description FROM activities ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($query);
    $activities = [];

    while ($row = $result->fetch_assoc()) {
        $activities[] = $row['description'];
    }

    return empty($activities) ? ["No recent activities."] : $activities;
}
?>