<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$username = isset($_POST['Username']) ? trim($_POST['Username']) : '';
$password = isset($_POST['Password']) ? $_POST['Password'] : '';
$role     = isset($_POST['UserRole']) ? $_POST['UserRole'] : '';

// Debug logging
error_log("Login attempt - Username: $username, Password length: " . strlen($password) . ", Role: $role");

// Validate inputs
if (empty($username) || empty($password) || empty($role)) {
    echo "<script>alert('All fields are required.'); history.back();</script>";
    exit;
}
// Check if user exists
$stmt = $conn->prepare("SELECT * FROM fams_user WHERE Username = ? AND UserRole = ?");
$stmt->bind_param("ss", $username, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Debug logging
    error_log("User found in DB - Username: {$user['Username']}, Role: {$user['UserRole']}");
    error_log("Stored password hash: {$user['Password']}");

    if ($user['UserRole'] === 'Admin' && $user['Username'] === 'fams@admin') {
        // Special case for superadmin
        error_log("Superadmin login - bypassing password check");
        setUserSession($user['UserID'], $user['Username'], $user['UserRole']);
        header("Location: ../Dashboard_page/Dashboard.php");
        exit;
    }
    // Verify hashed password
    error_log("Attempting password_verify for user: $username");
    if (password_verify($password, $user['Password'])) {
        error_log("Password verification SUCCESS for user: $username");
        setUserSession($user['UserID'], $user['Username'], $user['UserRole']);

        if ($user['UserRole'] === 'Admin') {
            header("Location: ../Dashboard_page/Dashboard.php");
        } else {
            header("Location: ../Facility_POV/Dashboard_page/Dashboard.php");
        }
        exit; 
    } else {
        error_log("Password verification FAILED for user: $username");
        error_log("Entered password: $password");
        error_log("Hash from DB: {$user['Password']}");
        echo "<script>alert('Incorrect password. Please try again.'); history.back();</script>";
    }
} else {
    error_log("User not found - Username: $username, Role: $role");
    echo "<script>
        alert('User not found. Please contact the administrator to create your account.');
        history.back();
    </script>";
}
?>
