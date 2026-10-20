<?php
require_once __DIR__ . '/../../connection/api_guard.php';
require_once __DIR__ . '/../../connection/db_connection.php';
require_once __DIR__ . '/../../connection/upload_helpers.php';

requireApi('Facility Management Staff');

header('Content-Type: application/json');

if (!isset($_FILES['proof']) || !isset($_POST['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$reportId = intval($_POST['report_id']);
$file = $_FILES['proof'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $file['error']]);
    exit;
}

// The report must exist and must not already be complete.
$check = $conn->prepare("SELECT status FROM report WHERE report_id = ?");
$check->bind_param("i", $reportId);
$check->execute();
$report = $check->get_result()->fetch_assoc();
$check->close();

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit;
}
if ($report['status'] === 'Complete') {
    echo json_encode(['success' => false, 'message' => 'This report is already complete']);
    exit;
}

// Validate by real file content, and take the extension from the detected type.
$fileType = famsDetectMime($file['tmp_name']);
$allTypes = FAMS_IMAGE_TYPES + FAMS_VIDEO_TYPES;

if (!isset($allTypes[$fileType])) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images (JPG, PNG, GIF, WEBP) and videos (MP4, MOV, AVI, WEBM) are allowed.']);
    exit;
}

$isVideo = isset(FAMS_VIDEO_TYPES[$fileType]);
$maxSize = $isVideo ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds ' . ($isVideo ? '50MB' : '10MB') . ' limit.']);
    exit;
}

$uploadDir = __DIR__ . '/uploads';
famsEnsureUploadDir($uploadDir);

$filename = famsRandomName('proof_' . $reportId, $allTypes[$fileType]);
$uploadPath = $uploadDir . '/' . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $stmt = $conn->prepare("UPDATE report SET proof_of_completion = ? WHERE report_id = ?");
    $stmt->bind_param("si", $filename, $reportId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Proof uploaded successfully', 'filename' => $filename]);
    } else {
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}

$conn->close();
?>
