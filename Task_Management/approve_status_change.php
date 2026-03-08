<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

// Require admin access
requireAdmin();

header('Content-Type: application/json');

if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$requestId = intval($_POST['request_id']);
$action = $_POST['action'];

if ($action === 'approve') {
    if (!isset($_POST['report_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing report details']);
        exit;
    }
    
    $reportId = intval($_POST['report_id']);
    $newStatus = 'Complete';
    
    $conn->begin_transaction();
    
    try {
        $updateReportSql = "UPDATE report SET status = ? WHERE report_id = ?";
        $updateStmt = $conn->prepare($updateReportSql);
        $updateStmt->bind_param("si", $newStatus, $reportId);
        
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update report status');
        }
        $updateStmt->close();
        
        $updateRequestSql = "UPDATE status_change_requests SET approval_status = 'approved', approved_date = NOW() WHERE request_id = ?";
        $requestStmt = $conn->prepare($updateRequestSql);
        $requestStmt->bind_param("i", $requestId);
        
        if (!$requestStmt->execute()) {
            throw new Exception('Failed to update request status');
        }
        $requestStmt->close();
        
        $approvalNotifMsg = "Your proof of completion for Report ID #" . $reportId . " has been approved. Status changed to Complete.";
        $adminUser = getUsername();
        $approvalNotifStmt = $conn->prepare("INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
        $approvalNotifStmt->bind_param("iss", $reportId, $approvalNotifMsg, $adminUser);
        $approvalNotifStmt->execute();
        $approvalNotifStmt->close();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Status changed to Complete successfully']);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} elseif ($action === 'reject') {
    if (!isset($_POST['report_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing report ID']);
        exit;
    }
    
    $reportId = intval($_POST['report_id']);
    $feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';
    
    if (empty($feedback)) {
        echo json_encode(['success' => false, 'message' => 'Feedback is required when rejecting']);
        exit;
    }
    
    $adminUser = getUsername();
    
    $updateRequestSql = "UPDATE status_change_requests SET approval_status = 'rejected', approved_date = NOW(), rejection_feedback = ? WHERE request_id = ?";
    $requestStmt = $conn->prepare($updateRequestSql);
    $requestStmt->bind_param("si", $feedback, $requestId);
    
    if ($requestStmt->execute()) {
        $notificationMsg = "Your status change request for Report ID #" . $reportId . " has been rejected. Feedback: " . $feedback;
        $notifSql = "INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)";
        $notifStmt = $conn->prepare($notifSql);
        $notifStmt->bind_param("iss", $reportId, $notificationMsg, $adminUser);
        $notifStmt->execute();
        $notifStmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Status change request rejected with feedback']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject request']);
    }
    $requestStmt->close();
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
