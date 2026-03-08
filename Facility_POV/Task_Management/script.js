const modal = document.getElementById("taskModal");
const newTaskBtn = document.getElementById("newTaskBtn");
const closeBtn = document.querySelector(".closeBtn");
const taskForm = document.getElementById("taskForm");
const taskTableBody = document.getElementById("taskTableBody");

function createMobileCards() {
  if (window.innerWidth <= 426) {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tbody tr');
    
    // Create container for mobile cards if it doesn't exist
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
      }
      
      // Date Reported
      if (cells[3]) {
        const dateRow = document.createElement('div');
        dateRow.className = 'card-row';
        dateRow.innerHTML = `
          <span class="card-label">Date:</span>
          <span class="card-value">${cells[3].textContent}</span>
        `;
        card.appendChild(dateRow);
      }
      
      // Proof Upload
      if (cells[4]) {
        const proofRow = document.createElement('div');
        proofRow.className = 'card-row';
        proofRow.innerHTML = `
          <span class="card-label">Proof:</span>
          <span class="card-value">${cells[4].innerHTML}</span>
        `;
        card.appendChild(proofRow);
        
        const uploadBtn = proofRow.querySelector('.uploadProofBtn');
        if (uploadBtn) {
          uploadBtn.addEventListener('click', function() {
            handleProofUpload(this);
          });
        }
      }
      
      // Status
      if (cells[5]) {
        const statusRow = document.createElement('div');
        statusRow.className = 'card-row';
        statusRow.innerHTML = `
          <span class="card-label">Status:</span>
          <span class="card-value">${cells[5].innerHTML}</span>
        `;
        card.appendChild(statusRow);
        
        const statusSelect = statusRow.querySelector('.statusSelect');
        if (statusSelect) {
          updateStatusClass(statusSelect);
          statusSelect.addEventListener("change", function() { 
            updateStatusClass(this);
            updateStatusInDatabase(this);
          });
        }
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

if (newTaskBtn && modal) {
  newTaskBtn.onclick = () => {
    modal.style.display = "flex";
  };
}

if (closeBtn && modal) {
  closeBtn.onclick = () => {
    modal.style.display = "none";
  };
}

if (modal) {
  window.onclick = (event) => {
    if (event.target === modal) {
      modal.style.display = "none";
    }
  };
}

function updateStatusClass(select) {
  select.classList.remove("status-pending", "status-inprogress", "status-complete");

  const value = select.value.toLowerCase().replace(/\s+/g, "");
  select.classList.add(`status-${value}`);
}


// Handle form submission
if (taskForm && taskTableBody) {
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


    row.querySelector(".deleteBtn").onclick = () => row.remove();

    const newStatusSelect = row.querySelector(".statusSelect");
    updateStatusClass(newStatusSelect);
    newStatusSelect.addEventListener("change", () => updateStatusClass(newStatusSelect));

    taskTableBody.appendChild(row);

    modal.style.display = "none";
    taskForm.reset();
  };
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("#taskTableBody .statusSelect").forEach(select => {
    updateStatusClass(select); // apply initial color
    select.addEventListener("change", function() { 
      updateStatusClass(this);
      updateStatusInDatabase(this); // Update status in database when changed
    }); 
  });

  document.querySelectorAll(".uploadProofBtn").forEach(btn => {
    btn.addEventListener("click", function() {
      handleProofUpload(this);
    });
  });
});

if (document.readyState === 'loading') {
} else {
  // DOM is ready
  document.querySelectorAll("#taskTableBody .statusSelect").forEach(select => {
    updateStatusClass(select);
    select.addEventListener("change", function() { 
      updateStatusClass(this);
      updateStatusInDatabase(this);
    });
  });

  // Handle upload button clicks
  document.querySelectorAll(".uploadProofBtn").forEach(btn => {
    btn.addEventListener("click", function() {
      handleProofUpload(this);
    });
  });
}

function handleProofUpload(button) {
  // Check if button is disabled
  if (button.disabled || button.classList.contains('disabled-btn')) {
    return;
  }
  
  const reportId = button.getAttribute('data-report-id');
  const container = button.closest('tr') || button.closest('.task-mobile-card');
  const statusSelect = container ? container.querySelector('.statusSelect') : null;
  
  // Create file input
  const fileInput = document.createElement('input');
  fileInput.type = 'file';
  fileInput.accept = 'image/*,video/*';
  
  fileInput.onchange = function(e) {
    const file = e.target.files[0];
    if (file) {
      const formData = new FormData();
      formData.append('proof', file);
      formData.append('report_id', reportId);
      
      fetch('upload_proof.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Proof uploaded successfully!');
          if (statusSelect) {
            statusSelect.disabled = false;
            statusSelect.style.opacity = '1';
            statusSelect.style.cursor = 'pointer';
          }
          button.textContent = 'Update Proof';
        } else {
          alert('Upload failed: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Upload failed. Please try again.');
      });
    }
  };
  
  fileInput.click();
}

// Function to update status in database
function updateStatusInDatabase(select) {
  const container = select.closest('tr') || select.closest('.task-mobile-card');
  if (!container) {
    console.error('Could not find parent container for status select');
    return;
  }
  
  const reportId = container.getAttribute('data-id');
  const newStatus = select.value;
  const currentStatus = select.getAttribute('data-current-status') || select.value;
  
  fetch('update_status.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `report_id=${reportId}&new_status=${encodeURIComponent(newStatus)}&current_status=${encodeURIComponent(currentStatus)}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Status change request submitted for admin approval.');
 
      select.setAttribute('data-pending-status', newStatus);
      select.value = currentStatus;
    } else {
      alert('Failed to submit status change request: ' + (data.message || 'Unknown error'));

      select.value = currentStatus;
    }
  })
  .catch(error => {
    console.error('Error updating status:', error);
    alert('Failed to submit status change request. Please try again.');
    select.value = currentStatus;
  });
}

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
  
  tableRows.forEach(row => {
    const cells = row.querySelectorAll('td');
    if (cells.length === 0) return;
    
    const task = cells[0].textContent.toLowerCase(); // Task/Details
    const facility = cells[1].textContent.toLowerCase(); // Facility/Issue Type
    
    // Check if row matches search and filter criteria
    const matchesSearch = searchValue === '' || 
                         task.includes(searchValue) || 
                         facility.includes(searchValue);
    const matchesFilter = filterValue === '' || facility.includes(filterValue);
    
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
    
    if (!facilityElement || !taskElement) return;
    
    const task = taskElement.textContent.toLowerCase();
    const facility = facilityElement.textContent.toLowerCase();
    
    const matchesSearch = searchValue === '' || 
                         task.includes(searchValue) || 
                         facility.includes(searchValue);
    const matchesFilter = filterValue === '' || facility.includes(filterValue);
    
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
