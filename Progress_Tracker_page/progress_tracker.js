google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawCharts);

async function fetchJsonSafe(url) {
  const res = await fetch(url, {cache: 'no-store'});
  const contentType = res.headers.get('content-type') || '';
  const text = await res.text();
  if (!res.ok) {
    const snippet = text.substring(0, 1000);
    throw new Error('HTTP ' + res.status + ': ' + res.statusText + '\n' + snippet);
  }
  if (!contentType.toLowerCase().includes('application/json')) {
    const snippet = text.substring(0, 1000);
    throw new Error('Expected JSON but received: ' + snippet);
  }
  // parse JSON
  try {
    return JSON.parse(text);
  } catch (err) {
    const snippet = text.substring(0, 1000);
    throw new Error('Invalid JSON: ' + err.message + '\n' + snippet);
  }
}

async function drawCharts() {
  // 🟦 COLUMN CHART - Building-wise task completion
  try {
    const data = await fetchJsonSafe('calculate_progress_data.php?type=task_progress');
    const chartData = [['Building', 'Completion %'], ...data];
    const dataTable = google.visualization.arrayToDataTable(chartData);

    const options1 = {
      title: 'Task Completion by Building (%)',
      legend: { position: 'none' },
      hAxis: { title: 'Buildings' },
      vAxis: { title: 'Completion %', minValue: 0, maxValue: 100 },
      colors: ['#386282']
    };

    new google.visualization.ColumnChart(document.getElementById('column_chart')).draw(dataTable, options1);
  } catch (error) {
    console.error('Error loading column chart data:', error);
    const el = document.getElementById('column_chart');
    if (el) el.innerHTML = '<div style="color:#a00;padding:12px;">Error loading column chart: ' + (error.message||error) + '</div>';
  }

  // 🟩 LINE CHART - Weekly progress trend
  try {
    const data2 = await fetchJsonSafe('calculate_progress_data.php?type=time_progress');
    const chartData2 = [['Week', 'Completion %'], ...data2];
    const dataTable2 = google.visualization.arrayToDataTable(chartData2);

    const options3 = {
      title: 'Weekly Task Completion Trend',
      curveType: 'function',
      legend: { position: 'bottom' },
      vAxis: { minValue: 0, maxValue: 100 },
      colors: ['#4f959d']
    };

    new google.visualization.LineChart(document.getElementById('line_chart')).draw(dataTable2, options3);
  } catch (error) {
    console.error('Error loading line chart data:', error);
    const el = document.getElementById('line_chart');
    if (el) el.innerHTML = '<div style="color:#a00;padding:12px;">Error loading line chart: ' + (error.message||error) + '</div>';
  }
}

