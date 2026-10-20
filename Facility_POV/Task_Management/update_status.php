<?php
require_once __DIR__ . '/../../connection/api_guard.php';
require_once __DIR__ . '/../../connection/db_connection.php';

requireApi('Facility Management Staff');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['report_id']) || !isset($_POST['new_status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$allowedStatuses = ['Pending', 'In Progress', 'Complete'];
$reportId  = intval($_POST['report_id']);
$newStatus = $_POST['new_status'];
$facilityUser = getUsername();

if (!in_array($newStatus, $allowedStatuses, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Current state comes from the database, not from the browser.
$reportStmt = $conn->prepare("SELECT status, proof_of_completion, issue_type, building_name FROM report WHERE report_id = ?");
$reportStmt->bind_param("i", $reportId);
$reportStmt->execute();
$report = $reportStmt->get_result()->fetch_assoc();
$reportStmt->close();

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit;
}
if ($report['status'] === 'Complete') {
    echo json_encode(['success' => false, 'message' => 'This report is already complete']);
    exit;
}
if (empty($report['proof_of_completion'])) {
    echo json_encode(['success' => false, 'message' => 'Upload proof before requesting a status change']);
    exit;
}
if ($report['status'] === $newStatus) {
    echo json_encode(['success' => false, 'message' => 'The report already has that status']);
    exit;
}

$currentStatus = $report['status'];

$checkStmt = $conn->prepare("SELECT request_id FROM status_change_requests WHERE report_id = ? AND approval_status = 'pending'");
$checkStmt->bind_param("i", $reportId);
$checkStmt->execute();
$hasPending = $checkStmt->get_result()->num_rows > 0;
$checkStmt->close();

if ($hasPending) {
    $updateStmt = $conn->prepare("UPDATE status_change_requests SET new_status = ?, request_date = NOW() WHERE report_id = ? AND approval_status = 'pending'");
    $updateStmt->bind_param("si", $newStatus, $reportId);
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status change request updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update request']);
    }
    $updateStmt->close();
} else {
    $insertStmt = $conn->prepare("INSERT INTO status_change_requests (report_id, current_status, new_status, requested_by, request_date, approval_status) VALUES (?, ?, ?, ?, NOW(), 'pending')");
    $insertStmt->bind_param("isss", $reportId, $currentStatus, $newStatus, $facilityUser);

    if ($insertStmt->execute()) {
        $notifMessage = "Approval needed for proof validation: " . $report['issue_type'] . " - " . $report['building_name'];
        $notifStmt = $conn->prepare("INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
        $notifStmt->bind_param("iss", $reportId, $notifMessage, $facilityUser);
        $notifStmt->execute();
        $notifStmt->close();

        echo json_encode(['success' => true, 'message' => 'Status change request submitted for approval']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create request']);
    }
    $insertStmt->close();
}

$conn->close();
?>
