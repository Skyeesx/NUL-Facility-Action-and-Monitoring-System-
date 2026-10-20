<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

// This script is the login handler (the filename is historical).

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$username = isset($_POST['Username']) ? trim($_POST['Username']) : '';
$password = isset($_POST['Password']) ? $_POST['Password'] : '';
$role     = isset($_POST['UserRole']) ? $_POST['UserRole'] : '';

// Never log passwords or password hashes.
error_log("Login attempt - Username: $username, Role: $role");

if (empty($username) || empty($password) || empty($role)) {
    echo "<script>alert('All fields are required.'); history.back();</script>";
    exit;
}

$stmt = $conn->prepare("SELECT UserID, Username, Password, UserRole FROM fams_user WHERE Username = ? AND UserRole = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Every account, including the default admin, must pass password verification.
    if (password_verify($password, $user['Password'])) {
        error_log("Login success - Username: $username");
        setUserSession($user['UserID'], $user['Username'], $user['UserRole']);

        if ($user['UserRole'] === 'Admin') {
            header("Location: ../Dashboard_page/Dashboard.php");
        } else {
            header("Location: ../Facility_POV/Dashboard_page/Dashboard.php");
        }
        exit;
    } else {
        error_log("Login failed (bad password) - Username: $username");
        echo "<script>alert('Incorrect password. Please try again.'); history.back();</script>";
    }
} else {
    error_log("Login failed (user not found) - Username: $username, Role: $role");
    echo "<script>
        alert('User not found. Please contact the administrator to create your account.');
        history.back();
    </script>";
}
$stmt->close();
?>
