<?php
require_once __DIR__ . '/../connection/api_guard.php';
requireApi('Admin');
require_once __DIR__ . '/../connection/db_connection.php';
header('Content-Type: application/json');

$overallStats = [];

// Total reports count
$totalQuery = "SELECT COUNT(*) as total FROM report";
$totalResult = $conn->query($totalQuery);
$overallStats['total_reports'] = $totalResult->fetch_assoc()['total'];

// Status breakdown
$statusQuery = "SELECT status, COUNT(*) as count FROM report GROUP BY status";
$statusResult = $conn->query($statusQuery);
$statusBreakdown = [];
while ($row = $statusResult->fetch_assoc()) {
    $statusBreakdown[$row['status']] = $row['count'];
}
$overallStats['status_breakdown'] = $statusBreakdown;

$priorityQuery = "SELECT priority_level, COUNT(*) as count FROM report GROUP BY priority_level";
$priorityResult = $conn->query($priorityQuery);
$priorityBreakdown = [];
while ($row = $priorityResult->fetch_assoc()) {
    $priorityBreakdown[$row['priority_level']] = $row['count'];
}
$overallStats['priority_breakdown'] = $priorityBreakdown;

$issueQuery = "SELECT issue_type, COUNT(*) as count FROM report GROUP BY issue_type";
$issueResult = $conn->query($issueQuery);
$issueBreakdown = [];
while ($row = $issueResult->fetch_assoc()) {
    $issueBreakdown[$row['issue_type']] = $row['count'];
}
$overallStats['issue_breakdown'] = $issueBreakdown;

$buildingQuery = "
    SELECT 
        building_name,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) as completed,
        ROUND((SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as completion_rate
    FROM report 
    GROUP BY building_name
";
$buildingResult = $conn->query($buildingQuery);
$buildingBreakdown = [];
while ($row = $buildingResult->fetch_assoc()) {
    $buildingBreakdown[] = $row;
}
$overallStats['building_breakdown'] = $buildingBreakdown;

// Recent reports (last 10)
$recentQuery = "
    SELECT 
        report_id,
        issue_type,
        building_name,
        area,
        details,
        status,
        priority_level,
        report_date
    FROM report 
    ORDER BY report_date DESC 
    LIMIT 10
";
$recentResult = $conn->query($recentQuery);
$recentReports = [];
while ($row = $recentResult->fetch_assoc()) {
    $recentReports[] = $row;
}
$overallStats['recent_reports'] = $recentReports;

// Average completion time (for completed tasks)
$avgTimeQuery = "
    SELECT 
        AVG(DATEDIFF(
            COALESCE(
                (SELECT approved_date FROM status_change_requests 
                 WHERE report_id = report.report_id 
                 AND approval_status = 'approved' 
                 ORDER BY approved_date DESC LIMIT 1),
                NOW()
            ), 
            report_date
        )) as avg_days
    FROM report 
    WHERE status = 'Complete'
";
$avgTimeResult = $conn->query($avgTimeQuery);
$avgDays = $avgTimeResult->fetch_assoc()['avg_days'];
$overallStats['avg_completion_days'] = round($avgDays ?? 0, 1);

// Completion rate percentage
$completionRate = $overallStats['total_reports'] > 0 
    ? round(($statusBreakdown['Complete'] ?? 0) / $overallStats['total_reports'] * 100, 2) 
    : 0;
$overallStats['completion_rate'] = $completionRate;

echo json_encode($overallStats);

$conn->close();
?>
