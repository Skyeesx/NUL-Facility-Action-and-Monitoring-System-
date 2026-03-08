<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

// Anyone can submit a report, but session must be started for security
// No login required for report submission

if (isset($_POST['Submit'])) {
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $issue_type = $_POST['issue_type'];
    $building_name = $_POST['location_type'];
    $area = $_POST['room_area'];
    $description = $_POST['description'];

    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);  
        }
        $photo_path = $targetDir . basename($_FILES["proof_image"]["name"]);
        move_uploaded_file($_FILES["proof_image"]["tmp_name"], $photo_path);
    }
    
    $stmt = $conn->prepare("INSERT INTO report (email, contact_number, issue_type, building_name, area, details, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $email, $contact, $issue_type, $building_name, $area, $description, $photo_path);

    if ($stmt->execute()) {
        $reportId = $stmt->insert_id;
        
        // Create notification for admin about new report
        $notifMessage = "New report submitted by: " . $email;
        $notifStmt = $conn->prepare("INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, 'system', NOW(), 0)");
        $notifStmt->bind_param("is", $reportId, $notifMessage);
        $notifStmt->execute();
        $notifStmt->close();
        
        // Create notification for facility staff about new task assignment
        $facilityNotifMessage = "New task assigned: " . $issue_type . " at " . $building_name . " - " . $area;
        $facilityNotifStmt = $conn->prepare("INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, 'system', NOW(), 0)");
        $facilityNotifStmt->bind_param("is", $reportId, $facilityNotifMessage);
        $facilityNotifStmt->execute();
        $facilityNotifStmt->close();
    }
    
    ?>
    <script>
        alert('Report submitted successfully!');
        window.location.href = 'submit.html'; // Redirect to submit.html
    </script>
    <?php
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="style.css">
    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Bungee+Inline&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inria+Sans:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../log_in_page/log_in.html">
            <img src="Logo.png" alt="Logo" width="91" height="63" class="d-inline-block align-text-top">
            NU-L FAMS
            </a>
        </div>
    </nav>
    <h1 id="header">NU Laguna Facility & Safety Report Form</h1>
    <div class="form">
        <div class="information-form">
            <!-- the php goes in the action, but submit.html is just temporary, just in case we want to let the people know that 
             their report has been acknowledged. -->
            <form action="report.php" method="POST" enctype="multipart/form-data">
                <div class="info-blocks">
                    <h3>Report Information</h3>
                    <div class="info emailad">
                        <p>NU Email Address:</p><input type="text" placeholder="delacruzja@students.nu-laguna.edu.ph" name="email">
                    </div>
                    <div class="info contactnum">
                        <p>Contact Number:</p><input type="tel" placeholder="" name="contact">
                    </div>
                    <h3>Issue Category</h3>
                    <div class="info issue">
                        <p>Type of Issue:</p>
                        <select name="issue_type" required>
                            <option value="" disabled selected>Select an issue</option>
                            <option value="Security">Security Issue</option>
                            <option value="Maintenance">Maintenance Issue</option>
                            <option value="Housekeeping">Housekeeping Issue</option>
                        </select>
                    </div>
                <h3>Location of Issue</h3>
                <div class="info bldg">
                <p>Building:</p>
                        <select name="location_type" required>
                                <option value="Main Building">Main Building</option>
                                <option value="INSPIRE Sports Academy">INSPIRE Sports Academy</option>
                                <option value="INSPIRE Dormitel">INSPIRE Dormitel</option>
                        </select>
                    </div>
                <div class="info room">
                    <p>Room/Area:</p>
                    <input type="text" placeholder="e.g. Room 101, CR, Lobby" name="room_area">
                </div>
                <div class="info pic">
                    <p>Photo Upload:</p>
                    <div class="file_upload"><input type="file" name="proof_image" accept="image/*" required></div>
                </div>
            </div>
            <div class="info-descrip">
            <h3>Description of Issue</h3>
                <div class="info desc">
                <p>Describe the issue in detail</p>
                <textarea placeholder="Type here..." name="description"></textarea>
                </div>
                <!-- trigger element -->
                <div class="button-container">
                     <input type="submit" value="Submit" name="Submit">
                   
                </div>
            </div>
            </form>
        </div>
    </div>
</body>
</html>