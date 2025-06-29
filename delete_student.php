<?php
session_start();
include 'db.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admission_number = trim($_POST['admission_number'] ?? '');

    if (empty($admission_number)) {
        $error_message = "Admission number is required.";
    } else {
        // Check if student exists
        $check = $conn->prepare("SELECT id FROM students WHERE admission_number = ?");
        $check->bind_param("s", $admission_number);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            // Start transaction
            $conn->begin_transaction();

            try {
                // Delete from dependent tables first
                $tables = ['fees', 'results', 'attendance']; // list your dependent tables here

                foreach ($tables as $table) {
                    $del = $conn->prepare("DELETE FROM $table WHERE admission_number = ?");
                    $del->bind_param("s", $admission_number);
                    $del->execute();
                    $del->close();
                }

                // Now delete from students table
                $stmt = $conn->prepare("DELETE FROM students WHERE admission_number = ?");
                $stmt->bind_param("s", $admission_number);
                $stmt->execute();
                $stmt->close();

                $conn->commit();

                $success_message = "✅ Student with Admission Number <strong>$admission_number</strong> and related data deleted successfully.";
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "❌ Failed to delete student: " . $e->getMessage();
            }
        } else {
            $error_message = "❌ No student found with Admission Number <strong>$admission_number</strong>.";
        }

        $check->close();
    }
}
?>

<!-- The same HTML structure as before -->
<!DOCTYPE html>
<html>
<head>
    <title>Delete Student</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f0f0;
            padding: 40px;
        }

        .container {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #c0392b;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #c0392b;
        }

        .message {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .back {
            text-align: center;
            margin-top: 20px;
        }

        .back a {
            text-decoration: none;
            color: #2980b9;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🗑 Delete Student</h2>

    <form method="POST" action="">
        <label for="admission_number">Enter Admission Number:</label>
        <input type="text" name="admission_number" id="admission_number" required>

        <button type="submit">Delete Student</button>
    </form>

    <div class="message">
        <?php if ($success_message): ?>
            <div class="success"><?= $success_message ?></div>
        <?php elseif ($error_message): ?>
            <div class="error"><?= $error_message ?></div>
        <?php endif; ?>
    </div>

    <div class="back">
        <a href="index.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
