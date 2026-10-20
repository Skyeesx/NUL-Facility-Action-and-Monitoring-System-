<?php
require_once __DIR__ . '/../../connection/session_config.php';
require_once __DIR__ . '/../../connection/db_connection.php';

// Require facility staff access
requireFacility();
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
                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">0</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow notif-menu" aria-labelledby="notifDropdown" id="notificationList">
                    <li class="dropdown-header fw-bold">Notifications</li>
                    <li id="noNotifications"><span class="dropdown-item small text-muted">No new notifications</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center text-primary small" href="#" onclick="markAllAsRead()">Mark all as read</a></li>
                </ul>
            </div>


            <!-- 👤 Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="profile.jpg" alt="Profile" class="rounded-circle" width="40" height="40">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item text-danger" href="../../logout.php">Sign Out</a></li>
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
            <option value="">Filter by Facility</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Housekeeping">Housekeeping</option>
            <option value="Security">Security</option>
          </select>
        </div>
      </div>

      <!-- TABLE -->
      <table>
        <thead>
          <tr>
            <th>Task</th>
            <th>Facility</th>
            <th>Priority</th>
            <th>Date Reported</th>
            <th>Proof of Completion</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="taskTableBody">
          <!-- Dynamic rows will be inserted here -->
           <?php
            // Fetch tasks with status change approval info
            $sql = "SELECT r.* FROM report r ORDER BY r.report_date DESC";        
            $result = $conn->query($sql); 
            while ($row = $result->fetch_assoc()) {
              // Check if proof has been uploaded
              $hasProof = !empty($row['proof_of_completion']);
              $isApproved = ($row['status'] === 'Complete');
              
              // Disable status dropdown if no proof OR if already approved
              $disabledAttr = (!$hasProof || $isApproved) ? ' disabled' : '';
              
              // Disable upload button if approved
              $uploadDisabled = $isApproved ? ' disabled' : '';
              $uploadBtnClass = $isApproved ? 'uploadProofBtn disabled-btn' : 'uploadProofBtn';
              
              echo "<tr data-id='" . $row['report_id'] . "'>";
              echo "<td>" . htmlspecialchars($row['details']) . "</td>";
              echo "<td>" . htmlspecialchars($row['issue_type']) . "</td>";
              echo "<td>" . htmlspecialchars($row['priority_level']) . "</td>";
              echo "<td>" . htmlspecialchars($row['report_date']) . "</td>";
              
              // Proof of Completion Column - show image if exists and approved
              echo "<td>";
              if ($hasProof && $isApproved) {
                  // Show the proof image/video
                  $proofPath = "uploads/" . htmlspecialchars($row['proof_of_completion']);
                  $fileExtension = strtolower(pathinfo($row['proof_of_completion'], PATHINFO_EXTENSION));
                  
                  // Check if it's a video
                  if (in_array($fileExtension, ['mp4', 'mov', 'avi', 'webm', 'mpeg'])) {
                      echo "<video class='proof-video' controls>
                              <source src='" . $proofPath . "' type='video/" . ($fileExtension === 'mov' ? 'quicktime' : $fileExtension) . "'>
                              Your browser does not support the video tag.
                            </video>";
                      echo "<span class='approved-badge'>✓ Approved</span>";
                  } else {
                      // It's an image
                      echo "<a href='" . $proofPath . "' target='_blank'><img src='" . $proofPath . "' alt='Proof' class='proof-thumbnail'></a>";
                      echo "<span class='approved-badge'>✓ Approved</span>";
                  }
              } else {
                  // Show upload button
                  echo "<button class='" . $uploadBtnClass . "' data-report-id='" . $row['report_id'] . "'" . $uploadDisabled . ">" . ($isApproved ? 'Completed' : 'Upload') . "</button>";
              }
              echo "</td>";
              
              // Status Column
              echo "<td>
                        <select class='statusSelect' name='status_" . $row['report_id'] . "' id='status_" . $row['report_id'] . "' data-current-status='" . htmlspecialchars($row['status']) . "' style='display: block !important; visibility: visible !important;'" . $disabledAttr . ">
                          <option value='Pending'" . ($row['status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                          <option value='In Progress'" . ($row['status'] == 'In Progress' ? ' selected' : '') . ">In Progress</option>
                          <option value='Complete'" . ($row['status'] == 'Complete' ? ' selected' : '') . ">Complete</option>
                        </select>
                      </td>";
              echo "</tr>";
              }

            ?>
        </tbody>
      </table>
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
          <li><a href="../Dashboard_page/Dashboard.php"><img src="./Dashboard_icon.png" alt="Dashboard_icon">Dashboard</a></li>
          <li><a href="../Task_Management/index.php"><img src="./Task_icon.png" alt="Task_icon">Tasks</a></li>
      </ul>
    </div>

    <!-- User Info -->
    <div class="user-info">
      <p class="user-label">Logged in as:</p>
      <div class="user-row">
        <img src="profile.jpg" alt="Profile Icon" class="user-avatar">
        <div class="user-details">
          <p class="user-name">Username</p>
          <a class="sign-out" href="../../logout.php">Sign-out</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle (for Offcanvas) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  
  <script>
    // Fetch and display notifications
    function loadNotifications() {
      fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const badge = document.getElementById('notifBadge');
            const notifList = document.getElementById('notificationList');
            const noNotif = document.getElementById('noNotifications');
            
            // Update badge count
            if (data.unread_count > 0) {
              badge.textContent = data.unread_count;
              badge.style.display = 'flex';
            } else {
              badge.style.display = 'none';
            }
            
            // Clear existing notifications (except header and footer)
            const existingItems = notifList.querySelectorAll('li:not(.dropdown-header):not(:has(.dropdown-divider)):not(:last-child)');
            existingItems.forEach(item => {
              if (item.id !== 'noNotifications' && !item.querySelector('.dropdown-divider')) {
                item.remove();
              }
            });
            
            // Add notifications
            if (data.notifications.length > 0) {
              noNotif.style.display = 'none';
              const divider = notifList.querySelector('.dropdown-divider').parentElement;
              
              data.notifications.forEach(notif => {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.className = 'dropdown-item small' + (notif.is_read ? '' : ' fw-bold');
                a.href = '#';
                a.innerHTML = notif.message;
                a.onclick = function(e) {
                  e.preventDefault();
                  markNotificationAsRead(notif.id);
                };
                li.appendChild(a);
                notifList.insertBefore(li, divider);
              });
            } else {
              noNotif.style.display = 'block';
            }
          }
        })
        .catch(error => console.error('Error loading notifications:', error));
    }
    
    // Mark notification as read
    function markNotificationAsRead(notificationId) {
      fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `notification_id=${notificationId}`
      })
      .then(() => loadNotifications());
    }
    
    // Mark all as read
    function markAllAsRead() {
      // You can implement this if needed
      alert('Mark all as read functionality');
    }
    
    // Load notifications on page load
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
  </script>
</body>
</html>
