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
  <title>Task Management</title>

  <!-- Bootstrap CSS (needed for sidebar) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
  
  <link href="https://fonts.googleapis.com/css2?family=Bungee+Inline&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@100..700&display=swap" rel="stylesheet">
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

  <!-- Task Tab -->
  <div class="taskTab">
    <div class="task-page">
      <div class="header">
        <h1>Tasks</h1>
        <div class="controls">
          <input type="text" id="search" placeholder="Search...">
          <button id="searchBtn">Search</button>
          <select  id="filter">
            <option value="">Filter by Status</option>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Complete">Complete</option>
          </select>
          <button id="newTaskBtn"> + New</button>
        </div>
      </div>

      <!-- TABLE -->
      <table>
        <thead>
          <tr>
            <th>Task/Report</th>
            <th>Facility</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Pending Status</th>
            <th>Proof of Completion</th>
            <th>Date Reported</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="taskTableBody">
          <tr>
            <?php
            // Fetch tasks with pending status change requests
            $sql = "SELECT r.*, 
                    scr.new_status as pending_status, 
                    scr.request_id,
                    scr.approval_status,
                    scr.request_date
                    FROM report r
                    LEFT JOIN status_change_requests scr ON r.report_id = scr.report_id AND scr.approval_status = 'pending'
                    ORDER BY r.report_date DESC";
            $result = $conn->query($sql); 
            while ($row = $result->fetch_assoc()) {
              $hasPendingRequest = !empty($row['request_id']);
              $hasProof = !empty($row['proof_of_completion']);
              
              echo "<tr data-id='" . $row['report_id'] . "' data-request-id='" . ($row['request_id'] ?? '') . "'>";
              echo "<td>" . htmlspecialchars($row['details']) . "</td>";
              echo "<td>" . htmlspecialchars($row['issue_type']) . "</td>";
              echo "<td>
                      <select class='prioritySelect'>
                        <option value='Low'" . ($row['priority_level'] == 'Low' ? ' selected' : '') . ">🟢 Low</option>
                        <option value='Medium'" . ($row['priority_level'] == 'Medium' ? ' selected' : '') . ">🟠 Medium</option>
                        <option value='High'" . ($row['priority_level'] == 'High' ? ' selected' : '') . ">🔴 High</option>
                      </select>
                    </td>";
              echo "<td><span class='current-status status-badge status-" . strtolower(str_replace(' ', '', $row['status'])) . "'>" . htmlspecialchars($row['status']) . "</span></td>";
              
              // Pending Status Column
              if ($hasPendingRequest) {
                  echo "<td><span class='pending-status status-badge status-" . strtolower(str_replace(' ', '', $row['pending_status'])) . "'>" . htmlspecialchars($row['pending_status']) . "</span></td>";
              } else {
                  echo "<td>-</td>";
              }
              
              // Proof of Completion Column
              if ($hasProof) {
                  $proofPath = "../Facility_POV/Task_Management/uploads/" . htmlspecialchars($row['proof_of_completion']);
                  $fileExtension = strtolower(pathinfo($row['proof_of_completion'], PATHINFO_EXTENSION));
                  
                  // Check if it's a video
                  if (in_array($fileExtension, ['mp4', 'mov', 'avi', 'webm', 'mpeg'])) {
                      echo "<td>
                              <video class='proof-video' controls>
                                <source src='" . $proofPath . "' type='video/" . ($fileExtension === 'mov' ? 'quicktime' : $fileExtension) . "'>
                                Your browser does not support the video tag.
                              </video>
                            </td>";
                  } else {
                      // It's an image
                      echo "<td><a href='" . $proofPath . "' target='_blank'><img src='" . $proofPath . "' alt='Proof' class='proof-thumbnail'></a></td>";
                  }
              } else {
                  echo "<td class='no-proof'>No proof uploaded</td>";
              }
              
              echo "<td>" . htmlspecialchars($row['report_date']) . "</td>";
              
              // Action Column
              if ($hasPendingRequest) {
                  echo "<td>
                          <button class='approve-btn' onclick='approveStatusChange(" . $row['request_id'] . ", " . $row['report_id'] . ", \"" . htmlspecialchars($row['pending_status']) . "\")'>Approve</button>
                          <button class='reject-btn' onclick='rejectStatusChange(" . $row['request_id'] . ", " . $row['report_id'] . ")'>Reject</button>
                        </td>";
              } else {
                  echo "<td>-</td>";
              }
              
              echo "</tr>";
              }

            ?>
          </tr> 
        </tbody>
      </table>
    </div>

    <!-- POP UP TAB -->
    <div id="taskModal" class="modal">
      <div class="modal-content">
        <span class="closeBtn">&times;</span>
        <h2>Add a New Task</h2>
        <form id="taskForm">
          <div class="inputForm">
            <label>Task:</label>
            <input type="text" id="taskInput" required>
          </div>

          <div class="inputForm">
            <label>Facility in Charge:</label>
            <select id="facilityInput" required>
              <option value=""></option>
              <option value="Maintenance">Maintenance</option>
              <option value="Housekeeping">Housekeeping</option>
              <option value="Security">Security</option>
            </select>
          </div>

          <div class="inputForm">
            <label>Status:</label>
            <select id="statusInput">
              <option value=""></option>
              <option value="Pending">Pending</option> 
              <option value="In Progress">In Progress</option>
              <option value="Complete">Complete</option>
            </select>
          </div>

          <div class="inputForm">
            <label>Date:</label>
            <input type="date" id="dateInput" required>
          </div>

          <span class="submitButton"><button type="submit">Add</button></span>
        </form>
      </div>
    </div>

    <!-- Rejection Feedback Modal -->
    <div id="rejectionModal" class="modal" style="display: none;">
      <div class="modal-content">
        <span class="closeRejectionBtn">&times;</span>
        <h2>Rejection Feedback</h2>
        <form id="rejectionForm">
          <div class="inputForm">
            <label>Reason for Rejection:</label>
            <textarea id="rejectionFeedback" rows="5" placeholder="Please provide feedback to the facility staff..." required></textarea>
          </div>
          <input type="hidden" id="rejectRequestId">
          <input type="hidden" id="rejectReportId">
          <span class="submitButton">
            <button type="submit" class="reject-submit-btn">Submit Rejection</button>
          </span>
        </form>
      </div>
    </div>
  </div>

  <!-- Sidebar / Offcanvas -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebar-label">
    <a class="sidebar-brand" href="../Dashboard_page/Dashboard.php">
      <img src="Logo.png" alt="Logo" width="91" height="63" class="d-inline-block align-text-top">
      NU-L FAMS
    </a>

    <div class="offcanvas-body">
      <ul class="sidebar-menu">
        <li><a href="../dashboard_page/Dashboard.php"><img src="./Dashboard_icon.png" alt="Dashboard_icon">Dashboard</a></li>
        <li><a href="../Submitted_Reports/submittedReports.php"><img src="./Reports_icon.png" alt="Reports_icon">Reports</a></li>
        <li><a href="../Task_Management/index.php"><img src="./Task_icon.png" alt="Task_icon">Tasks</a></li>
        <li><a href="../Progress_Tracker_page/Progress_Tracker.php"><img src="./Progress_icon.png" alt="Progress_icon">Progress Tracker</a></li>
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

  <!-- Bootstrap Bundle (for Offcanvas) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  <script src="task_management.js"></script>
</body>
</html>