google.charts.load("current", { packages: ["corechart"] });
google.charts.setOnLoadCallback(drawAllCharts);

// Store chart objects and data globally
let donutChart, donutData, donutOptions;
let columnCharts = [];
let columnData = [];
let columnOptions = [];

function drawAllCharts() {
    fetch('./Data_Folder/data1.php')
        .then(response => response.json())
        .then(data => {
            donutData = new google.visualization.DataTable();
            donutData.addColumn('string', 'Status');
            donutData.addColumn('number', 'Count');
            const statusCounts = {};
            data.forEach(item => {
                const status = item.status;
                statusCounts[status] = (statusCounts[status] || 0) + 1;
            });

            const chartData = Object.entries(statusCounts).map(([status, count]) => [status, count]);
            donutData.addRows(chartData);

            donutOptions = {
                pieHole: 0.5,
                legend: 'none',
                chartArea: { width: '90%', height: '90%' },
                pieSliceText: 'value'
            };

            donutChart = new google.visualization.PieChart(document.getElementById('donutchart'));
            donutChart.draw(donutData, donutOptions);

            loadProgressBars();
        })
        .then(() => {
            return fetch('./Data_Folder/data2.php');
        })
        .then(response => response.json())
        .then(data => {
            const grouped = {};
            data.forEach(item => {
                const issueType = item.issue_type;
                const status = item.status;
                const count = parseInt(item.count);

                if (!grouped[issueType]) {
                    grouped[issueType] = { 'Pending': 0, 'In Progress': 0, 'Complete': 0 };
                }
                grouped[issueType][status] += count;
            });

            const colors = {
                'Pending': '#FFBF00',
                'In Progress': '#0064E7',
                'Complete': '#00D75A'
            };

            const chartIDs = ["columnchart1", "columnchart2", "columnchart3"];

            let index = 0;
            for (const [issueType, statuses] of Object.entries(grouped)) {
                const rows = Object.entries(statuses).map(([status, count]) => [status, count, colors[status]]);

                const columnData = google.visualization.arrayToDataTable([
                    ["Status", "Reports", { role: "style" }],
                    ...rows
                ]);

                const columnOptions = {
                    title: issueType,
                    bar: { groupWidth: "95%" },
                    legend: { position: "none" },
                    chartArea: { width: '90%', height: '70%' },
                    vAxis: { minValue: 0 }
                };

                const chart = new google.visualization.ColumnChart(document.getElementById(chartIDs[index]));
                chart.draw(columnData, columnOptions);

                index++;
            }
        });
}

function updateDonutChart(rowIndex, newValue) {
    donutData.setValue(rowIndex, 1, newValue);
    donutChart.draw(donutData, donutOptions);
}

function updateColumnChart(chartIndex, rowIndex, colIndex, newValue) {
    columnData[chartIndex].setValue(rowIndex, colIndex, newValue);
    columnCharts[chartIndex].draw(columnData[chartIndex], columnOptions[chartIndex]);
}

function loadPriorityTasks() {
    fetch('./Data_Folder/priority_tasks.php')
        .then(response => response.json())
        .then(data => {
            const tasks = data.tasks || [];
            const container = document.querySelector('.tasklist');
            container.innerHTML = '';

            if (!tasks.length) {
                container.innerHTML = '<p>No priority tasks available.</p>';
                return;
            }

            tasks.slice(0, 2).forEach(task => {
                const priority = (task.priority_level || '').toLowerCase();
                const priorityClass =
                    priority === 'high' ? 'priority-high' :
                    priority === 'medium' ? 'priority-medium' :
                    'priority-low';

                const card = `
                    <div class="task-card ${priorityClass}">
                        <div class="task-left">
                            <span class="task-circle"></span>
                            <p class="task-text">${task.details}</p>
                        </div>
                        <div class="task-right">
                            <p class="task-location">${task.area}</p>
                            <p class="task-facility">${task.issue_type}</p>
                        </div>
                    </div>
                `;
                container.innerHTML += card;
            });

            const totalTasks = tasks.length;
            const highPriority = tasks.filter(task => task.priority_level === "High").length;
            const percentage = totalTasks > 0 ? (highPriority / totalTasks) * 100 : 0;

            const urgentBar = document.getElementById("urgentTasksBar");
            if (urgentBar) {
                urgentBar.style.width = percentage + "%";
                urgentBar.textContent = `High Priority (${Math.round(percentage)}%)`;
            }
        })
        .catch(error => console.error("Error loading priority tasks:", error));
}

