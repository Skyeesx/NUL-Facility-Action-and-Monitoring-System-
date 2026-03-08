<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

// Require admin access
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NUL-FAMS | Progress Tracker</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Progress_Tracker_style.css">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bungee+Inline&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@100..700&display=swap" rel="stylesheet">

  <!-- Google Charts -->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

  <!-- jsPDF and html2canvas libraries -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>

      

  <!-- Navbar -->
  <nav class="navbar bg-body-tertiary shadow-sm">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- Left side (hamburger + brand) -->
            <div class="left-side d-flex align-items-center gap-3">
            <a href="#sidebar" class="hamburger-link" data-bs-toggle="offcanvas" role="button" aria-controls="sidebar">
                <img src="Hamburger_icon.png" alt="Hamburger" class="hamburger_icon">
            </a>

            <a class="navbar-brand d-flex align-items-center" href="../dashboard_page/dashboard.php">
                <img src="Logo.png" alt="Logo" width="91" height="63" class="d-inline-block align-text-top">
                NU-L FAMS
            </a>
            </div>

            <!-- Right side (Notifications + Profile) -->
            <div class="right-side d-flex align-items-center gap-4">

            <!-- 🔔 Notifications Icon with Badge -->
            <div class="dropdown notification-dropdown">
                <a href="#" class="position-relative d-flex align-items-center" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="bell.png" alt="Notifications" class="notification-icon">
                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;"></span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow notif-menu" id="notificationMenu" aria-labelledby="notifDropdown">
                    <li class="dropdown-header fw-bold">Notifications</li>
                    <li id="noNotifications"><a class="dropdown-item small text-muted">No new notifications</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center text-primary small" href="../Submitted_reports/submittedReports.php">View all</a></li>
                </ul>
            </div>


            <!-- 👤 Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="profile.jpg" alt="Profile" class="rounded-circle" width="40" height="40">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown">
                <li><span class="dropdown-item-text small"><strong><?php echo htmlspecialchars(getUsername()); ?></strong></span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php">Sign Out</a></li>
                </ul>
            </div>
            </div>
        </div>
    </nav>

    <!-- Generate Report Button (above chart cards) -->
    <div class="report-btn-container">
      <button id="generatePDFBtn" class="generate-report-btn">Generate Report</button>
    </div>

  <!-- Dashboard Body -->
  <div id="dashboard_body">
    <!-- LEFT COLUMN: Charts -->
    <div class="dashboard_left">
      <!-- Column Chart -->
      <div class="dashboard_content chart-card">
        <h1>Task Progress (%)</h1>
        <div id="column_chart" class="chart-box"></div>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="dashboard_right">
      <!-- Progress Over Time -->
      <div class="dashboard_content chart-card">
        <h1>Progress Over Time</h1>
        <div id="line_chart" class="chart-box"></div>
      </div>
    </div>
  </div>

  <!-- Sidebar / Offcanvas -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
    <a class="sidebar-brand" href="../dashboard_page/dashboard.php">
      <img src="Logo.png" alt="Logo" width="90" height="60" class="d-inline-block align-text-top">
      NU-L FAMS
    </a>
    <div class="offcanvas-body">
      <ul class="sidebar-menu">
        <li><a href="../dashboard_page/dashboard.php"><img src="./Dashboard_icon.png">Dashboard</a></li>
        <li><a href="../Submitted_Reports/submittedReports.php"><img src="./Reports_icon.png">Reports</a></li>
        <li><a href="../Task_Management/index.php"><img src="./Task_icon.png">Tasks</a></li>
        <li><a href="../Progress_Tracker_page/Progress_Tracker.html"><img src="./Progress_icon.png">Progress Tracker</a></li>
      </ul>
    </div>
    <!-- User Info -->
    <div class="user-info">
      <p class="user-label">Logged in as:</p>
      <div class="user-row">
        <img src="profile.jpg" alt="Profile Avatar" class="user-avatar">
        <div class="user-details">
          <p class="user-name">Username</p>
          <a class="sign-out" href="../logout.php">Sign-out</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="progress_tracker.js"></script>
</body>
</html>
