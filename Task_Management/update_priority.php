<?php
include '../connection/db_connection.php'; // adjust path as needed

if (isset($_POST['report_id'], $_POST['priority_level'])) {
    $id = intval($_POST['report_id']);
    $priority = $_POST['priority_level'];

    $stmt = $conn->prepare("UPDATE report SET priority_level = ? WHERE report_id = ?");
    $stmt->bind_param("si", $priority, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Priority updated successfully!";
    } else {
        echo "No rows updated. Check if report_id exists or value unchanged.";
    }
    $stmt->close();
} else {
    echo "Missing parameters.";
}
$conn->close();
?>
