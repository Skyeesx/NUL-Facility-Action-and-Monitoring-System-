<?php
    include('../../../connection/db_connection.php'); // adjust path if needed

    // Get top priority tasks for display
    $Priorityquery = "SELECT report_id, details, area, issue_type, priority_level
            FROM report
            WHERE priority_level IS NOT NULL 
            ORDER BY 
                CASE 
                WHEN priority_level = 'High' THEN 1
                WHEN priority_level = 'Medium' THEN 2
                WHEN priority_level = 'Low' THEN 3
                END
            LIMIT 3";

    $Priorityresult = mysqli_query($conn, $Priorityquery);

    $tasks = [];
    while ($row = mysqli_fetch_assoc($Priorityresult)) {
        $tasks[] = $row;
    }

    // Get all reports with status for progress calculation
    $Statusquery = "SELECT report_id, details, area, issue_type, status, priority_level
            FROM report
            ORDER BY report_id DESC"; 
    $Statusresult = mysqli_query($conn, $Statusquery);

    $statusTasks = [];
    while ($row = mysqli_fetch_assoc($Statusresult)) {
        $statusTasks[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['tasks' => $tasks, 'statusTasks' => $statusTasks]);
?>
