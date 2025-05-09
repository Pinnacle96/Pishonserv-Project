<?php
include 'includes/db_connect.php';

$property_id = filter_input(INPUT_GET, 'property_id', FILTER_VALIDATE_INT);

if (!$property_id) {
    echo json_encode([]);
    exit;
}

$query = "SELECT check_in_date, check_out_date FROM bookings 
          WHERE property_id = ? AND status = 'confirmed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

$bookedDates = [];

while ($row = $result->fetch_assoc()) {
    $start = new DateTime($row['check_in_date']);
    $end = new DateTime($row['check_out_date']);
    $interval = new DateInterval('P1D');
    $range = new DatePeriod($start, $interval, $end->modify('+1 day')); // include last day

    foreach ($range as $date) {
        $bookedDates[] = $date->format('Y-m-d');
    }
}

echo json_encode($bookedDates);
?>
