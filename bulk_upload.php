<?php
session_start();
include 'db.php';
require 'phpqrcode/qrlib.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

// Convert dd-mm-yyyy or other valid format to yyyy-mm-dd
function convertDate($dateVal) {
    $dateVal = trim($dateVal);
    if (preg_match("/^\d{2}-\d{2}-\d{4}$/", $dateVal)) {
        $parts = explode('-', $dateVal);
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
    } elseif (strtotime($dateVal)) {
        return date('Y-m-d', strtotime($dateVal));
    } else {
        return '0000-00-00';
    }
}

// Convert scientific notation Aadhaar to string
function formatAadhaar($aadhaar) {
    if (is_numeric($aadhaar)) {
        return number_format($aadhaar, 0, '', '');
    }
    return preg_replace('/[^0-9]/', '', $aadhaar);
}

// ✅ Auto-detect local IP address
$ip_address = getHostByName(getHostName());

$successCount = 0;
$errorMessages = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"]["tmp_name"];

    if (($handle = fopen($file, "r")) !== false) {
        fgetcsv($handle); // Skip header row

        if (!file_exists('qrcodes')) {
            mkdir('qrcodes', 0777, true);
        }

        $rowNum = 1;
        while (($data = fgetcsv($handle, 2000, ",")) !== false) {
            $rowNum++;

            $data = array_map('trim', array_pad($data, 19, ''));

            list(
                $admission_number, $student_name, $gender, $nationality, $mother_tongue,
                $religion, $caste, $dob, $aadhaar_number, $student_category,
                $admission_date, $sts_number, $batch, $class, $father_name, $mother_name,
                $mobile_primary, $mobile_secondary, $permanent_address
            ) = $data;

            if (empty($admission_number)) {
                $errorMessages[] = "⚠️ Row $rowNum: Admission number is missing.";
                continue;
            }

            $dob = convertDate($dob);
            $admission_date = convertDate($admission_date);

            $aadhaar_number = formatAadhaar($aadhaar_number); // No validation — always include

            // Check for duplicate
            $check = $conn->prepare("SELECT admission_number FROM students WHERE admission_number = ?");
            $check->bind_param("s", $admission_number);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows === 0) {
                $stmt = $conn->prepare("INSERT INTO students (
                    admission_number, student_name, gender, nationality, mother_tongue,
                    religion, caste, dob, aadhaar_number, student_category,
                    admission_date, sts_number, batch, class, father_name, mother_name,
                    mobile_primary, mobile_secondary, permanent_address
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("sssssssssssssssssss",
                    $admission_number, $student_name, $gender, $nationality, $mother_tongue,
                    $religion, $caste, $dob, $aadhaar_number, $student_category,
                    $admission_date, $sts_number, $batch, $class, $father_name, $mother_name,
                    $mobile_primary, $mobile_secondary, $permanent_address
                );

                if ($stmt->execute()) {
                    $qr_data = "http://$ip_address/student_qr_system/student.php?admission_number=" . urlencode($admission_number);
                    $filename = str_replace(['/', '\\'], '_', $admission_number);
                    QRcode::png($qr_data, "qrcodes/$filename.png", QR_ECLEVEL_L, 4);
                    $successCount++;
                } else {
                    $errorMessages[] = "❌ Row $rowNum: Insert error for $admission_number - " . $stmt->error;
                }
            } else {
                $errorMessages[] = "⚠️ Row $rowNum: Admission No $admission_number already exists.";
            }
        }

        fclose($handle);
    } else {
        $errorMessages[] = "❌ Failed to open the file.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bulk Upload</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7fafc;
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        form {
            background-color: #ffffff;
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 10px;
            color: #34495e;
        }
        input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background-color: #f1f1f1;
        }
        button {
            width: 100%;
            margin-top: 20px;
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
        .message {
            text-align: center;
            font-weight: bold;
            margin: 20px auto;
            max-width: 600px;
            padding: 15px;
            border-radius: 8px;
        }
        .success {
            color: #2ecc71;
            background: #eafaf1;
            border: 1px solid #2ecc71;
        }
        .error {
            color: #e74c3c;
            background: #fdecea;
            border: 1px solid #e74c3c;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<h2>📤 Bulk Upload Students (CSV)</h2>

<?php if ($successCount > 0): ?>
    <div class="message success"><?= $successCount ?> students uploaded successfully.</div>
<?php endif; ?>

<?php foreach ($errorMessages as $error): ?>
    <div class="message error"><?= $error ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">
    <label>Select CSV File (Exactly 19 columns):</label>
    <input type="file" name="csv_file" accept=".csv" required>
    <button type="submit">Upload & Generate QR</button>
</form>

</body>
</html>
