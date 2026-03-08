// Priority select change handler
document.querySelectorAll('.prioritySelect').forEach(select => {
  select.addEventListener('change', function() {
    const newPriority = this.value;
    const reportId = this.closest('tr').dataset.id;

    fetch('update_priority.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `report_id=${reportId}&priority_level=${encodeURIComponent(newPriority)}`
    })
    .then(response => response.text())
    .then(data => {
      console.log('Server response:', data);
      alert(data);
    })
    .catch(err => console.error('AJAX error:', err));
  });
});

// Function to approve status change
function approveStatusChange(requestId, reportId, newStatus) {
  if (!confirm('Are you sure you want to approve this status change?')) {
    return;
  }

  fetch('approve_status_change.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `request_id=${requestId}&report_id=${reportId}&new_status=${encodeURIComponent(newStatus)}&action=approve`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Status change approved successfully!');
      location.reload();
    } else {
      alert('Failed to approve: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(err => {
    console.error('Approval error:', err);
    alert('An error occurred. Please try again.');
  });
}

// Function to reject status change
function rejectStatusChange(requestId, reportId) {
  document.getElementById('rejectRequestId').value = requestId;
  document.getElementById('rejectReportId').value = reportId;
  document.getElementById('rejectionModal').style.display = 'flex';
}

// Close rejection modal
document.querySelector('.closeRejectionBtn').onclick = function() {
  document.getElementById('rejectionModal').style.display = 'none';
  document.getElementById('rejectionFeedback').value = '';
};

// Handle rejection form submission
document.getElementById('rejectionForm').onsubmit = function(e) {
  e.preventDefault();
  
  const requestId = document.getElementById('rejectRequestId').value;
  const reportId = document.getElementById('rejectReportId').value;
  const feedback = document.getElementById('rejectionFeedback').value;

  if (!feedback.trim()) {
    alert('Please provide feedback for rejection.');
    return;
  }

  fetch('approve_status_change.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `request_id=${requestId}&report_id=${reportId}&action=reject&feedback=${encodeURIComponent(feedback)}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Status change request rejected with feedback sent to facility staff.');
      document.getElementById('rejectionModal').style.display = 'none';
      location.reload();
    } else {
      alert('Failed to reject: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(err => {
    console.error('Rejection error:', err);
    alert('An error occurred. Please try again.');
  });
};

// Load admin notifications
function loadAdminNotifications() {
  fetch('../Dashboard_page/get_admin_notifications.php')
      .then(response => response.json())
      .then(data => {
          const badge = document.getElementById('notifBadge');
          const menu = document.getElementById('notificationMenu');
          const noNotif = document.getElementById('noNotifications');
          const markAllBtn = document.getElementById('markAllReadBtn');
          
          // Update badge
          if (data.unread_count > 0) {
              badge.textContent = data.unread_count;
              badge.style.display = 'block';
              if (markAllBtn) markAllBtn.style.display = 'inline-block';
          } else {
              badge.style.display = 'none';
              if (markAllBtn) markAllBtn.style.display = 'none';
          }
          
          // Clear existing notifications (keep header, no notif message, and footer)
          const menuItems = Array.from(menu.children);
          menuItems.forEach(item => {
              if (item.id !== 'noNotifications' && !item.querySelector('.dropdown-divider') && !item.classList.contains('dropdown-header') && !item.querySelector('.text-primary')) {
                  item.remove();
              }
          });
          
          // Add notifications
          if (data.notifications.length > 0) {
              noNotif.style.display = 'none';
              
              // Find the position to insert (after header, before divider)
              const header = menu.querySelector('.dropdown-header');
              
              data.notifications.forEach(notif => {
                  const li = document.createElement('li');
                  const a = document.createElement('a');
                  a.className = 'dropdown-item small';
                  a.href = notif.link;
                  
                  // Add icon based on type
                  let icon = '📋';
                  if (notif.type === 'new_report') {
                      icon = '📝';
                  } else if (notif.type === 'approval_request') {
                      icon = '✅';
                  }
                  
                  a.innerHTML = icon + ' ' + notif.message;
                  li.appendChild(a);
                  header.after(li);
              });
          } else {
              noNotif.style.display = 'block';
          }
      })
      .catch(error => console.error('Error loading notifications:', error));
}

// Mark all notifications as read
function markAllNotificationsAsRead() {
  fetch('../Dashboard_page/mark_all_notifications_read.php', {
      method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          // Reload notifications immediately
          loadAdminNotifications();
      } else {
          console.error('Failed to mark notifications as read:', data.message);
      }
  })
  .catch(error => console.error('Error marking notifications as read:', error));
}

// Load notifications on page load and refresh every 30 seconds
loadAdminNotifications();
setInterval(loadAdminNotifications, 30000);

// Attach event listener to Mark All Read button
document.addEventListener('DOMContentLoaded', function() {
  const markAllBtn = document.getElementById('markAllReadBtn');
  if (markAllBtn) {
      markAllBtn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          markAllNotificationsAsRead();
      });
  }
});