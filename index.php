<?php
require_once __DIR__ . '/connection/session_config.php';
require_once __DIR__ . '/connection/db_connection.php';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: /FAMS/Dashboard_page/Dashboard.php");
    }
    if (isFacility()) { 
      header("Location: /FAMS/Facility_POV/Dashboard_page/Dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NUL-FAMS | Log in as</title>
  <link rel="stylesheet" href="Log_in_as.css">
</head>
<body>
  <div class="log-in-container">
    <div class="content-wrapper">
      <img src="./Log_in_page/Log_in_images/Logo.png" alt="Logo" id="logo">

      <div class="title-group">
        <h1>Sign In</h1>
        <h2>Choose your account type</h2>
      </div>

      <!-- Button section -->
      <div class="button-group">
        <a href="Log_in_page/Admin_LogIn.php" class="role-button admin-btn">Log in as Admin</a>
        <a href="Log_in_page/Log_in_faci.php" class="role-button facility-btn">Log in as Facility Member</a>
      </div>
    </div>
  </div>
</body>
</html>
