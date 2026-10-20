<?php
require_once __DIR__ . '/../../../connection/api_guard.php'; 
requireApi('Facility Management Staff');
include '../../../connection/db_connection.php';

// Get the 3 most recent reports (you can adjust LIMIT as needed)
$query = "SELECT area, details, status, report_date FROM report ORDER BY report_date DESC LIMIT 8";
$result = $conn->query($query);

$reports = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($reports);
?>
