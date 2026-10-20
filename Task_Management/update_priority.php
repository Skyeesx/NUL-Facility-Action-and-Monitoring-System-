<?php
require_once __DIR__ . '/../connection/api_guard.php';
require_once __DIR__ . '/../connection/db_connection.php';

requireApi('Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

$allowed = ['Low', 'Medium', 'High'];

if (isset($_POST['report_id'], $_POST['priority_level'])) {
    $id = intval($_POST['report_id']);
    $priority = $_POST['priority_level'];

    if (!in_array($priority, $allowed, true)) {
        http_response_code(400);
        echo "Invalid priority level.";
        exit;
    }

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
    http_response_code(400);
    echo "Missing parameters.";
}
$conn->close();
?>
