<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

// This page only renders the login form. Authentication is handled in register_user.php
$role = isset($_GET['role']) ? $_GET['role'] : 'Facility Management Staff';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUL-FAMS | Log in</title>
    <link rel="stylesheet" href="Log_in_faci.css">
</head>
<body>
    <div class="log-in-container">
          <div class="content-wrapper">
            <img src="./Log_in_images/Logo.png" alt="Logo" id="logo">
            <div class="title-group">
              <h1>Sign In</h1>
              <h2>Sign-in with your NU account</h2>
            </div>
            <form action="register_user.php" method="post" class="sign-in-form">
              <input type="hidden" name="UserRole" value="<?= htmlspecialchars($role) ?>">
              <div class="input-group">
                <div class="icon-bg">
                  <img src="Log_in_images/username_icon.png" alt="User Icon" class="input-icon">
                </div>
                <div class="input-text">
                  <input type="text" name="Username" placeholder="jadelacruz@nu-laguna.edu.ph" required>
                </div>
              </div>
              <div class="input-group">
                <div class="icon-bg">
                  <img src="Log_in_images/password_icon.png" alt="Password Icon" class="input-icon">
                </div>
                <div class="input-text">
                  <input type="password" name="Password" placeholder="Password" required>
                </div>
              </div>
              <button type="submit" class="log-in-button">Log In</button>
            </form>
          </div>
    </div>
</body>
</html>