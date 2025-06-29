<?php
include 'db.php'; 

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admission_number = trim($_POST['admission_number']);
    $attendance_percentage = trim($_POST['attendance_percentage']);

    // Validate inputs
    if (!is_numeric($attendance_percentage) || $attendance_percentage < 0 || $attendance_percentage > 100) {
        $message = "Please enter a valid attendance percentage between 0 and 100.";
        $messageClass = "error";
    } else {
        // Check if the attendance entry for the student already exists
        $stmt = $conn->prepare("SELECT * FROM attendance WHERE admission_number = ?");
        $stmt->bind_param("s", $admission_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If attendance already exists, update it
            $stmt = $conn->prepare("UPDATE attendance SET attendance_percentage = ? WHERE admission_number = ?");
            $stmt->bind_param("ds", $attendance_percentage, $admission_number);
            if ($stmt->execute()) {
                $message = "Attendance updated successfully!";
                $messageClass = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $messageClass = "error";
            }
        } else {
            // If no attendance entry exists, insert new record
            $stmt = $conn->prepare("INSERT INTO attendance (admission_number, attendance_percentage) VALUES (?, ?)");
            $stmt->bind_param("sd", $admission_number, $attendance_percentage);
            if ($stmt->execute()) {
                $message = "Attendance added successfully!";
                $messageClass = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $messageClass = "error";
            }
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Attendance</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7fafc;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin: 20px auto;
            padding: 15px;
            border-radius: 8px;
        }

        .success {
            color: #2ecc71;
            background-color: #eafaf1;
            border: 1px solid #2ecc71;
        }

        .error {
            color: #e74c3c;
            background-color: #fdecea;
            border: 1px solid #e74c3c;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 10px;
            color: #34495e;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background-color: #f1f1f1;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Update Attendance</h2>

    <?php if (!empty($message)): ?>
        <div class="message <?= $messageClass ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="update_attendance.php">
        <label for="admission_number">Admission Number</label>
        <input type="text" name="admission_number" id="admission_number" required>

        <label for="attendance_percentage">Attendance Percentage</label>
        <input type="number" name="attendance_percentage" id="attendance_percentage" required step="0.01" min="0" max="100">

        <button type="submit">Update Attendance</button>
    </form>
</div>

</body>
</html>
