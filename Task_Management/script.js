// Admin Tasks page: mobile card layout + search/filter.
// (The old "+ New" task form only added unsaved rows in the browser and has been removed.
//  Priority changes and approvals live in task_management.js.)

function createMobileCards() {
  const table = document.querySelector('table');
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

    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length === 0) return;

      const card = document.createElement('div');
      card.className = 'task-mobile-card';
      card.setAttribute('data-id', row.getAttribute('data-id') || '');
      card.setAttribute('data-request-id', row.getAttribute('data-request-id') || '');

      const header = document.createElement('div');
      header.className = 'card-header';
      header.innerHTML = `<div class="task-title">${cells[0].innerHTML}</div>`;
      card.appendChild(header);

      const addRow = (label, html) => {
        const el = document.createElement('div');
        el.className = 'card-row';
        el.innerHTML = `<span class="card-label">${label}</span><span class="card-value">${html}</span>`;
        card.appendChild(el);
        return el;
      };

      if (cells[1]) addRow('Facility:', cells[1].innerHTML);

      if (cells[2]) {
        const priorityRow = addRow('Priority:', cells[2].innerHTML);
        const prioritySelect = priorityRow.querySelector('.prioritySelect');
        if (prioritySelect) {
          prioritySelect.addEventListener('change', function () {
            const newPriority = this.value;
            const reportId = card.getAttribute('data-id');

            fetch('update_priority.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `report_id=${reportId}&priority_level=${encodeURIComponent(newPriority)}`
            })
              .then(response => response.text())
              .then(data => alert(data))
              .catch(err => console.error('AJAX error:', err));
          });
        }
      }

      if (cells[3]) addRow('Status:', cells[3].innerHTML);
      if (cells[4] && cells[4].textContent.trim() !== '-') addRow('Pending:', cells[4].innerHTML);
      if (cells[5]) addRow('Proof:', cells[5].innerHTML);
      if (cells[6]) addRow('Date:', cells[6].innerHTML);

      // Approve/Reject buttons keep their inline onclick handlers through innerHTML.
      if (cells[7] && cells[7].textContent.trim() !== '-') {
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'card-actions';
        actionsDiv.innerHTML = cells[7].innerHTML;
        card.appendChild(actionsDiv);
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

// Search and filter (works on both the table rows and the mobile cards)
function cardRowValue(card, labelText) {
  const rows = card.querySelectorAll('.card-row');
  for (const row of rows) {
    const label = row.querySelector('.card-label');
    if (label && label.textContent.trim() === labelText) {
      const value = row.querySelector('.card-value');
      return value ? value.textContent.toLowerCase() : '';
    }
  }
  return '';
}

function filterTasks() {
  const searchInput = document.getElementById('search');
  const filterSelect = document.getElementById('filter');
  if (!searchInput || !filterSelect) return;

  const searchValue = searchInput.value.toLowerCase().trim();
  const filterValue = filterSelect.value.toLowerCase().trim();

  document.querySelectorAll('#taskTableBody tr').forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length === 0) return;

    const task = cells[0].textContent.toLowerCase();
    const facility = cells[1].textContent.toLowerCase();
    const status = cells[3] ? cells[3].textContent.toLowerCase() : '';

    const matchesSearch = searchValue === '' || task.includes(searchValue) || facility.includes(searchValue);
    const matchesFilter = filterValue === '' || status.includes(filterValue);
    row.style.display = matchesSearch && matchesFilter ? '' : 'none';
  });

  document.querySelectorAll('.task-mobile-card').forEach(card => {
    const taskElement = card.querySelector('.task-title');
    if (!taskElement) return;

    const task = taskElement.textContent.toLowerCase();
    const facility = cardRowValue(card, 'Facility:');
    const status = cardRowValue(card, 'Status:');

    const matchesSearch = searchValue === '' || task.includes(searchValue) || facility.includes(searchValue);
    const matchesFilter = filterValue === '' || status.includes(filterValue);
    card.style.display = matchesSearch && matchesFilter ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('search');
  const searchBtn = document.getElementById('searchBtn');
  const filterSelect = document.getElementById('filter');

  if (searchInput) {
    searchInput.addEventListener('input', filterTasks);
    searchInput.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') filterTasks();
    });
  }
  if (searchBtn) searchBtn.addEventListener('click', filterTasks);
  if (filterSelect) filterSelect.addEventListener('change', filterTasks);
});
