<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';
require_once __DIR__ . '/../connection/upload_helpers.php';

// Anyone can submit a report, no login required.

$allowedIssueTypes = ['Security', 'Maintenance', 'Housekeeping'];
$allowedBuildings  = ['Main Building', 'INSPIRE Sports Academy', 'INSPIRE Dormitel'];

$errors = [];
$old = ['email' => '', 'contact' => '', 'issue_type' => '', 'location_type' => '', 'room_area' => '', 'description' => ''];

if (isset($_POST['Submit'])) {
    foreach ($old as $key => $_) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 100) {
        $errors[] = 'Enter a valid email address (up to 100 characters).';
    }
    if (!preg_match('/^[0-9+()\-\s]{7,15}$/', $old['contact'])) {
        $errors[] = 'Enter a valid contact number (7 to 15 digits).';
    }
    if (!in_array($old['issue_type'], $allowedIssueTypes, true)) {
        $errors[] = 'Select a type of issue.';
    }
    if (!in_array($old['location_type'], $allowedBuildings, true)) {
        $errors[] = 'Select a building.';
    }
    if ($old['room_area'] === '' || mb_strlen($old['room_area']) > 100) {
        $errors[] = 'Enter the room or area (up to 100 characters).';
    }
    if ($old['description'] === '' || mb_strlen($old['description']) > 255) {
        $errors[] = 'Describe the issue (up to 255 characters).';
    }

    // Photo: validated by real content, extension chosen from the detected type.
    $photoTmp = null;
    $photoExt = null;
    if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Attach a photo of the issue.';
    } else {
        $mime = famsDetectMime($_FILES['proof_image']['tmp_name']);
        if (!isset(FAMS_IMAGE_TYPES[$mime])) {
            $errors[] = 'The photo must be a JPG, PNG, GIF or WEBP image.';
        } elseif ($_FILES['proof_image']['size'] > 10 * 1024 * 1024) {
            $errors[] = 'The photo must be 10MB or smaller.';
        } else {
            $photoTmp = $_FILES['proof_image']['tmp_name'];
            $photoExt = FAMS_IMAGE_TYPES[$mime];
        }
    }

    if (!$errors) {
        $targetDir = __DIR__ . '/uploads';
        famsEnsureUploadDir($targetDir);
        $fileName = famsRandomName('report', $photoExt);

        if (!move_uploaded_file($photoTmp, $targetDir . '/' . $fileName)) {
            $errors[] = 'The photo could not be saved. Please try again.';
        } else {
            $photo_path = 'uploads/' . $fileName;

            $stmt = $conn->prepare("INSERT INTO report (email, contact_number, issue_type, building_name, area, details, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $old['email'], $old['contact'], $old['issue_type'], $old['location_type'], $old['room_area'], $old['description'], $photo_path);

            if ($stmt->execute()) {
                $reportId = $stmt->insert_id;

                // Notification for facility staff about the new task.
                // (Admins are notified from the report table directly. The reporter's email
                // is intentionally not written into this shared notifications table.)
                $facilityNotifMessage = "New task assigned: " . $old['issue_type'] . " at " . $old['location_type'] . " - " . $old['room_area'];
                $facilityNotifStmt = $conn->prepare("INSERT INTO notifications (report_id, message, created_by, created_at, is_read) VALUES (?, ?, 'system', NOW(), 0)");
                $facilityNotifStmt->bind_param("is", $reportId, $facilityNotifMessage);
                $facilityNotifStmt->execute();
                $facilityNotifStmt->close();

                $stmt->close();
                $conn->close();
                header('Location: submit.html');
                exit;
            }

            // Insert failed: remove the orphaned photo and report the failure.
            unlink($targetDir . '/' . $fileName);
            $errors[] = 'Your report could not be saved. Please try again.';
            $stmt->close();
        }
    }
}

function fieldValue($old, $key) {
    return htmlspecialchars($old[$key], ENT_QUOTES, 'UTF-8');
}
function selectedIf($old, $key, $value) {
    return $old[$key] === $value ? ' selected' : '';
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
            <a class="navbar-brand" href="report.php">
            <img src="Logo.png" alt="Logo" width="91" height="63" class="d-inline-block align-text-top">
            NU-L FAMS
            </a>
        </div>
    </nav>
    <h1 id="header">NU Laguna Facility & Safety Report Form</h1>

    <?php if ($errors): ?>
        <div class="alert alert-danger mx-auto" role="alert" style="max-width: 900px;">
            <strong>Please fix the following:</strong>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form">
        <div class="information-form">
            <form action="report.php" method="POST" enctype="multipart/form-data">
                <div class="info-blocks">
                    <h3>Report Information</h3>
                    <div class="info emailad">
                        <p>NU Email Address:</p><input type="email" placeholder="delacruzja@students.nu-laguna.edu.ph" name="email" maxlength="100" required value="<?= fieldValue($old, 'email') ?>">
                    </div>
                    <div class="info contactnum">
                        <p>Contact Number:</p><input type="tel" placeholder="" name="contact" maxlength="15" required value="<?= fieldValue($old, 'contact') ?>">
                    </div>
                    <h3>Issue Category</h3>
                    <div class="info issue">
                        <p>Type of Issue:</p>
                        <select name="issue_type" required>
                            <option value="" disabled<?= $old['issue_type'] === '' ? ' selected' : '' ?>>Select an issue</option>
                            <option value="Security"<?= selectedIf($old, 'issue_type', 'Security') ?>>Security Issue</option>
                            <option value="Maintenance"<?= selectedIf($old, 'issue_type', 'Maintenance') ?>>Maintenance Issue</option>
                            <option value="Housekeeping"<?= selectedIf($old, 'issue_type', 'Housekeeping') ?>>Housekeeping Issue</option>
                        </select>
                    </div>
                <h3>Location of Issue</h3>
                <div class="info bldg">
                <p>Building:</p>
                        <select name="location_type" required>
                                <option value="Main Building"<?= selectedIf($old, 'location_type', 'Main Building') ?>>Main Building</option>
                                <option value="INSPIRE Sports Academy"<?= selectedIf($old, 'location_type', 'INSPIRE Sports Academy') ?>>INSPIRE Sports Academy</option>
                                <option value="INSPIRE Dormitel"<?= selectedIf($old, 'location_type', 'INSPIRE Dormitel') ?>>INSPIRE Dormitel</option>
                        </select>
                    </div>
                <div class="info room">
                    <p>Room/Area:</p>
                    <input type="text" placeholder="e.g. Room 101, CR, Lobby" name="room_area" maxlength="100" required value="<?= fieldValue($old, 'room_area') ?>">
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
                <textarea placeholder="Type here..." name="description" maxlength="255" required><?= fieldValue($old, 'description') ?></textarea>
                </div>
                <div class="button-container">
                     <input type="submit" value="Submit" name="Submit">
                </div>
            </div>
            </form>
        </div>
    </div>
</body>
</html>
