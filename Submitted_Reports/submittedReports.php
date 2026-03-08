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
  <title>Submitted Reports</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="srstyle.css">
  <link href="https://fonts.googleapis.com/css2?family=Bungee+Inline&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@100..700&display=swap" rel="stylesheet">

  <!-- Bootstrap JS for sidebar -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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

            <a class="navbar-brand d-flex align-items-center" href="../Dashboard_page/Dashboard.php">
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
                    <li class="dropdown-header fw-bold d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <button id="markAllReadBtn" class="btn btn-sm btn-link text-primary p-0 small" style="display:none; font-size: 11px; text-decoration: none;">Mark all as read</button>
                    </li>
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
                <li><a class="dropdown-item text-danger" href="../logout.php">Sign Out</a></li>
                </ul>
            </div>
            </div>
        </div>
    </nav>

  <!-- Sidebar / Offcanvas -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
    <a class="sidebar-brand" href="../Dashboard_page/Dashboard.php">
      <img src="Logo.png" alt="Logo" width="91" height="63" class="d-inline-block align-text-top">
      NU-L FAMS
    </a>

    <div class="offcanvas-body">
      <ul class="sidebar-menu">
        <li><a href="../dashboard_page/dashboard.php"><img src="./Dashboard_icon.png">Dashboard</a></li>
        <li><a href="../Submitted_reports/submittedReports.php"><img src="./Reports_icon.png">Reports</a></li>
        <li><a href="../Task_Management/index.php"><img src="./Task_icon.png">Tasks</a></li>
        <li><a href="../Progress_Tracker_page/Progress_Tracker.php"><img src="./Progress_icon.png">Progress Tracker</a></li>
      </ul>
    </div>

    <!-- User Info -->
    <div class="user-info">
      <p class="user-label">Logged in as:</p>
      <div class="user-row">
        <img src="profile.jpg" alt="Profile Icon" class="user-avatar">
        <div class="user-details">
          <p class="user-name">Username</p>
          <a class="sign-out" href="../logout.php">Sign-out</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Report Table -->
  <div class="reportTab">
    <div class="report-table">
      <div class="header">
        <h1>Submitted Reports</h1>
        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Search reports...">
          <button onclick="searchReports()">Search</button>
        </div>
      </div>

      <table id="reportsTable">
        <thead>
          <tr>
            <th>Report ID</th>
            <th>Email</th>
            <th>Contact Number</th>
            <th>Issue Type</th>
            <th>Building</th>
            <th>Area</th>
            <th>Description</th>
            <th>Photo</th>
            <th>Date Reported</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="reportTableBody">
          <tr>
            <?php
            // Fetch reports from the database
            $sql = "SELECT * FROM report ORDER BY report_date DESC";
            $result = $conn->query($sql); 
            while ($row = $result->fetch_assoc()) {
                echo "<tr data-id='" . $row['report_id'] . "'>";
                echo "<td>" . htmlspecialchars($row['report_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                echo "<td>" . htmlspecialchars($row['issue_type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['building_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                echo "<td>" . htmlspecialchars($row['details']) . "</td>";
                
                // Photo with proper path and thumbnail styling
                $photoPath = !empty($row['photo_path']) ? htmlspecialchars($row['photo_path']) : '';
                if ($photoPath) {
                    // Ensure path is correct (relative to Submit_report folder)
                    $fullPhotoPath = "../Submit_report/" . $photoPath;
                    echo "<td><a href='" . $fullPhotoPath . "' target='_blank'><img src='" . $fullPhotoPath . "' alt='Report Photo' class='report-photo-thumbnail'></a></td>";
                } else {
                    echo "<td>No photo</td>";
                }
                
                echo "<td>" . htmlspecialchars($row['report_date']) . "</td>";
                
                // Status dropdown with proper class
                $statusClass = '';
                switch($row['status']) {
                    case 'Pending':
                        $statusClass = 'status-pending';
                        break;
                    case 'In Progress':
                        $statusClass = 'status-inprogress';
                        break;
                    case 'Complete':
                        $statusClass = 'status-complete';
                        break;
                }
                
                echo "<td>
                        <select class='statusSelect " . $statusClass . "' data-report-id='" . $row['report_id'] . "' onchange='updateStatus(this)'>
                          <option value='Pending'" . ($row['status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                          <option value='In Progress'" . ($row['status'] == 'In Progress' ? ' selected' : '') . ">In Progress</option>
                          <option value='Complete'" . ($row['status'] == 'Complete' ? ' selected' : '') . ">Complete</option>
                        </select>
                      </td>";
                echo "</tr>";
            }

            ?>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script src="submitted_reports.js"></script>
</body>
</html>
