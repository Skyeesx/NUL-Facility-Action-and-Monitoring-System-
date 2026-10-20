// Admin Reports page.
// Status is read-only here: a report's status changes only through the
// facility proof-of-completion request and admin approval (Tasks page).

function createMobileCards() {
  const table = document.getElementById('reportsTable');
  if (!table) return;

  if (window.innerWidth <= 426) {
    const rows = table.querySelectorAll('tbody tr');

    let mobileContainer = document.querySelector('.mobile-cards-container');
    if (!mobileContainer) {
      mobileContainer = document.createElement('div');
      mobileContainer.className = 'mobile-cards-container';
      table.parentNode.insertBefore(mobileContainer, table);
    }
    mobileContainer.style.display = '';
    mobileContainer.innerHTML = '';
    table.style.display = 'none';

    // Cell content is set with textContent so report text can never inject markup.
    const makeRow = (label, valueClass, contentFn) => {
      const rowEl = document.createElement('div');
      rowEl.className = 'card-row';
      const labelEl = document.createElement('span');
      labelEl.className = 'card-label';
      labelEl.textContent = label;
      const valueEl = document.createElement('span');
      valueEl.className = 'card-value' + (valueClass ? ' ' + valueClass : '');
      contentFn(valueEl);
      rowEl.appendChild(labelEl);
      rowEl.appendChild(valueEl);
      return rowEl;
    };

    const textFields = [
      [1, 'Email:', ''], [2, 'Contact:', ''], [3, 'Issue Type:', ''],
      [4, 'Building:', ''], [5, 'Area:', ''], [6, 'Description:', 'description']
    ];

    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length === 0) return;

      const card = document.createElement('div');
      card.className = 'report-mobile-card';
      card.setAttribute('data-id', row.getAttribute('data-id') || '');

      const header = document.createElement('div');
      header.className = 'card-header';
      const idEl = document.createElement('div');
      idEl.className = 'report-id';
      idEl.textContent = 'Report #' + cells[0].textContent;
      header.appendChild(idEl);
      card.appendChild(header);

      textFields.forEach(([index, label, cls]) => {
        if (cells[index]) {
          card.appendChild(makeRow(label, cls, el => { el.textContent = cells[index].textContent; }));
        }
      });

      // Photo and status cells are server-rendered markup, so they are cloned rather than re-parsed.
      if (cells[7]) {
        card.appendChild(makeRow('Photo:', 'card-photo', el => {
          Array.from(cells[7].childNodes).forEach(n => el.appendChild(n.cloneNode(true)));
        }));
      }
      if (cells[8]) {
        card.appendChild(makeRow('Date:', '', el => { el.textContent = cells[8].textContent; }));
      }
      if (cells[9]) {
        card.appendChild(makeRow('Status:', '', el => {
          Array.from(cells[9].childNodes).forEach(n => el.appendChild(n.cloneNode(true)));
        }));
      }

      mobileContainer.appendChild(card);
    });
  } else {
    const mobileContainer = document.querySelector('.mobile-cards-container');
    table.style.display = 'table';
    if (mobileContainer) mobileContainer.style.display = 'none';
  }
}

window.addEventListener('load', createMobileCards);
window.addEventListener('resize', createMobileCards);

function searchReports() {
  const input = document.getElementById('searchInput').value.toLowerCase();
  const trs = document.querySelectorAll('#reportsTable tbody tr');

  trs.forEach(tr => {
    const tds = tr.getElementsByTagName('td');
    if (tds.length === 0) return;
    let rowText = '';
    for (let j = 0; j < tds.length; j++) {
      rowText += tds[j].textContent.toLowerCase() + ' ';
    }
    tr.style.display = rowText.includes(input) ? '' : 'none';
  });

  document.querySelectorAll('.report-mobile-card').forEach(card => {
    card.style.display = card.textContent.toLowerCase().includes(input) ? '' : 'none';
  });
}

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

      Array.from(menu.children).forEach(item => {
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
          if (notif.type === 'new_report') icon = '📝';
          else if (notif.type === 'approval_request') icon = '✅';

          a.textContent = icon + ' ' + notif.message;
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
  fetch('../Dashboard_page/mark_all_notifications_read.php', { method: 'POST' })
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

document.addEventListener('DOMContentLoaded', function () {
  const markAllBtn = document.getElementById('markAllReadBtn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      markAllNotificationsAsRead();
    });
  }
});
