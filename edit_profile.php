<?php
session_start();
include 'db.php';

if (!isset($_SESSION["faculty_logged_in"])) {
    header("Location: faculty_login.php");
    exit;
}

$current_username = $_SESSION["faculty_username"];
$success = "";
$error = "";

// Fetch current username from DB
$stmt = $conn->prepare("SELECT username FROM faculty_users WHERE username = ?");
$stmt->bind_param("s", $current_username);
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST["new_username"]);

    if (!empty($new_username)) {
        // Check if new username already exists
        $check = $conn->prepare("SELECT id FROM faculty_users WHERE username = ? AND username != ?");
        $check->bind_param("ss", $new_username, $current_username);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "❌ Username already taken.";
        } else {
            // Update username
            $update = $conn->prepare("UPDATE faculty_users SET username = ? WHERE username = ?");
            $update->bind_param("ss", $new_username, $current_username);
            if ($update->execute()) {
                $_SESSION["faculty_username"] = $new_username;
                $_SESSION["faculty_name"] = $new_username; // update displayed name
                $success = "✅ Profile updated successfully.";
                $current_username = $new_username;
            } else {
                $error = "❌ Error updating username.";
            }
        }
    } else {
        $error = "❌ New username cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma;
            background: #f4f4f4;
            padding: 50px;
        }

        form {
            background: #fff;
            max-width: 400px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
        }

        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

    <form method="POST">
        <h2>Edit Profile</h2>
        <label>Current Username:</label>
        <input type="text" value="<?= htmlspecialchars($current_username) ?>" disabled>

        <label>New Username:</label>
        <input type="text" name="new_username" required>

        <button type="submit">Update</button>

        <?php if ($success): ?>
            <div class="msg success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="msg error"><?= $error ?></div>
        <?php endif; ?>
    </form>

</body>
</html>
