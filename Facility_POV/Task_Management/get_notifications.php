<?php
require_once __DIR__ . '/../../connection/session_config.php';
require_once __DIR__ . '/../../connection/db_connection.php';

// Require facility staff access
requireFacility();

header('Content-Type: application/json');

$countSql = "SELECT COUNT(*) as unread_count FROM notifications WHERE is_read = 0";
$countResult = $conn->query($countSql);
$unreadCount = $countResult->fetch_assoc()['unread_count'];

$notifSql = "SELECT n.*, r.details 
             FROM notifications n
             LEFT JOIN report r ON n.report_id = r.report_id
             ORDER BY n.created_at DESC 
             LIMIT 10";
$notifResult = $conn->query($notifSql);

$notifications = [];
while ($row = $notifResult->fetch_assoc()) {
    $notifications[] = [
        'id' => $row['notification_id'],
        'report_id' => $row['report_id'],
        'message' => $row['message'],
        'details' => $row['details'] ?? '',
        'created_at' => $row['created_at'],
        'is_read' => $row['is_read']
    ];
}

echo json_encode([
    'success' => true,
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);

$conn->close();
?>
