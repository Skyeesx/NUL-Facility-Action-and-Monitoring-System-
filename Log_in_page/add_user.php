<?php
require_once __DIR__ . '/../connection/session_config.php';
require_once __DIR__ . '/../connection/db_connection.php';

// Require admin access
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['Username']);
    $password = $_POST['Password'];
    $role = $_POST['UserRole'];

    // Debug output
    error_log("Add User Debug - Username: $username, Password length: " . strlen($password) . ", Role: $role");

    if (empty($username) || empty($password) || empty($role)) {
        echo "<script>alert('All fields are required.');</script>";
    } else {
        // Check if user already exists
        $check = $conn->prepare("SELECT Username FROM fams_user WHERE Username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo "<script>alert('Username already exists.');</script>";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Debug logging
            error_log("Hashed password for $username: $hashedPassword");

            $stmt = $conn->prepare("INSERT INTO fams_user (Username, Password, UserRole) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                error_log("User $username added successfully with hashed password");
                echo "<script>alert('User successfully added!'); window.location.href='add_user.php';</script>";
            } else {
                error_log("Error adding user: " . $stmt->error);
                echo "<script>alert('Error adding user. Please try again.');</script>";
            }

            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f3f3f3;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 380px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            box-sizing: border-box;
        }
        label {
            display: block;
            margin-top: 12px;
            font-weight: 500;
        }
        button {
            background-color: #0056b3;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #004099;
        }
        .password-container {
            position: relative;
            margin-top: 0;
        }
        .password-container input {
            padding-right: 45px;
            width: 100%;
            margin-top: 12px;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 18px;
            user-select: none;
        }
        .toggle-password:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New User</h2>
        <form method="POST" action="">
            <label>Username (NU Email)</label>
            <input type="text" name="Username" required placeholder="example@nu-laguna.edu.ph">

            <label>Password</label>
            <div class="password-container">
                <input type="password" name="Password" id="password" required placeholder="Enter password">
                <span class="toggle-password" onclick="togglePassword('password', this)">👁</span>
            </div>

            <label>Role</label>
            <select name="UserRole" required>
                <option value="">Select Role</option>
                <option value="Admin">Admin</option>
                <option value="Facility Management Staff">Facility Management Staff</option>
            </select>

            <button type="submit">Add User</button>
        </form>
    </div>

    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = '👁';
                icon.style.textDecoration = 'line-through';
            } else {
                input.type = 'password';
                icon.textContent = '👁';
                icon.style.textDecoration = 'none';
            }
        }
    </script>
</body>
</html>
