<?php
include 'db.php';
session_start();

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$errorDetails = [];

if (isset($_POST['upload_fees'])) {
    if (isset($_FILES['fees_file']) && $_FILES['fees_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['fees_file']['tmp_name'];
        $fileType = $_FILES['fees_file']['type'];

        $allowedMimeTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
        if (!in_array($fileType, $allowedMimeTypes)) {
            $message = "❌ Please upload a valid CSV file.";
        } else {
            $file = fopen($fileTmpPath, 'r');
            $rowCount = 0;
            $insertCount = 0;
            $missingStudents = [];

            fgetcsv($file); // Skip header

            while (($row = fgetcsv($file)) !== false) {
                $rowCount++;
                $admission_number = trim($row[0]);
                $paid_date = trim($row[1]);
                $amount_paid = trim($row[2]);

                // Format date
                $paid_date_parts = explode('-', $paid_date);
                $paid_date_formatted = (count($paid_date_parts) === 3)
                    ? $paid_date_parts[2] . '-' . $paid_date_parts[1] . '-' . $paid_date_parts[0]
                    : '';

                if (!empty($admission_number) && is_numeric($amount_paid) && preg_match('/^\d{2}-\d{2}-\d{4}$/', $paid_date)) {
                    // Check if student exists
                    $checkStudent = $conn->prepare("SELECT admission_number FROM students WHERE admission_number = ?");
                    $checkStudent->bind_param("s", $admission_number);
                    $checkStudent->execute();
                    $checkStudent->store_result();

                    if ($checkStudent->num_rows === 0) {
                        $missingStudents[] = $admission_number;
                        $checkStudent->close();
                        continue;
                    }
                    $checkStudent->close();

                    // Check duplicate fee entry
                    $check = $conn->prepare("SELECT id FROM fees WHERE admission_number = ? AND paid_date = ?");
                    $check->bind_param("ss", $admission_number, $paid_date_formatted);
                    $check->execute();
                    $check->store_result();

                    if ($check->num_rows === 0) {
                        $stmt = $conn->prepare("INSERT INTO fees (admission_number, paid_date, amount_paid) VALUES (?, ?, ?)");
                        $stmt->bind_param("ssd", $admission_number, $paid_date_formatted, $amount_paid);
                        if ($stmt->execute()) {
                            $insertCount++;
                        }
                        $stmt->close();
                    }
                    $check->close();
                }
            }

            fclose($file);

            $message = "✅ Successfully inserted $insertCount fee records.";
            if (!empty($missingStudents)) {
                $errorDetails[] = "❌ These admission numbers were not found in the system:";
                foreach ($missingStudents as $missing) {
                    $errorDetails[] = "- $missing";
                }
            }
        }
    } else {
        $message = "❌ Please select a CSV file to upload.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Upload Fees</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            max-width: 420px;
            width: 100%;
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 700;
            font-size: 24px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 15px;
            color: #34495e;
            font-weight: 600;
            text-align: left;
        }

        input[type="file"] {
            margin-bottom: 25px;
            padding: 10px;
            font-size: 16px;
            border: 2px dashed #3498db;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 14px 0;
            width: 100%;
            font-size: 17px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        .message {
            margin-top: 25px;
            font-weight: 700;
            font-size: 16px;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1.5px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1.5px solid #f5c6cb;
            text-align: left;
            padding: 15px 20px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

<form method="POST" enctype="multipart/form-data">
    <h2>Bulk Upload Fees (CSV)</h2>
    <label>Select CSV file (Headers: admission_number, paid_date, amount_paid):</label><br>
    <input type="file" name="fees_file" accept=".csv" required><br>
    <button type="submit" name="upload_fees">Upload</button>

    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!empty($errorDetails)): ?>
        <div class="message error">
            <?= implode("<br>", array_map('htmlspecialchars', $errorDetails)) ?>
        </div>
    <?php endif; ?>
</form>

</body>
</html>
