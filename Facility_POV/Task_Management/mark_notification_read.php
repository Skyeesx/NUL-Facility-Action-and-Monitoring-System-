<?php
require_once __DIR__ . '/../../connection/session_config.php';
require_once __DIR__ . '/../../connection/db_connection.php';

// Require facility staff access
requireFacility();

header('Content-Type: application/json');

if (isset($_POST['mark_all']) && $_POST['mark_all'] === 'true') {
    $sql = "UPDATE notifications SET is_read = 1 WHERE is_read = 0";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to mark all as read']);
    }
    $conn->close();
    exit;
}

if (!isset($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
    exit;
}

$notificationId = intval($_POST['notification_id']);

$sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $notificationId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark as read']);
}

$stmt->close();
$conn->close();
?>
