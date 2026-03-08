// Get elements
const modal = document.getElementById("taskModal");
const newTaskBtn = document.getElementById("newTaskBtn");
const closeBtn = document.querySelector(".closeBtn");
const taskForm = document.getElementById("taskForm");
const taskTableBody = document.getElementById("taskTableBody");

function createMobileCards() {
  if (window.innerWidth <= 426) {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tbody tr');
    
    let mobileContainer = document.querySelector('.mobile-cards-container');
    if (!mobileContainer) {
      mobileContainer = document.createElement('div');
      mobileContainer.className = 'mobile-cards-container';
      table.parentNode.insertBefore(mobileContainer, table);
    }
    
    // Clear existing cards
    mobileContainer.innerHTML = '';
    
    // Hide table on mobile
    table.style.display = 'none';
    
    // Convert each row to a card
    rows.forEach(row => {
      const cells = row.querySelectorAll('td');
      if (cells.length === 0) return;
      
      const card = document.createElement('div');
      card.className = 'task-mobile-card';
      card.setAttribute('data-id', row.getAttribute('data-id') || '');
      card.setAttribute('data-request-id', row.getAttribute('data-request-id') || '');
      
      // Header with task title
      const header = document.createElement('div');
      header.className = 'card-header';
      header.innerHTML = `<div class="task-title">${cells[0].innerHTML}</div>`;
      card.appendChild(header);
      
      // Facility
      if (cells[1]) {
        const facilityRow = document.createElement('div');
        facilityRow.className = 'card-row';
        facilityRow.innerHTML = `
          <span class="card-label">Facility:</span>
          <span class="card-value">${cells[1].innerHTML}</span>
        `;
        card.appendChild(facilityRow);
      }
      
      // Priority
      if (cells[2]) {
        const priorityRow = document.createElement('div');
        priorityRow.className = 'card-row';
        priorityRow.innerHTML = `
          <span class="card-label">Priority:</span>
          <span class="card-value">${cells[2].innerHTML}</span>
        `;
        card.appendChild(priorityRow);
        
        // Re-attach priority change event listener
        const prioritySelect = priorityRow.querySelector('.prioritySelect');
        if (prioritySelect) {
          prioritySelect.addEventListener('change', function() {
            const newPriority = this.value;
            const reportId = card.getAttribute('data-id');

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
        }
      }
      
      // Current Status
      if (cells[3]) {
        const statusRow = document.createElement('div');
        statusRow.className = 'card-row';
        statusRow.innerHTML = `
          <span class="card-label">Status:</span>
          <span class="card-value">${cells[3].innerHTML}</span>
        `;
        card.appendChild(statusRow);
      }
      
      // Pending Status
      if (cells[4] && cells[4].textContent.trim() !== '-') {
        const pendingRow = document.createElement('div');
        pendingRow.className = 'card-row';
        pendingRow.innerHTML = `
          <span class="card-label">Pending:</span>
          <span class="card-value">${cells[4].innerHTML}</span>
        `;
        card.appendChild(pendingRow);
      }
      
      // Proof
      if (cells[5]) {
        const proofRow = document.createElement('div');
        proofRow.className = 'card-row';
        proofRow.innerHTML = `
          <span class="card-label">Proof:</span>
          <span class="card-value">${cells[5].innerHTML}</span>
        `;
        card.appendChild(proofRow);
      }
      
      // Date
      if (cells[6]) {
        const dateRow = document.createElement('div');
        dateRow.className = 'card-row';
        dateRow.innerHTML = `
          <span class="card-label">Date:</span>
          <span class="card-value">${cells[6].textContent}</span>
        `;
        card.appendChild(dateRow);
      }
      
      // Actions (Approve/Reject buttons)
      // Note: onclick handlers in HTML are preserved through innerHTML
      if (cells[7] && cells[7].textContent.trim() !== '-') {
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'card-actions';
        actionsDiv.innerHTML = cells[7].innerHTML;
        card.appendChild(actionsDiv);
      }
      
      mobileContainer.appendChild(card);
    });
  } else {
    // Desktop view - show table
    const table = document.querySelector('table');
    const mobileContainer = document.querySelector('.mobile-cards-container');
    if (table) table.style.display = 'table';
    if (mobileContainer) mobileContainer.style.display = 'none';
  }
}

// Run on load and resize
window.addEventListener('load', createMobileCards);
window.addEventListener('resize', createMobileCards);

// Open modal
newTaskBtn.onclick = () => {
  modal.style.display = "flex";
};

// Close modal
closeBtn.onclick = () => {
  modal.style.display = "none";
};
window.onclick = (event) => {
  if (event.target === modal) {
    modal.style.display = "none";
  }
};

// Function to update status color class
function updateStatusClass(selectEl) {
  selectEl.classList.remove("status-pending", "status-inprogress", "status-complete");

  if (selectEl.value === "Pending") {
    selectEl.classList.add("status-pending");
  } else if (selectEl.value === "In Progress") {
    selectEl.classList.add("status-inprogress");
  } else if (selectEl.value === "Complete") {
    selectEl.classList.add("status-complete");
  }
}


// Handle form submission
taskForm.onsubmit = (e) => {
  e.preventDefault();

  const task = document.getElementById("taskInput").value;
  const facility = document.getElementById("facilityInput").value;
  const status = document.getElementById("statusInput").value;
  const date = document.getElementById("dateInput").value;

  // Create new row
  const row = document.createElement("tr");
  row.innerHTML = `
    <td>${task}</td>
    <td>${facility}</td>
    <td>
      <select class="prioritySelect">
        <option>🟢 Low</option>
        <option>🟠 Medium</option>
        <option>🔴 Urgent</option>
      </select>
    </td>
    <td>
      <select class="statusSelect">
        <option ${status === "Pending" ? "selected" : ""}>Pending</option>
        <option ${status === "In Progress" ? "selected" : ""}>In Progress</option>
        <option ${status === "Complete" ? "selected" : ""}>Complete</option>
      </select>
    </td>
    <td>${date}</td>
    <td>
      <button class="deleteBtn">Delete</button>
    </td>
  `;

  // Add delete functionality
  row.querySelector(".deleteBtn").onclick = () => row.remove();

  // ✅ Add status color handling for this row
  const statusSelect = row.querySelector(".statusSelect");
  updateStatusClass(statusSelect);
  statusSelect.addEventListener("change", () => updateStatusClass(statusSelect));

  // Append row to table
  taskTableBody.appendChild(row);

  // Close modal + reset form
  modal.style.display = "none";
  taskForm.reset();
};

// Search and Filter Functionality
function filterTasks() {
  const searchInput = document.getElementById('search');
  const filterSelect = document.getElementById('filter');
  
  if (!searchInput || !filterSelect) {
    console.error('Search or filter elements not found');
    return;
  }
  
  const searchValue = searchInput.value.toLowerCase().trim();
  const filterValue = filterSelect.value.toLowerCase().trim();
  
  const tableRows = document.querySelectorAll('#taskTableBody tr');
  const mobileCards = document.querySelectorAll('.task-mobile-card');
  
  // Filter table rows (desktop view)
  tableRows.forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length === 0) return;
    
    const task = cells[0].textContent.toLowerCase(); // Task/Details
    const facility = cells[1].textContent.toLowerCase(); // Facility/Issue Type
    const status = cells[3] ? cells[3].textContent.toLowerCase() : ''; // Status
    
    // Check if row matches search and filter criteria
    const matchesSearch = searchValue === '' || 
                         task.includes(searchValue) || 
                         facility.includes(searchValue);
    const matchesFilter = filterValue === '' || status.includes(filterValue);
    
    if (matchesSearch && matchesFilter) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
  
  // Filter mobile cards (mobile view)
  mobileCards.forEach(card => {
    const facilityElement = card.querySelector('.card-row:nth-child(2) .card-value');
    const taskElement = card.querySelector('.task-title');
    const statusElement = card.querySelector('.card-row:has(.card-label:contains("Status:")) .card-value');
    
    if (!facilityElement || !taskElement) return;
    
    const task = taskElement.textContent.toLowerCase();
    const facility = facilityElement.textContent.toLowerCase();
    const status = statusElement ? statusElement.textContent.toLowerCase() : '';
    
    const matchesSearch = searchValue === '' || 
                         task.includes(searchValue) || 
                         facility.includes(searchValue);
    const matchesFilter = filterValue === '' || status.includes(filterValue);
    
    if (matchesSearch && matchesFilter) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}

// Attach event listeners for search and filter
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('search');
  const searchBtn = document.getElementById('searchBtn');
  const filterSelect = document.getElementById('filter');
  
  if (searchInput) {
    searchInput.addEventListener('input', filterTasks);
    // Also trigger search on Enter key
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        filterTasks();
      }
    });
  }
  
  if (searchBtn) {
    searchBtn.addEventListener('click', filterTasks);
  }
  
  if (filterSelect) {
    filterSelect.addEventListener('change', filterTasks);
  }
});
