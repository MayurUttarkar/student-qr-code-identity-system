<?php
include 'db.php';

if (isset($_POST["upload"])) {
    if ($_FILES["file"]["error"] == 0 && $_FILES["file"]["type"] == "text/csv") {
        $file = fopen($_FILES["file"]["tmp_name"], "r");
        $inserted = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) !== 4) {
                $skipped++;
                continue;
            }

            $admission_number = trim($row[0]);
            $year = trim($row[1]);
            $semester = (int)$row[2];
            $marks_obtained = (int)$row[3];

            // Check if admission_number exists in students table
            $check = $conn->prepare("SELECT admission_number FROM students WHERE admission_number = ?");
            $check->bind_param("s", $admission_number);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows === 0) {
                $skipped++;
                continue;
            }

            $stmt = $conn->prepare("INSERT INTO results (admission_number, year, semester, marks_obtained) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $admission_number, $year, $semester, $marks_obtained);
            
            if ($stmt->execute()) {
                $inserted++;
            } else {
                $skipped++;
            }

            $stmt->close();
            $check->close();
        }

        fclose($file);
        $message = "Upload completed: $inserted inserted";
    } else {
        $message = "Please upload a valid CSV file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bulk Upload Results</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
        margin: 0;
        padding: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container {
        background: white;
        padding: 30px 40px;
        border-radius: 15px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        max-width: 500px;
        width: 90%;
        box-sizing: border-box;
        text-align: center;
    }

    h2 {
        margin-bottom: 25px;
        color: #34495e;
        font-weight: 700;
        font-size: 28px;
    }

    .message {
        padding: 15px;
        margin-bottom: 25px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        background-color: #d1e7ff;
        color: #0b3d91;
        box-shadow: 0 3px 6px rgba(11, 61, 145, 0.2);
        user-select: none;
    }

    input[type="file"] {
        display: block;
        width: 100%;
        padding: 12px 10px;
        margin-bottom: 25px;
        font-size: 16px;
        border: 2px dashed #2980b9;
        border-radius: 10px;
        cursor: pointer;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }

    input[type="file"]:hover {
        border-color: #1c5980;
    }

    button {
        width: 100%;
        padding: 14px 0;
        background-color: #2980b9;
        border: none;
        border-radius: 12px;
        color: white;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 6px 15px rgba(41, 128, 185, 0.4);
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    button:hover {
        background-color: #1c5980;
        box-shadow: 0 8px 20px rgba(28, 89, 128, 0.6);
    }
    </style>
</head>
<body>
<div class="container">
    <h2>Bulk Upload Student Results (CSV)</h2>

    <?php if (isset($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form action="bulk_upload_result.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="file" accept=".csv" required>
        <button type="submit" name="upload">Upload Results</button>
    </form>
</div>
</body>
</html>
