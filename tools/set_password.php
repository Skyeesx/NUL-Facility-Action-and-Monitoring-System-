<?php
/**
 * Command-line helper: create or reset a user's password (stored hashed).
 *
 *   php tools/set_password.php fams@admin "NewStrongPassword" Admin
 *   php tools/set_password.php staff@nu-laguna.edu.ph "Password" "Facility Management Staff"
 *
 * Needed once for the default admin account, because the login no longer
 * bypasses the password check for it.
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}
require_once __DIR__ . '/../connection/db_connection.php';

if ($argc < 3) {
    fwrite(STDERR, "Usage: php tools/set_password.php <username> <password> [Admin|\"Facility Management Staff\"]\n");
    exit(1);
}
[$script, $username, $password] = $argv;
$role = $argv[3] ?? 'Admin';

if (!in_array($role, ['Admin', 'Facility Management Staff'], true)) {
    fwrite(STDERR, "Role must be 'Admin' or 'Facility Management Staff'\n");
    exit(1);
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Use a password of at least 8 characters\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO fams_user (Username, Password, UserRole) VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE Password = VALUES(Password), UserRole = VALUES(UserRole)"
);
$stmt->bind_param("sss", $username, $hash, $role);
$stmt->execute();
echo "Password set for $username ($role)\n";
