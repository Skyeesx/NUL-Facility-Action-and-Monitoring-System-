<?php
require_once __DIR__ . '/../connection/api_guard.php';
require_once __DIR__ . '/../connection/db_connection.php';

requireApi('Admin');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['request_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$allowedStatuses = ['Pending', 'In Progress', 'Complete'];
$requestId = intval($_POST['request_id']);
$action    = $_POST['action'];
$adminUser = getUsername();

if ($action !== 'approve' && $action !== 'reject') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$feedback = '';
if ($action === 'reject') {
    $feedback = mb_substr(trim($_POST['feedback'] ?? ''), 0, 1000);
    if ($feedback === '') {
        echo json_encode(['success' => false, 'message' => 'Feedback is required when rejecting']);
        exit;
    }
}

$conn->begin_transaction();

try {
    // The request row is the source of truth for the report and the requested status.
    // Values posted by the browser are not trusted.
    $lookup = $conn->prepare("SELECT report_id, new_status, approval_status FROM status_change_requests WHERE request_id = ? FOR UPDATE");
    $lookup->bind_param("i", $requestId);
    $lookup->execute();
    $request = $lookup->get_result()->fetch_assoc();
    $lookup->close();

    if (!$request) {
        throw new Exception('Request not found');
    }
    if ($request['approval_status'] !== 'pending') {
        throw new Exception('This request has already been processed');
    }

    $reportId  = (int) $request['report_id'];
    $newStatus = $request['new_status'];

    if ($action === 'approve') {
        if (!in_array($newStatus, $allowedStatuses, true)) {
            throw new Exception('Requested status is not valid');
        }

        $updateReport = $conn->prepare("UPDATE report SET status = ? WHERE report_id = ?");
        $updateReport->bind_param("si", $newStatus, $reportId);
        if (!$updateReport->execute()) {
            throw new Exception('Failed to update report status');
        }
        $updateReport->close();

        $updateRequest = $conn->prepare("UPDATE status_change_requests SET approval_status = 'approved', approved_date = NOW() WHERE request_id = ?");
        $updateRequest->bind_param("i", $requestId);
        if (!$updateRequest->execute()) {
            throw new Exception('Failed to update request status');
        }
        $updateRequest->close();

        $message = "Your status change request for Report ID #" . $reportId . " has been approved. Status changed to " . $newStatus . ".";
        $responseMessage = 'Status changed to ' . $newStatus . ' successfully';
    } else {
        $updateRequest = $conn->prepare("UPDATE status_change_requests SET approval_status = 'rejected', approved_date = NOW(), rejection_feedback = ? WHERE request_id = ?");
        $updateRequest->bind_param("si", $feedback, $requestId);
        if (!$updateRequest->execute()) {
            throw new Exception('Failed to reject request');
        }
        $updateRequest->close();

        $message = "Your status change request for Report ID #" . $reportId . " has been rejected. Feedback: " . $feedback;
        $responseMessage = 'Status change request rejected with feedback';
    }

    $notif = $conn->prepare("INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
    $notif->bind_param("iss", $reportId, $message, $adminUser);
    $notif->execute();
    $notif->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => $responseMessage]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
