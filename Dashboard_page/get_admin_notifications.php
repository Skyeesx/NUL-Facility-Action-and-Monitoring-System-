<?php
require_once __DIR__ . '/../connection/api_guard.php'; 
requireApi('Admin');
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';
header('Content-Type: application/json');

$createTableSql = "CREATE TABLE IF NOT EXISTS admin_notifications_read (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(255) NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    notification_id VARCHAR(100) NOT NULL,
    marked_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_notif (admin_username, notification_type, notification_id)
)";
$conn->query($createTableSql);

$username = getUsername();
$notifications = [];

$newReportsSql = "SELECT 
                    r.report_id,
                    r.email,
                    r.report_date as created_at,
                    'new_report' as type
                  FROM report r
                  LEFT JOIN admin_notifications_read anr 
                    ON anr.notification_id = CONCAT('report_', r.report_id)
                    AND anr.admin_username = ?
                    AND anr.notification_type = 'new_report'
                  WHERE r.report_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND anr.id IS NULL
                  ORDER BY r.report_date ASC 
                  LIMIT 10";

$stmt = $conn->prepare($newReportsSql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $message = "New report submitted by: " . $row['email'];
        $notifications[] = [
            'id' => 'report_' . $row['report_id'],
            'message' => $message,
            'created_at' => $row['created_at'],
            'type' => 'new_report',
            'report_id' => $row['report_id'],
            'link' => '../Submitted_Reports/submittedReports.php'
        ];
    }
}
$stmt->close();

$approvalRequestsSql = "SELECT 
                            scr.request_id,
                            scr.report_id,
                            scr.new_status,
                            scr.request_date,
                            scr.requested_by
                        FROM status_change_requests scr
                        LEFT JOIN admin_notifications_read anr 
                            ON anr.notification_id = CONCAT('approval_', scr.request_id)
                            AND anr.admin_username = ?
                            AND anr.notification_type = 'approval_request'
                        WHERE scr.approval_status = 'pending'
                            AND anr.id IS NULL
                        ORDER BY scr.request_date ASC
                        LIMIT 10";

$stmt = $conn->prepare($approvalRequestsSql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $message = "Approval needed by: " . $row['requested_by'];
        
        $notifications[] = [
            'id' => 'approval_' . $row['request_id'],
            'message' => $message,
            'created_at' => $row['request_date'],
            'type' => 'approval_request',
            'report_id' => $row['report_id'],
            'request_id' => $row['request_id'],
            'link' => '../Task_Management/index.php'
        ];
    }
}
$stmt->close();

usort($notifications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$notifications = array_slice($notifications, 0, 15);

$unreadCount = count($notifications);

echo json_encode([
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);

$conn->close();
?>
