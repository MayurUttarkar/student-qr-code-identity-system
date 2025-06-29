<?php
session_start();
include 'db.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect all POST data
    $admission_number = $_POST['admission_number'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $mother_tongue = $_POST['mother_tongue'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $caste = $_POST['caste'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $aadhaar_number = $_POST['aadhaar_number'] ?? '';
    $student_category = $_POST['student_category'] ?? '';
    $admission_date = $_POST['admission_date'] ?? '';
    $sts_number = $_POST['sts_number'] ?? '';
    $class = $_POST['class'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $mobile_primary = $_POST['mobile_primary'] ?? '';
    $mobile_secondary = $_POST['mobile_secondary'] ?? '';
    $permanent_address = $_POST['permanent_address'] ?? '';

    if (empty($admission_number)) {
        die("Admission number is required.");
    }

    // Prepare update query
    $stmt = $conn->prepare("UPDATE students SET 
        student_name = ?, gender = ?, nationality = ?, mother_tongue = ?, religion = ?, caste = ?, dob = ?, 
        aadhaar_number = ?, student_category = ?, admission_date = ?, sts_number = ?, class = ?, 
        father_name = ?, mother_name = ?, mobile_primary = ?, mobile_secondary = ?, permanent_address = ? 
        WHERE admission_number = ?");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssssssssssssssss",
        $student_name, $gender, $nationality, $mother_tongue, $religion, $caste, $dob,
        $aadhaar_number, $student_category, $admission_date, $sts_number, $class,
        $father_name, $mother_name, $mobile_primary, $mobile_secondary, $permanent_address,
        $admission_number
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Student details updated successfully.";
        // ✅ Change this to the correct redirect page
        header("Location: view_students.php"); // or view_students.php if it exists
        exit;
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}
?>
