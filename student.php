<?php
include 'db.php';

if (!isset($_GET['admission_number']) || empty($_GET['admission_number'])) {
    die("⚠️ No student selected.");
}

$admission_number = $_GET['admission_number'];

// Fetch student
$stmt = $conn->prepare("SELECT * FROM students WHERE admission_number = ?");
$stmt->bind_param("s", $admission_number);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();

// Fetch fees
$fees_stmt = $conn->prepare("SELECT * FROM fees WHERE admission_number = ?");
$fees_stmt->bind_param("s", $admission_number);
$fees_stmt->execute();
$fees_result = $fees_stmt->get_result();

// Fetch attendance
$att_stmt = $conn->prepare("SELECT * FROM attendance WHERE admission_number = ?");
$att_stmt->bind_param("s", $admission_number);
$att_stmt->execute();
$att_result = $att_stmt->get_result();
$attendance = $att_result->fetch_assoc();

// Fetch results
$res_stmt = $conn->prepare("SELECT * FROM results WHERE admission_number = ? ORDER BY year, semester");
$res_stmt->bind_param("s", $admission_number);
$res_stmt->execute();
$res_result = $res_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Information</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
        }

        .header-img {
            width: 100%;
            max-width: 500px;
            margin-bottom: 30px;
        }

        .student-photo {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            margin: 20px auto;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
            display: block;
        }

        .btn-group {
            margin-bottom: 20px;
        }

        .btn-group button {
            background-color: #3498db;
            border: none;
            color: white;
            padding: 12px 20px;
            margin: 0 5px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-group button:hover {
            background-color: #2980b9;
        }

        .section {
            display: none;
        }

        .active {
            display: block;
        }

        table {
            margin: 0 auto 30px;
            border-collapse: collapse;
            width: 90%;
            max-width: 800px;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        th, td {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #3498db;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .back-link {
            margin-top: 30px;
            display: inline-block;
            padding: 10px 20px;
            border: 1px solid #3498db;
            border-radius: 6px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            background-color: #3498db;
            color: white;
        }

        #fees,
        #attendance,
        #results {
            background-color: #fff;
            padding: 30px;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        #fees form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        #fees .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
        }

        #fees input[type="date"],
        #fees input[type="number"],
        #fees button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
            color: #333;
            width: 200px;
        }

        #fees button {
            background-color: #3498db;
            color: white;
            margin-top: 20px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            border: none;
        }

        #fees button:hover {
            background-color: #2980b9;
        }

        #fees th, #fees td,
        #attendance th, #attendance td,
        #results th, #results td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        #fees th, #attendance th, #results th {
            background-color: #3498db;
            color: white;
        }

        #fees p, #attendance p, #results p {
            font-size: 16px;
            color: #e74c3c;
        }
    </style>
</head>
<body>

    <img src="vvfgc.png" alt="College Logo" class="header-img">

    <div class="btn-group">
        <button onclick="showSection('details')">Student Details</button>
        <button onclick="showSection('fees')">Fees</button>
        <button onclick="showSection('attendance')">Attendance</button>
        <button onclick="showSection('results')">Test Results</button>
    </div>

    <!-- Student Details -->
    <div id="details" class="section active">
        <?php if ($student): ?>
            <h2>Student Details</h2>

            <?php if (!empty($student['photo_path']) && file_exists($student['photo_path'])): ?>
                <img src="<?= htmlspecialchars($student['photo_path']) ?>" alt="Student Photo" class="student-photo">
            <?php endif; ?>

            <table>
                <tr><th>Field</th><th>Details</th></tr>
                <?php foreach ($student as $key => $value): ?>
                    <?php if ($key != 'photo_path'): ?>
                        <tr>
                            <td><?= ucwords(str_replace("_", " ", htmlspecialchars($key))) ?></td>
                            <td><?= htmlspecialchars($value) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p style="color:red;">❌ Student not found.</p>
        <?php endif; ?>
    </div>

    <!-- Fees Section -->
    <div id="fees" class="section">
        <h2>Fees</h2>

        <?php if (isset($_GET['admin']) && $_GET['admin'] == 1): ?>
            <form method="POST" action="add_fee.php">
                <input type="hidden" name="admission_number" value="<?= htmlspecialchars($admission_number) ?>">
                <label>Fee Date: <input type="date" name="paid_date" required></label>
                <label>Amount Paid: <input type="number" name="amount_paid" step="0.01" required></label>
                <button type="submit">Add Fee</button>
            </form>
        <?php endif; ?>

        <?php if ($fees_result->num_rows > 0): ?>
            <table>
                <tr><th>Paid Date</th><th>Amount</th></tr>
                <?php while ($fee = $fees_result->fetch_assoc()): ?>
                    <tr><td><?= htmlspecialchars($fee['paid_date']) ?></td><td>₹<?= htmlspecialchars($fee['amount_paid']) ?></td></tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No fees records found.</p>
        <?php endif; ?>
    </div>

    <!-- Attendance Section -->
    <div id="attendance" class="section">
        <h2>Attendance</h2>
        <?php if ($attendance): ?>
            <table>
                <tr>
                    <th>Attendance Percentage</th>
                    <td style="background-color: #3498db; color:white;"><?= htmlspecialchars($attendance['attendance_percentage']) ?>%</td>
                </tr>
            </table>
        <?php else: ?>
            <p>No attendance record found.</p>
        <?php endif; ?>
    </div>

    <!-- Test Results Section -->
    <div id="results" class="section">
        <h2>Test Results</h2>
        <?php if ($res_result->num_rows > 0): ?>
            <table>
                <tr><th>Year</th><th>Semester</th><th>Marks Obtained</th></tr>
                <?php while ($res = $res_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($res['year']) ?></td>
                        <td><?= htmlspecialchars($res['semester']) ?></td>
                        <td><?= htmlspecialchars($res['marks_obtained']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No test result records found.</p>
        <?php endif; ?>
    </div>

    <a href="admin.php" class="back-link">← Back to Admin</a>

    <script>
        function showSection(id) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(id).classList.add('active');
        }
    </script>
</body>
</html>
