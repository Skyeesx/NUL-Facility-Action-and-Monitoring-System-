<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../connection/db_connection.php';

$query = "SELECT status FROM report";
$result = $conn->query($query);

$data1 = [];
while ($row = $result->fetch_assoc()) {
    $data1[] = ['status' => $row['status']];
}

echo json_encode($data1);
$conn->close();

?>