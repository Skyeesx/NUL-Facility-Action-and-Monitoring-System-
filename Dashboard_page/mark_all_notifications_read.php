<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

requireAdmin();

header('Content-Type: application/json');

try {
    $createTableSql = "CREATE TABLE IF NOT EXISTS admin_notifications_read (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_username VARCHAR(255) NOT NULL,
        notification_type VARCHAR(50) NOT NULL,
        notification_id VARCHAR(100) NOT NULL,
        marked_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_notif (admin_username, notification_type, notification_id)
    )";
    
    if (!$conn->query($createTableSql)) {
        throw new Exception("Failed to create table: " . $conn->error);
    }

    $username = getUsername();
    
    $currentNotifications = [];
    
    $newReportsSql = "SELECT report_id FROM report WHERE report_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $result = $conn->query($newReportsSql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $currentNotifications[] = [
                'type' => 'new_report',
                'id' => 'report_' . $row['report_id']
            ];
        }
    }
    
    $approvalsSql = "SELECT request_id FROM status_change_requests WHERE approval_status = 'pending'";
    $result = $conn->query($approvalsSql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $currentNotifications[] = [
                'type' => 'approval_request',
                'id' => 'approval_' . $row['request_id']
            ];
        }
    }
    
    $stmt = $conn->prepare("INSERT IGNORE INTO admin_notifications_read (admin_username, notification_type, notification_id) VALUES (?, ?, ?)");
    
    $markedCount = 0;
    foreach ($currentNotifications as $notif) {
        $stmt->bind_param("sss", $username, $notif['type'], $notif['id']);
        if ($stmt->execute()) {
            $markedCount++;
        }
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'All notifications marked as read',
        'marked_count' => $markedCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
