function createMobileCards() {
  if (window.innerWidth <= 426) {
    const table = document.getElementById('reportsTable');
    const rows = table.querySelectorAll('tbody tr');
    
    let mobileContainer = document.querySelector('.mobile-cards-container');
    if (!mobileContainer) {
      mobileContainer = document.createElement('div');
      mobileContainer.className = 'mobile-cards-container';
      table.parentNode.insertBefore(mobileContainer, table);
    }
    
    mobileContainer.innerHTML = '';
    
    table.style.display = 'none';
    
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length === 0) return;
      
      const card = document.createElement('div');
      card.className = 'report-mobile-card';
      card.setAttribute('data-id', row.getAttribute('data-id') || '');
      
      const header = document.createElement('div');
      header.className = 'card-header';
      header.innerHTML = `<div class="report-id">Report #${cells[0].textContent}</div>`;
      card.appendChild(header);
      
      if (cells[1]) {
        const emailRow = document.createElement('div');
        emailRow.className = 'card-row';
        emailRow.innerHTML = `
          <span class="card-label">Email:</span>
          <span class="card-value">${cells[1].textContent}</span>
        `;
        card.appendChild(emailRow);
      }
      
      if (cells[2]) {
        const contactRow = document.createElement('div');
        contactRow.className = 'card-row';
        contactRow.innerHTML = `
          <span class="card-label">Contact:</span>
          <span class="card-value">${cells[2].textContent}</span>
        `;
        card.appendChild(contactRow);
      }
      
      if (cells[3]) {
        const issueRow = document.createElement('div');
        issueRow.className = 'card-row';
        issueRow.innerHTML = `
          <span class="card-label">Issue Type:</span>
          <span class="card-value">${cells[3].textContent}</span>
        `;
        card.appendChild(issueRow);
      }
      
      if (cells[4]) {
        const buildingRow = document.createElement('div');
        buildingRow.className = 'card-row';
        buildingRow.innerHTML = `
          <span class="card-label">Building:</span>
          <span class="card-value">${cells[4].textContent}</span>
        `;
        card.appendChild(buildingRow);
      }
      
      if (cells[5]) {
        const areaRow = document.createElement('div');
        areaRow.className = 'card-row';
        areaRow.innerHTML = `
          <span class="card-label">Area:</span>
          <span class="card-value">${cells[5].textContent}</span>
        `;
        card.appendChild(areaRow);
      }
      
      if (cells[6]) {
        const descRow = document.createElement('div');
        descRow.className = 'card-row';
        descRow.innerHTML = `
          <span class="card-label">Description:</span>
          <span class="card-value description">${cells[6].textContent}</span>
        `;
        card.appendChild(descRow);
      }
      
      if (cells[7]) {
        const photoRow = document.createElement('div');
        photoRow.className = 'card-row';
        photoRow.innerHTML = `
          <span class="card-label">Photo:</span>
          <span class="card-value card-photo">${cells[7].innerHTML}</span>
        `;
        card.appendChild(photoRow);
      }
      
      if (cells[8]) {
        const dateRow = document.createElement('div');
        dateRow.className = 'card-row';
        dateRow.innerHTML = `
          <span class="card-label">Date:</span>
          <span class="card-value">${cells[8].textContent}</span>
        `;
        card.appendChild(dateRow);
      }
      
      if (cells[9]) {
        const statusRow = document.createElement('div');
        statusRow.className = 'card-row';
        statusRow.innerHTML = `
          <span class="card-label">Status:</span>
          <span class="card-value">${cells[9].innerHTML}</span>
        `;
        card.appendChild(statusRow);
        
        const statusSelect = statusRow.querySelector('.statusSelect');
        if (statusSelect) {
          updateStatusClass(statusSelect);
          statusSelect.addEventListener('change', function() {
            updateStatusClass(this);
            const newStatus = this.value;
            const reportId = card.getAttribute('data-id');
            
            fetch('update_status.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `report_id=${reportId}&status=${encodeURIComponent(newStatus)}`
            })
            .then(res => res.text())
            .then(data => alert(data))
            .catch(err => console.error('Status AJAX error:', err));
          });
        }
      }
      
      mobileContainer.appendChild(card);
    });
  } else {
    const table = document.getElementById('reportsTable');
    const mobileContainer = document.querySelector('.mobile-cards-container');
    if (table) table.style.display = 'table';
    if (mobileContainer) mobileContainer.style.display = 'none';
  }
}

window.addEventListener('load', createMobileCards);
window.addEventListener('resize', createMobileCards);

function searchReports() {
  let input = document.getElementById("searchInput").value.toLowerCase();
  let table = document.getElementById("reportsTable");
  let trs = table.getElementsByTagName("tr");

  for (let i = 1; i < trs.length; i++) {
    let tds = trs[i].getElementsByTagName("td");
    let rowText = "";
    for (let j = 0; j < tds.length; j++) {
      rowText += tds[j].textContent.toLowerCase();
    }
    trs[i].style.display = rowText.includes(input) ? "" : "none";
  }
}

function updateStatusClass(selectEl) {
  selectEl.classList.remove("status-pending", "status-inprogress", "status-complete");
  if (selectEl.value === "Pending") selectEl.classList.add("status-pending");
  else if (selectEl.value === "In Progress") selectEl.classList.add("status-inprogress");
  else if (selectEl.value === "Complete") selectEl.classList.add("status-complete");
}

document.querySelectorAll(".statusSelect").forEach(select => {
  updateStatusClass(select);
  select.addEventListener("change", () => updateStatusClass(select));
});

document.querySelectorAll('.statusSelect').forEach(select => {
  select.addEventListener('change', function() {
    const newStatus = this.value;
    const reportId = this.closest('tr').dataset.id;

    fetch('update_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `report_id=${reportId}&status=${encodeURIComponent(newStatus)}`
    })
    .then(res => res.text())
    .then(data => alert(data))
    .catch(err => console.error('Status AJAX error:', err));
  });
});

function loadAdminNotifications() {
  fetch('../Dashboard_page/get_admin_notifications.php')
      .then(response => response.json())
      .then(data => {
          const badge = document.getElementById('notifBadge');
          const menu = document.getElementById('notificationMenu');
          const noNotif = document.getElementById('noNotifications');
          const markAllBtn = document.getElementById('markAllReadBtn');
          
          if (data.unread_count > 0) {
              badge.textContent = data.unread_count;
              badge.style.display = 'block';
              if (markAllBtn) markAllBtn.style.display = 'inline-block';
          } else {
              badge.style.display = 'none';
              if (markAllBtn) markAllBtn.style.display = 'none';
          }
          
          const menuItems = Array.from(menu.children);
          menuItems.forEach(item => {
              if (item.id !== 'noNotifications' && !item.querySelector('.dropdown-divider') && !item.classList.contains('dropdown-header') && !item.querySelector('.text-primary')) {
                  item.remove();
              }
          });
          
          if (data.notifications.length > 0) {
              noNotif.style.display = 'none';
              
              const header = menu.querySelector('.dropdown-header');
              
              data.notifications.forEach(notif => {
                  const li = document.createElement('li');
                  const a = document.createElement('a');
                  a.className = 'dropdown-item small';
                  a.href = notif.link;
                  
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

function markAllNotificationsAsRead() {
  fetch('../Dashboard_page/mark_all_notifications_read.php', {
      method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
      if (data.success) {
          loadAdminNotifications();
      } else {
          console.error('Failed to mark notifications as read:', data.message);
      }
  })
  .catch(error => console.error('Error marking notifications as read:', error));
}

loadAdminNotifications();
setInterval(loadAdminNotifications, 30000);

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
