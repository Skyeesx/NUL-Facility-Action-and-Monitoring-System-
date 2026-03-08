<?php
include '../connection/db_connection.php'; 

if (isset($_POST['report_id'], $_POST['status'])) {
    $id = intval($_POST['report_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE report SET status = ? WHERE report_id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Status updated successfully!";
    } else {
        echo "No rows updated. Check if report_id exists or value unchanged.";
    }

    $stmt->close();
} else {
    echo "Missing parameters.";
}
$conn->close();
?>
