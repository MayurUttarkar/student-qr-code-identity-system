<?php
session_start();
include 'db.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['admission_number'])) {
    echo "No admission number provided.";
    exit;
}

$admission_number = $_GET['admission_number'];
$stmt = $conn->prepare("SELECT * FROM students WHERE admission_number = ?");
$stmt->bind_param("s", $admission_number);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo "Student not found.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            padding: 40px;
        }

        .container {
            background: white;
            padding: 25px 30px;
            border-radius: 10px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            resize: vertical;
        }

        .full-width {
            grid-column: 1 / -1;
            text-align: center;
        }

        .full-width button {
            background-color: #27ae60;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .full-width button:hover {
            background-color: #219150;
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
    <h2>✏️ Edit Student Details</h2>

    <form action="update_student.php" method="POST">
        <input type="hidden" name="admission_number" value="<?= htmlspecialchars($student['admission_number']) ?>">

        <div>
            <label>Student Name</label>
            <input type="text" name="student_name" value="<?= htmlspecialchars($student['student_name']) ?>" required>
        </div>

        <div>
            <label>Gender</label>
            <select name="gender" required>
                <option value="Male" <?= $student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $student['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div>
            <label>Nationality</label>
            <input type="text" name="nationality" value="<?= htmlspecialchars($student['nationality']) ?>" required>
        </div>

        <div>
            <label>Mother Tongue</label>
            <input type="text" name="mother_tongue" value="<?= htmlspecialchars($student['mother_tongue']) ?>" required>
        </div>

        <div>
            <label>Religion</label>
            <input type="text" name="religion" value="<?= htmlspecialchars($student['religion']) ?>" required>
        </div>

        <div>
            <label>Caste</label>
            <input type="text" name="caste" value="<?= htmlspecialchars($student['caste']) ?>" required>
        </div>

        <div>
            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= htmlspecialchars($student['dob']) ?>" required>
        </div>

        <div>
            <label>Aadhaar Number</label>
            <input type="text" name="aadhaar_number" value="<?= htmlspecialchars($student['aadhaar_number']) ?>" required>
        </div>

        <div>
            <label>Student Category</label>
            <input type="text" name="student_category" value="<?= htmlspecialchars($student['student_category']) ?>" required>
        </div>

        <div>
            <label>Admission Date</label>
            <input type="date" name="admission_date" value="<?= htmlspecialchars($student['admission_date']) ?>" required>
        </div>

        <div>
            <label>STS Number</label>
            <input type="text" name="sts_number" value="<?= htmlspecialchars($student['sts_number']) ?>" required>
        </div>

        <div>
            <label>Class</label>
            <input type="text" name="class" value="<?= htmlspecialchars($student['class']) ?>" required>
        </div>

        <div>
            <label>Father Name</label>
            <input type="text" name="father_name" value="<?= htmlspecialchars($student['father_name']) ?>" required>
        </div>

        <div>
            <label>Mother Name</label>
            <input type="text" name="mother_name" value="<?= htmlspecialchars($student['mother_name']) ?>" required>
        </div>

        <div>
            <label>Mobile (Primary)</label>
            <input type="text" name="mobile_primary" value="<?= htmlspecialchars($student['mobile_primary']) ?>" required>
        </div>

        <div>
            <label>Mobile (Secondary)</label>
            <input type="text" name="mobile_secondary" value="<?= htmlspecialchars($student['mobile_secondary']) ?>">
        </div>

        <div class="full-width">
            <label>Permanent Address</label>
            <textarea name="permanent_address" required><?= htmlspecialchars($student['permanent_address']) ?></textarea>
        </div>

        <div class="full-width">
            <button type="submit">Update Student</button>
        </div>
    </form>

    <div class="back">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
