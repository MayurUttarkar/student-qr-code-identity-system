<?php
include 'db.php';
include 'phpqrcode/qrlib.php';

$ip_address = getHostByName(getHostName());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $admission_number = $_POST['admission_number'];
    $student_name = $_POST['student_name'];
    $gender = $_POST['gender'];
    $nationality = $_POST['nationality'];
    $mother_tongue = $_POST['mother_tongue'];
    $religion = $_POST['religion'];
    $caste = $_POST['caste'];
    $dob = $_POST['dob'];
    $aadhaar_number = $_POST['aadhaar_number'];
    $student_category = $_POST['student_category'];
    $admission_date = $_POST['admission_date'];
    $sts_number = $_POST['sts_number'];
    $class = $_POST['class'];
    $batch = $_POST['batch'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
    $mobile_primary = !empty($_POST['mobile_primary']) ? $_POST['mobile_primary'] : NULL;
    $mobile_secondary = !empty($_POST['mobile_secondary']) ? $_POST['mobile_secondary'] : NULL;
    $permanent_address = $_POST['permanent_address'];

    $paid_date = isset($_POST['paid_date']) ? $_POST['paid_date'] : NULL;
    $amount_paid = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : NULL;
    $attendance_percentage = isset($_POST['attendance_percentage']) ? floatval($_POST['attendance_percentage']) : NULL;
    $results = isset($_POST['results']) ? $_POST['results'] : NULL;

    // ✅ Handle student photo upload
    $photo_path = '';
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['student_photo']['tmp_name'];
        $ext = pathinfo($_FILES['student_photo']['name'], PATHINFO_EXTENSION);
        $safe_adm = preg_replace("/[^A-Za-z0-9]/", "_", $admission_number);
        $photo_path = "uploads/" . $safe_adm . "." . $ext;
        move_uploaded_file($tmp_name, $photo_path);
    }

    // Insert student
    $stmt = $conn->prepare("INSERT INTO students (
        admission_number, student_name, gender, nationality, mother_tongue,
        religion, caste, dob, aadhaar_number, student_category,
        admission_date, sts_number, class, batch, father_name, mother_name,
        mobile_primary, mobile_secondary, permanent_address, photo_path
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssssssssssssss",
        $admission_number, $student_name, $gender, $nationality, $mother_tongue,
        $religion, $caste, $dob, $aadhaar_number, $student_category,
        $admission_date, $sts_number, $class, $batch, $father_name, $mother_name,
        $mobile_primary, $mobile_secondary, $permanent_address, $photo_path
    );

    if ($stmt->execute()) {
        // QR Code generation
        $qr_data = "http://$ip_address/student_qr_system/student.php?admission_number=" . urlencode($admission_number);
        $clean_adm_no = str_replace(['/', '\\'], '_', $admission_number);
        $qr_file = "qrcodes/$clean_adm_no.png";
        QRcode::png($qr_data, $qr_file, QR_ECLEVEL_L, 4);

        // Fees
        if ($paid_date && $amount_paid !== NULL) {
            $fees_stmt = $conn->prepare("INSERT INTO fees (admission_number, paid_date, amount_paid) VALUES (?, ?, ?)");
            $fees_stmt->bind_param("ssd", $admission_number, $paid_date, $amount_paid);
            $fees_stmt->execute();
        }

        // Attendance
        if ($attendance_percentage !== NULL) {
            $att_stmt = $conn->prepare("INSERT INTO attendance (admission_number, attendance_percentage) VALUES (?, ?)");
            $att_stmt->bind_param("sd", $admission_number, $attendance_percentage);
            $att_stmt->execute();
        }

        // Results
        if (is_array($results)) {
            $res_stmt = $conn->prepare("INSERT INTO results (admission_number, year, semester, marks_obtained) VALUES (?, ?, ?, ?)");
            foreach ($results as $year => $semesters) {
                foreach ($semesters as $semester => $marks) {
                    $year = intval($year);
                    $semester = intval($semester);
                    $marks = floatval($marks);
                    $res_stmt->bind_param("siid", $admission_number, $year, $semester, $marks);
                    $res_stmt->execute();
                }
            }
        }

        // Success Page
        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>QR Code Generated</title>
            <style>
                body {
                    font-family: 'Segoe UI', sans-serif;
                    background-color: #f4f6f9;
                    padding: 40px;
                    text-align: center;
                }
                .container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 0 12px rgba(0,0,0,0.1);
                    max-width: 600px;
                    margin: auto;
                }
                img {
                    width: 200px;
                    height: 200px;
                    border: 3px solid #3498db;
                    border-radius: 12px;
                    margin-top: 20px;
                }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background: #3498db;
                    color: white;
                    border-radius: 6px;
                    text-decoration: none;
                    transition: background 0.3s ease;
                }
                a:hover {
                    background: #2980b9;
                }
                h2 {
                    color: #2c3e50;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>🎉 Student Registered Successfully</h2>
                <img src='$qr_file' alt='QR Code'><br>
                <a href='student.php?admission_number=" . urlencode($admission_number) . "'>🔍 View Student Details</a><br>
                <a href='admin.php'>← Back to Admin</a>
            </div>
        </body>
        </html>";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>
