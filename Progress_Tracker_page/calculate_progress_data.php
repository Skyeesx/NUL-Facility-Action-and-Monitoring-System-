<?php
require_once __DIR__ . '/../connection/api_guard.php';
requireApi('Admin');

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

ob_start();
require_once __DIR__ . '/../connection/db_connection.php';
$buffer = trim(ob_get_clean());

if (!empty($buffer)) {
    http_response_code(500);
    echo json_encode(['error' => 'Server include produced output', 'details' => substr($buffer, 0, 1000)]);
    exit;
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection is not available']);
    exit;
}

$dataType = isset($_GET['type']) ? $_GET['type'] : 'task_progress';

try {
    if ($dataType === 'task_progress') {
        // Formula: (Completed tasks / Total tasks) * 100 for each building
        
        $sql = "SELECT 
                    building_name,
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) as completed_tasks
                FROM report
                GROUP BY building_name
                HAVING total_tasks > 0
                ORDER BY building_name";
        
        $result = $conn->query($sql);
        if ($result === false) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $progress_percent = ($row['completed_tasks'] / $row['total_tasks']) * 100;
            $data[] = [
                $row['building_name'], 
                round($progress_percent, 0)
            ];
        }
        
        if (empty($data)) {
            $data = [
                ['No reports yet', 0]
            ];
        }
        
    } elseif ($dataType === 'time_progress') {
        // Formula: Track completion rate over the past 8 weeks
        
        $sql = "SELECT 
                    DATE_FORMAT(report_date, '%Y-%U') as year_week,
                    DATE_FORMAT(report_date, 'Week %u') as week_label,
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) as completed_tasks
                FROM report
                WHERE report_date >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
                GROUP BY year_week, week_label
                ORDER BY year_week";
        
        $result = $conn->query($sql);
        if ($result === false) {
            throw new Exception('Query failed: ' . $conn->error);
        }
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $progress_percent = $row['total_tasks'] > 0 
                ? ($row['completed_tasks'] / $row['total_tasks']) * 100 
                : 0;
            $data[] = [
                $row['week_label'], 
                round($progress_percent, 0)
            ];
        }
        
        // If no data, return sample timeline to show chart structure
        if (empty($data)) {
            $data = [
                ['Week 1', 0],
                ['Week 2', 0]
            ];
        }
        
    } else {
        throw new Exception('Invalid data type requested');
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Data calculation failed',
        'details' => $e->getMessage()
    ]);
}

$conn->close();
?>
