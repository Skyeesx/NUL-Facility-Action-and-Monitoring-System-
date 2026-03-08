<?php
include '../connection/db_connection.php';

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
