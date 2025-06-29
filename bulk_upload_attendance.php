<?php
include 'db.php';
session_start();
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$missingStudents = [];

if (isset($_POST['upload_attendance'])) {
    if (isset($_FILES['attendance_file']) && $_FILES['attendance_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['attendance_file']['tmp_name'];
        $fileType = $_FILES['attendance_file']['type'];
        
        $allowedMimeTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
        if (!in_array($fileType, $allowedMimeTypes)) {
            $message = "❌ Please upload a valid CSV file.";
        } else {
            $file = fopen($fileTmpPath, 'r');
            $rowCount = 0;
            $insertCount = 0;

            fgetcsv($file); // Skip header row

            while (($row = fgetcsv($file)) !== false) {
                $rowCount++;
                $admission_number = trim($row[0]);
                $attendance_percentage = trim($row[1]);

                if (!empty($admission_number) && is_numeric($attendance_percentage) && $attendance_percentage >= 0 && $attendance_percentage <= 100) {
                    // Check if student exists
                    $check = $conn->prepare("SELECT admission_number FROM students WHERE admission_number = ?");
                    $check->bind_param("s", $admission_number);
                    $check->execute();
                    $check->store_result();

                    if ($check->num_rows > 0) {
                        // Insert or update attendance
                        $stmt = $conn->prepare("INSERT INTO attendance (admission_number, attendance_percentage) VALUES (?, ?) ON DUPLICATE KEY UPDATE attendance_percentage = ?");
                        $stmt->bind_param("sdd", $admission_number, $attendance_percentage, $attendance_percentage);
                        if ($stmt->execute()) {
                            $insertCount++;
                        }
                        $stmt->close();
                    } else {
                        $missingStudents[] = $admission_number;
                    }

                    $check->close();
                }
            }

            fclose($file);
            $message = "✅ Successfully inserted/updated";
        }
    } else {
        $message = "❌ Please select a CSV file to upload.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Upload Attendance</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #e8f5e9;
        margin: 0;
        padding: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    form {
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        max-width: 460px;
        width: 100%;
        box-sizing: border-box;
        text-align: center;
    }

    h2 {
        margin-bottom: 25px;
        color: #2c3e50;
        font-weight: 700;
        font-size: 26px;
    }

    label {
        display: block;
        margin-bottom: 12px;
        font-size: 16px;
        font-weight: 600;
        color: #2e7d32;
        text-align: left;
    }

    input[type="file"] {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 2px dashed #27ae60;
        border-radius: 8px;
        cursor: pointer;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
        margin-bottom: 25px;
    }

    input[type="file"]:hover {
        border-color: #219150;
    }

    button {
        background-color: #27ae60;
        color: white;
        border: none;
        padding: 14px 0;
        width: 100%;
        font-size: 18px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
    }

    button:hover {
        background-color: #219150;
        box-shadow: 0 7px 20px rgba(33, 145, 80, 0.6);
    }

    .message {
        margin-top: 25px;
        font-weight: 700;
        font-size: 16px;
        padding: 15px 20px;
        border-radius: 10px;
        max-width: 420px;
        margin-left: auto;
        margin-right: auto;
        box-sizing: border-box;
        text-align: center;
    }

    .success {
        background-color: #d0f0d8;
        color: #2e7d32;
        border: 2px solid #27ae60;
    }

    .error {
        background-color: #f8d7da;
        color: #9b2c2c;
        border: 2px solid #d32f2f;
        text-align: left;
        white-space: pre-wrap;
    }
    </style>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <h2>📋 Bulk Upload Attendance (CSV)</h2>
        <label>Select CSV file (Headers: admission_number, attendance_percentage):</label><br>
        <input type="file" name="attendance_file" accept=".csv" required><br>
        <button type="submit" name="upload_attendance">Upload</button>

        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if (!empty($missingStudents)): ?>
            <div class="message error">
                ❌ The following admission numbers were not found in the system:<br>
                <?= implode('<br>', array_map('htmlspecialchars', $missingStudents)) ?>
            </div>
        <?php endif; ?>
    </form>
</body>
</html>
