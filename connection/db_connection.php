<?php
require 'config.php';


$createUserTable = "CREATE TABLE IF NOT EXISTS fams_user (
        UserID INT(11) AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        UserRole ENUM('Admin', 'Facility Management Staff') NOT NULL
)";
if ($conn->query($createUserTable) === FALSE) {
    echo "Error creating user table: " . $conn->error;
}

$createReportTable = "CREATE TABLE IF NOT EXISTS report (
        report_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        contact_number VARCHAR(15) NOT NULL,
        issue_type VARCHAR(100) NOT NULL,
        building_name VARCHAR(100) NOT NULL,
        area VARCHAR(100) NOT NULL,
        details VARCHAR(255) NOT NULL,
        photo_path VARCHAR(255),
        report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50) DEFAULT 'Pending',
        priority_level ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
        proof_of_completion VARCHAR(255) DEFAULT NULL
    )";

if ($conn->query($createReportTable) === FALSE) {
    echo "Error creating report table: " . $conn->error;
}

$createStatusChangeRequestsTable = "CREATE TABLE IF NOT EXISTS status_change_requests (
        request_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        report_id INT(11) NOT NULL,
        current_status VARCHAR(50) NOT NULL,
        new_status VARCHAR(50) NOT NULL,
        requested_by VARCHAR(100) NOT NULL,
        request_date DATETIME NOT NULL,
        approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        approved_date DATETIME DEFAULT NULL,
        rejection_feedback TEXT DEFAULT NULL,
        FOREIGN KEY (report_id) REFERENCES report(report_id) ON DELETE CASCADE
    )";
if ($conn->query($createStatusChangeRequestsTable) === FALSE) {
    echo "Error creating status_change_requests table: " . $conn->error;
}

$createNotificationsTable = "CREATE TABLE IF NOT EXISTS notifications (
        notification_id INT(11) AUTO_INCREMENT PRIMARY KEY,
        report_id INT(11) NOT NULL,
        message TEXT NOT NULL,
        created_by VARCHAR(100) NOT NULL,
        created_at DATETIME NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        FOREIGN KEY (report_id) REFERENCES report(report_id) ON DELETE CASCADE
    )";
if ($conn->query($createNotificationsTable) === FALSE) {
    echo "Error creating notifications table: " . $conn->error;
}

?>