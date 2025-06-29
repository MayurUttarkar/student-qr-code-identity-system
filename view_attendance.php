<?php
session_start();
include 'db.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

// Fetch students with attendance
$sql = "
    SELECT s.student_name, s.admission_number, a.attendance_percentage
    FROM attendance a
    JOIN students s ON a.admission_number = s.admission_number
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Attendance</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #ecf0f1;
            padding: 30px;
        }

        .container {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            max-width: 700px;
            margin: auto;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        /* Back to Dashboard Button */
        .back-btn {
            display: block;
            width: 180px;
            margin: 0 auto 25px auto;
            padding: 12px 0;
            text-align: center;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .back-btn:hover {
            background-color: #2980b9;
            box-shadow: 0 7px 20px rgba(41, 128, 185, 0.6);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📋 Student Attendance Overview</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Admission No.</th>
                <th>Attendance %</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['admission_number']) ?></td>
                    <td><?= htmlspecialchars($row['attendance_percentage']) ?>%</td>
                </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <a href="index.php" class="back-btn">Back to Dashboard</a>
    <?php else: ?>
        <p>No student attendance records found.</p>
    <?php endif; ?>
</div>

</body>
</html>
