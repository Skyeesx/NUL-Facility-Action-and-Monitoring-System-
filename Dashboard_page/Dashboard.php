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
    <title>NUL-FAMS | Dashboard</title>

    <!-- Bootstrap CSS (needed for sidebar) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Dashboard_style.css">

    <link href="https://fonts.googleapis.com/css2?family=Bungee+Inline&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@100..700&display=swap" rel="stylesheet">

    <!-- Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow notif-menu" aria-labelledby="notifDropdown" id="notificationMenu">
                    <li class="dropdown-header fw-bold d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <button id="markAllReadBtn" class="btn btn-sm btn-link text-primary p-0 small" style="display:none; font-size: 11px; text-decoration: none;">Mark all as read</button>
                    </li>
                    <li id="noNotifications"><span class="dropdown-item small text-muted">No new notifications</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center text-primary small" href="../Submitted_Reports/submittedReports.php">View all reports</a></li>
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
                <li><a class="dropdown-item" href="../Log_in_page/add_user.php">Add User</a></li>
                <li><a class="dropdown-item text-danger" href="../logout.php">Sign Out</a></li>
                </ul>
            </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Body -->
    <div id="dashboard_body">
         <div class="dashboard_left">
            <div class="dashboard_content recent">
                <h1>Recent Reports</h1>
                <div id="recentReportsContainer">
                    <!-- Recent reports will be loaded here dynamically -->
                    <div class="report-format"></div>
                </div>
                <div class="view_rep">
                    <a href="../Submitted_reports/submittedReports.php" class="view-more-btn">
                        <img src="arrow-up.png" alt="arrow up">
                        View all reports
                    </a>
                </div>
            </div>
        </div>
        <div class="dashboard_middle">
            <div class="dashboard_content_middle monthly">
                <h1>Monthly Progress</h1>
                <!-- Top row: charts 1 and 2 -->
                <div class="charts-top-row">
                    <div id="columnchart1" class="chart-box"></div>
                    <div id="columnchart2" class="chart-box"></div>
                </div>

                <!-- Bottom row: chart 3 centered -->
                <div class="charts-bottom-row">
                    <div id="columnchart3" class="chart-box"></div>
                </div>
            </div>
            <div class="dashboard_content_middle priority">
                <div class="title">
                    <a href="../Task_Management/index.php"><h1>Priority Tasks</h1></a>
                    <div class="addtask" id="priorityTaskContainer"><span class="add-icon"><a href="../Task_Management/index.php">+</a></span></div>
                </div>

                <div class="tasklist">
                </div>
            </div>
        </div>
        <div class="dashboard_right">
            <div class="dashboard_content tracker">
                <h1>Progress Tracker</h1>
            <!-- Graph -->
            <div id="donutchart" class="chart"></div>

            <!-- Progress Bars -->
            <div class="progress-bars">
            <div class="progress-item">
                <progress id="complete"></progress>
                <div class="progress-footer">
                <label for="complete">Completed</label>
                <span class="progress-indicator"></span>
                </div>
            </div>

            <div class="progress-item">
                <progress id="urgent"></progress>
                <div class="progress-footer">
                <label for="urgent">Urgent</label>
                <span class="progress-indicator"></span>
                </div>
            </div>

            <div class="progress-item">
                <progress id="ongoing"></progress>
                <div class="progress-footer">
                <label for="ongoing">Ongoing</label>
                <span class="progress-indicator"></span>
                </div>
            </div>
            </div>
            
            </div>
        </div>
    </div>

    <!-- Sidebar / Offcanvas -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebar-label">
        <a class="sidebar-brand" href="../Dashboard_page/dashboard.php">
            <img src="Logo.png" alt="Logo" width="91" height="63" class="d-inline-block align-text-top">
            NU-L FAMS
        </a>

       <div class="offcanvas-body">
            <ul class="sidebar-menu">
                <li><a href="../dashboard_page/dashboard.php"><img src="./Dashboard_icon.png" alt="Dashboard_icon">Dashboard</a></li>
                <li><a href="../Submitted_reports/submittedReports.php"><img src="./Reports_icon.png" alt="Reports_icon">Reports</a></li>
                <li><a href="../Task_Management/index.php"><img src="./Task_icon.png" alt="Task_icon">Tasks</a></li>
                <li><a href="../Progress_Tracker_page/Progress_Tracker.php"><img src="./Progress_icon.png" alt="Progress_icon">Progress Tracker</a></li>
            </ul>
        </div>

    <!-- ✅ User Info Box at Bottom -->
    <div class="user-info">
        <p class="user-label">Logged in as:</p>
        <div class="user-row">
            <img src="profile.jpg" alt="Profile Icon" class="user-avatar">
            <div class="user-details">
            <p class="user-name">
                <?php echo htmlspecialchars(getUsername()); ?>
            </p>
            <a class="sign-out" href="../logout.php">Sign-out</a>
            </div>
        </div>
    </div>


    <!-- Bootstrap Bundle (for Offcanvas & JS features) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>
