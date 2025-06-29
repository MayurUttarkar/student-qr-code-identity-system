<?php
session_start();
include 'db.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

// Count total students
$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];

// Count students by batch
$batch_counts = [];
$batches = ["1st year", "2nd year", "3rd year"];
foreach ($batches as $batch) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM students WHERE batch = ?");
    $stmt->bind_param("s", $batch);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $batch_counts[$batch] = $result['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: right;
            margin-bottom: 20px;
        }

        .header a {
            text-decoration: none;
            font-weight: bold;
            color: #e74c3c;
            background: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin-bottom: 8px;
            font-size: 16px;
        }

        .card span {
            font-size: 24px;
            font-weight: bold;
        }

        .total { background-color: #8e44ad; color: white; }
        .attendance { background-color: #2980b9; color: white; }
        .testmarks { background-color: #c0392b; color: white; }
    </style>
</head>
<body>

<div class="header">
    <a href="logout.php">🚪 Logout</a>
</div>

<h2>📊 Admin Dashboard</h2>

<div class="dashboard">
    <div class="card total">
        <h3>Total Students</h3>
        <span><?= $total_students ?></span>
    </div>

    <div class="card batch1">
        <h3>1st Year</h3>
        <span><?= $batch_counts["1st year"] ?? 0 ?></span>
    </div>

    <div class="card batch2">
        <h3>2nd Year</h3>
        <span><?= $batch_counts["2nd year"] ?? 0 ?></span>
    </div>

    <div class="card batch3">
        <h3>3rd Year</h3>
        <span><?= $batch_counts["3rd year"] ?? 0 ?></span>
    </div>

    <a href="view_attendance.php" style="text-decoration: none;">
    <div class="card attendance">
        <h3>Attendance</h3>
        <span>View Records</span>
    </div>
    </a>


    <div class="card testmarks">
        <h3><a href="view_testmarks.php" style="color: white; text-decoration: none;">Test Marks</a></h3>
    </div>

</div>

</body>
</html>
