<?php
header('Content-Type: application/json; charset=utf-8');
include('../../../connection/db_connection.php');

$query = "SELECT issue_type, status, COUNT(*) as count FROM report GROUP BY issue_type, status";
$result = $conn->query($query);

$data2 = [];
while ($row = $result->fetch_assoc()) {
    $data2[] = $row;
}

echo json_encode($data2);
?>