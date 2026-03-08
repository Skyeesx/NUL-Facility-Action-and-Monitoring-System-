<?php
require_once __DIR__ . '/../../connection/session_config.php';
require_once __DIR__ . '/../../connection/db_connection.php';

// Require facility staff access
requireFacility();

header('Content-Type: application/json');

if (!isset($_POST['report_id']) || !isset($_POST['new_status']) || !isset($_POST['current_status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$reportId = intval($_POST['report_id']);
$newStatus = $_POST['new_status'];
$currentStatus = $_POST['current_status'];

// Get facility user info from session
$facilityUser = getUsername();

$checkSql = "SELECT * FROM status_change_requests WHERE report_id = ? AND approval_status = 'pending'";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $reportId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    $updateSql = "UPDATE status_change_requests SET new_status = ?, request_date = NOW() WHERE report_id = ? AND approval_status = 'pending'";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $reportId);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status change request updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update request']);
    }
    $updateStmt->close();
} else {
    $insertSql = "INSERT INTO status_change_requests (report_id, current_status, new_status, requested_by, request_date, approval_status) VALUES (?, ?, ?, ?, NOW(), 'pending')";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("isss", $reportId, $currentStatus, $newStatus, $facilityUser);
    
    if ($insertStmt->execute()) {
        $reportSql = "SELECT issue_type, building_name, area FROM report WHERE report_id = ?";
        $reportStmt = $conn->prepare($reportSql);
        $reportStmt->bind_param("i", $reportId);
        $reportStmt->execute();
        $reportResult = $reportStmt->get_result();
        $reportData = $reportResult->fetch_assoc();
        
        $notifMessage = "Approval needed for proof validation: " . $reportData['issue_type'] . " - " . $reportData['building_name'];
        $notifStmt = $conn->prepare("INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
        $notifStmt->bind_param("iss", $reportId, $notifMessage, $facilityUser);
        $notifStmt->execute();
        $notifStmt->close();
        $reportStmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Status change request submitted for approval']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create request: ' . $conn->error]);
    }
    $insertStmt->close();
}

$checkStmt->close();
$conn->close();
?>