function loadProgressBars() {
    fetch("./Data_Folder/priority_tasks.php")
        .then(response => response.json())
        .then(data => {
            const allTasks = data.statusTasks || [];
            const totalTasks = allTasks.length;

            const completed = allTasks.filter(task => task.status === "Complete").length;
            const ongoing = allTasks.filter(task => task.status === "In Progress").length;

            const allReports = data.tasks || [];
            const totalReports = allReports.length;
            const highPriority = allReports.filter(task => task.priority_level === "High").length;

            const urgentPercent = totalReports ? (highPriority / totalReports) * 100 : 0;
            const completedPercent = totalTasks ? (completed / totalTasks) * 100 : 0;
            const ongoingPercent = totalTasks ? (ongoing / totalTasks) * 100 : 0;

            document.getElementById("complete").value = completedPercent;
            document.getElementById("complete").max = 100;
            document.querySelector("label[for='complete']").textContent = `Completed (${completed}/${totalTasks})`;

            document.getElementById("urgent").value = urgentPercent;
            document.getElementById("urgent").max = 100;
            document.querySelector("label[for='urgent']").textContent = `Urgent (${highPriority}/${totalReports})`;

            document.getElementById("ongoing").value = ongoingPercent;
            document.getElementById("ongoing").max = 100;
            document.querySelector("label[for='ongoing']").textContent = `Ongoing (${ongoing}/${totalTasks})`;
        })
        .catch(error => console.error("Error loading progress bars:", error));
}

function loadRecentReports() {
    fetch('./Data_Folder/recent_reports.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentReportsContainer');
            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = '<p>No recent reports available.</p>';
                return;
            }

            data.slice(0, 10).forEach(report => {
                const status = (report.status || '').toLowerCase();
                let statusColor = '#00d75a';
                if (status === 'pending') statusColor = '#ffbf00';
                else if (status === 'in progress') statusColor = '#0064e7';
                else if (status === 'complete') statusColor = '#00d75a';

                const reportDiv = document.createElement('div');
                reportDiv.classList.add('report-format');
                reportDiv.innerHTML = `
                    <div class="report-left">
                        <h4>${report.area}</h4>
                        <p>${report.details}</p>
                    </div>
                    <div class="report-right">
                        <span class="status-badge" style="background-color: ${statusColor};">${report.status}</span>
                        <p class="date">${new Date(report.report_date).toLocaleDateString()}</p>
                    </div>
                `;
                container.appendChild(reportDiv);
            });
        })
        .catch(error => console.error('Error loading recent reports:', error));
}

function loadNotifications() {
    fetch('../Task_Management/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to load notifications');
                return;
            }

            const badge = document.getElementById('notifBadge');
            const notifList = document.getElementById('notificationList');
            const noNotif = document.getElementById('noNotifications');

            if (data.unread_count > 0) {
                badge.textContent = data.unread_count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }

            const menuItems = Array.from(notifList.children);
            menuItems.forEach(item => {
                if (item.id !== 'noNotifications' && !item.querySelector('.dropdown-divider') && !item.classList.contains('dropdown-header') && !item.querySelector('.text-primary')) {
                    item.remove();
                }
            });

            if (data.notifications.length > 0) {
                noNotif.style.display = 'none';

                const header = notifList.querySelector('.dropdown-header');

                data.notifications.forEach(notif => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.className = 'dropdown-item small';
                    a.href = '../Task_Management/index.php';
                    a.style.cursor = 'pointer';

                    let icon = '📋';
                    if (notif.message.includes('New task assigned')) {
                        icon = '📝';
                    } else if (notif.message.includes('approved')) {
                        icon = '✅';
                    } else if (notif.message.includes('rejected')) {
                        icon = '❌';
                    }

                    a.innerHTML = icon + ' ' + notif.message;

                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        markNotificationAsRead(notif.id);
                        window.location.href = '../Task_Management/index.php';
                    });

                    li.appendChild(a);
                    header.after(li);
                });
            } else {
                noNotif.style.display = 'block';
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

function markNotificationAsRead(notificationId) {
    fetch('../Task_Management/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `notification_id=${notificationId}`
    });
}

function markAllAsRead() {
    fetch('../Task_Management/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'mark_all=true'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

// Run all initialization when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadPriorityTasks();
    loadRecentReports();
    loadProgressBars();
    loadNotifications();
});

setInterval(loadNotifications, 30000);
