<?php
require_once __DIR__ . '/../../connection/session_config.php';
require_once __DIR__ . '/../../connection/db_connection.php';

// Require facility staff access
requireFacility();

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

// Validate file type - only images and videos allowed
$allowedTypes = [
    'image/jpeg', 
    'image/png', 
    'image/jpg', 
    'image/gif',
    'image/webp',
    'video/mp4',
    'video/mpeg',
    'video/quicktime',
    'video/x-msvideo',
    'video/webm'
];
$fileType = mime_content_type($file['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images (JPG, PNG, GIF, WEBP) and videos (MP4, MOV, AVI, WEBM) are allowed.']);
    exit;
}

// Validate file size (max 50MB for videos, 10MB for images)
$maxSize = (strpos($fileType, 'video') !== false) ? 50 * 1024 * 1024 : 10 * 1024 * 1024; // 50MB for video, 10MB for images
if ($file['size'] > $maxSize) {
    $maxSizeMB = (strpos($fileType, 'video') !== false) ? '50MB' : '10MB';
    echo json_encode(['success' => false, 'message' => 'File size exceeds ' . $maxSizeMB . ' limit.']);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'proof_' . $reportId . '_' . time() . '.' . $extension;
$uploadPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $stmt = $conn->prepare("UPDATE report SET proof_of_completion = ? WHERE report_id = ?");
    $stmt->bind_param("si", $filename, $reportId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Proof uploaded successfully',
            'filename' => $filename
        ]);
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
