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
    if (!isset($_POST['report_id']) || !isset($_POST['new_status'])) {
        echo json_encode(['success' => false, 'message' => 'Missing report details']);
        exit;
    }
    
    $reportId = intval($_POST['report_id']);
    $newStatus = $_POST['new_status'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update the report status
        $updateReportSql = "UPDATE report SET status = ? WHERE report_id = ?";
        $updateStmt = $conn->prepare($updateReportSql);
        $updateStmt->bind_param("si", $newStatus, $reportId);
        
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update report status');
        }
        $updateStmt->close();
        
        // Update the request status to approved
        $updateRequestSql = "UPDATE status_change_requests SET approval_status = 'approved', approved_date = NOW() WHERE request_id = ?";
        $requestStmt = $conn->prepare($updateRequestSql);
        $requestStmt->bind_param("i", $requestId);
        
        if (!$requestStmt->execute()) {
            throw new Exception('Failed to update request status');
        }
        $requestStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Status change approved successfully']);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} elseif ($action === 'reject') {
    // Update the request status to rejected
    $updateRequestSql = "UPDATE status_change_requests SET approval_status = 'rejected', approved_date = NOW() WHERE request_id = ?";
    $requestStmt = $conn->prepare($updateRequestSql);
    $requestStmt->bind_param("i", $requestId);
    
    if ($requestStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status change request rejected']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject request']);
    }
    $requestStmt->close();
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