// PDF Generation
document.addEventListener("DOMContentLoaded", function () {
  const { jsPDF } = window.jspdf;
  const button = document.getElementById("generatePDFBtn");

  if (button) {
    button.addEventListener("click", async function () {
      try {
        // Fetch detailed report data
        const response = await fetch('get_detailed_report_data.php');
        const reportData = await response.json();
        
        const pdf = new jsPDF("p", "mm", "a4");
        const margin = 15;
        const pageWidth = pdf.internal.pageSize.width;
        const pageHeight = pdf.internal.pageSize.height;
        let yPos = margin;
        
        // ===== PAGE 1: TITLE AND SUMMARY =====
        pdf.setFontSize(20);
        pdf.setFont(undefined, 'bold');
        pdf.text("NU Laguna FAMS", pageWidth / 2, yPos, { align: 'center' });
        yPos += 10;
        
        pdf.setFontSize(16);
        pdf.text("Progress Report", pageWidth / 2, yPos, { align: 'center' });
        yPos += 8;
        
        pdf.setFontSize(10);
        pdf.setFont(undefined, 'normal');
        const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        pdf.text("Generated: " + today, pageWidth / 2, yPos, { align: 'center' });
        yPos += 15;
        
        // Overall Statistics
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Overall Statistics", margin, yPos);
        yPos += 8;
        
        pdf.setFontSize(11);
        pdf.setFont(undefined, 'normal');
        pdf.text(`Total Reports: ${reportData.total_reports}`, margin + 5, yPos);
        yPos += 7;
        pdf.text(`Completion Rate: ${reportData.completion_rate}%`, margin + 5, yPos);
        yPos += 7;
        pdf.text(`Average Completion Time: ${reportData.avg_completion_days} days`, margin + 5, yPos);
        yPos += 12;
        
        // Status Breakdown
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Status Breakdown", margin, yPos);
        yPos += 8;
        
        pdf.setFontSize(11);
        pdf.setFont(undefined, 'normal');
        const statusBreakdown = reportData.status_breakdown || {};
        pdf.text(`Pending: ${statusBreakdown['Pending'] || 0}`, margin + 5, yPos);
        yPos += 7;
        pdf.text(`In Progress: ${statusBreakdown['In Progress'] || 0}`, margin + 5, yPos);
        yPos += 7;
        pdf.text(`Complete: ${statusBreakdown['Complete'] || 0}`, margin + 5, yPos);
        yPos += 12;
        
        // Priority Breakdown
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Priority Breakdown", margin, yPos);
        yPos += 8;
        
        pdf.setFontSize(11);
        pdf.setFont(undefined, 'normal');
        const priorityBreakdown = reportData.priority_breakdown || {};
        pdf.text(`High Priority: ${priorityBreakdown['High'] || 0}`, margin + 5, yPos);
        yPos += 7;
        pdf.text(`Medium Priority: ${priorityBreakdown['Medium'] || 0}`, margin + 5, yPos);
        yPos += 7;
        pdf.text(`Low Priority: ${priorityBreakdown['Low'] || 0}`, margin + 5, yPos);
        yPos += 12;
        
        // Issue Type Breakdown
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Issue Type Breakdown", margin, yPos);
        yPos += 8;
        
        pdf.setFontSize(11);
        pdf.setFont(undefined, 'normal');
        const issueBreakdown = reportData.issue_breakdown || {};
        Object.entries(issueBreakdown).forEach(([type, count]) => {
          pdf.text(`${type}: ${count}`, margin + 5, yPos);
          yPos += 7;
        });
        yPos += 5;
        
        // Building Performance
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Building Performance", margin, yPos);
        yPos += 8;
        
        pdf.setFontSize(10);
        pdf.setFont(undefined, 'normal');
        const buildings = reportData.building_breakdown || [];
        buildings.forEach(building => {
          if (yPos > pageHeight - 20) {
            pdf.addPage();
            yPos = margin;
          }
          pdf.text(`${building.building_name}:`, margin + 5, yPos);
          yPos += 6;
          pdf.text(`  Total: ${building.total} | Completed: ${building.completed} | Rate: ${building.completion_rate}%`, margin + 5, yPos);
          yPos += 8;
        });
        
        // ===== PAGE 2: CHARTS =====
        pdf.addPage();
        yPos = margin;
        
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Task Completion by Building", margin, yPos);
        yPos += 5;
        
        const chart1 = document.getElementById("column_chart");
        const canvas1 = await html2canvas(chart1);
        const imgData1 = canvas1.toDataURL("image/png");
        pdf.addImage(imgData1, "PNG", margin, yPos, 180, 100);
        yPos += 110;
        
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Progress Over Time", margin, yPos);
        yPos += 5;
        
        const chart2 = document.getElementById("line_chart");
        const canvas2 = await html2canvas(chart2);
        const imgData2 = canvas2.toDataURL("image/png");
        pdf.addImage(imgData2, "PNG", margin, yPos, 180, 100);
        
        // ===== PAGE 3: RECENT REPORTS =====
        pdf.addPage();
        yPos = margin;
        
        pdf.setFontSize(14);
        pdf.setFont(undefined, 'bold');
        pdf.text("Recent Reports (Last 10)", margin, yPos);
        yPos += 10;
        
        pdf.setFontSize(9);
        pdf.setFont(undefined, 'normal');
        
        const recentReports = reportData.recent_reports || [];
        recentReports.forEach((report, index) => {
          if (yPos > pageHeight - 25) {
            pdf.addPage();
            yPos = margin;
          }
          
          pdf.setFont(undefined, 'bold');
          pdf.text(`${index + 1}. Report #${report.report_id}`, margin, yPos);
          yPos += 5;
          
          pdf.setFont(undefined, 'normal');
          pdf.text(`Issue: ${report.issue_type} | Location: ${report.building_name} - ${report.area}`, margin + 5, yPos);
          yPos += 5;
          pdf.text(`Status: ${report.status} | Priority: ${report.priority_level} | Date: ${new Date(report.report_date).toLocaleDateString()}`, margin + 5, yPos);
          yPos += 5;
          
          const details = report.details.length > 80 ? report.details.substring(0, 80) + '...' : report.details;
          const detailsLines = pdf.splitTextToSize(`Details: ${details}`, pageWidth - 2 * margin - 5);
          pdf.text(detailsLines, margin + 5, yPos);
          yPos += 5 * detailsLines.length + 5;
        });
        
        // Save PDF
        pdf.save("FAMS_Detailed_Progress_Report_" + today.replace(/\s/g, '_') + ".pdf");
        
      } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Error generating PDF report. Please try again.');
      }
    });
  }
  
  // Load admin notifications
  loadAdminNotifications();
});

// Load admin notifications function
function loadAdminNotifications() {
  fetch('../Dashboard_page/get_admin_notifications.php')
      .then(response => response.json())
      .then(data => {
          const badge = document.getElementById('notifBadge');
          const menu = document.getElementById('notificationMenu');
          const noNotif = document.getElementById('noNotifications');
          
          // Update badge
          if (data.unread_count > 0) {
              badge.textContent = data.unread_count;
              badge.style.display = 'block';
          } else {
              badge.style.display = 'none';
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

// Refresh notifications every 30 seconds
setInterval(loadAdminNotifications, 30000);
