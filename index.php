<?php
session_start();
if (!isset($_SESSION["faculty_logged_in"])) {
    header("Location: faculty_login.php");
    exit;
}
include 'db.php';

$faculty_name = $_SESSION["faculty_name"] ?? "";
$search_term = $_GET['search'] ?? "";

// Fetch students with optional search
if (!empty($search_term)) {
    $like = '%' . $search_term . '%';
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_name LIKE ? OR admission_number LIKE ?");
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM students");
}

$stmt->execute();
$result = $stmt->get_result();

$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard - Student QR System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h2 { margin: 0; }
        .profile span { font-weight: bold; }
        .profile a {
            color: white;
            margin-left: 10px;
            text-decoration: none;
            background: #3498db;
            padding: 6px 12px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .profile a:hover { background: #2980b9; }

        .main-container {
            display: flex;
            justify-content: space-between;
            padding: 20px 40px;
            flex-wrap: wrap;
        }

        .dashboard {
            width: 25%;
            display: flex;
            flex-direction: column;
        }

        .dashboard .card {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            color: white;
            text-align: center;
            font-size: 16px;
            transition: 0.3s;
        }

        .dashboard .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.1);
        }

        .dashboard .card h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .dashboard .card p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .total { background: #8e44ad; }
        .attendance { background: #2980b9; }
        .testmarks { background: #c0392b; }

        .card.editstudent { background-color: #f39c12; }
        .card.deletestudent { background-color: #2e8a00; }

        .card.editstudent:hover,
        .card.deletestudent:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        .qr-list {
            width: 70%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 30px;
            padding: 20px 0;
        }

        .qr-item {
            text-align: center;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s;
            text-decoration: none;
            color: inherit;
        }

        .qr-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.08);
        }

        .qr-item img {
            width: 160px;
            height: 160px;
            margin-bottom: 10px;
        }

        .qr-item .name {
            font-weight: 600;
            font-size: 18px;
            color: #34495e;
            margin-bottom: 5px;
        }

        .qr-item div {
            font-size: 14px;
            color: #555;
        }

        .search-bar {
            width: 100%;
            text-align: center;
            margin: 20px 0;
        }

        .search-bar input[type="text"] {
            width: 60%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .search-bar button {
            padding: 10px 20px;
            background: #3498db;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 10px;
        }

        .search-bar button:hover {
            background: #2980b9;
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
                align-items: center;
            }
            .dashboard, .qr-list {
                width: 100%;
            }
            .search-bar input[type="text"] {
                width: 90%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h2>📚 Student Dashboard</h2>
    <div class="profile">
        Welcome, <span><?= htmlspecialchars($faculty_name) ?></span>
        <a href="edit_profile.php">Edit Profile</a>
        <a href="faculty_logout.php" style="background:#e74c3c;">Logout</a>
    </div>
</div>

<div class="search-bar">
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by name or admission number" value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit">Search</button>
    </form>
</div>

<div class="main-container">
    <!-- Sidebar: Dashboard Cards -->
    <div class="dashboard">
        <div class="card total">
            <h3>Total Students</h3>
            <p><?= $total_students ?></p>
        </div>
        <a href="view_attendance.php" style="text-decoration: none;">
            <div class="card attendance">
                <h3>Attendance</h3>
                <span>View Records</span>
            </div>
        </a>
        <a href="view_testmarks.php" style="text-decoration: none;">
            <div class="card testmarks">
                <h3>Test Marks</h3>
                <span>View Records</span>
            </div>
        </a>
        <a href="search_student_edit.php" style="text-decoration: none;">
            <div class="card editstudent">
                <h3>Edit Student</h3>
                <span>Update Info</span>
            </div>
        </a>
        <a href="delete_student.php" style="text-decoration: none;">
            <div class="card deletestudent">
                <h3>Delete Student</h3>
                <span>Remove Record</span>
            </div>
        </a>
    </div>

    <!-- QR Code Cards -->
    <div class="qr-list">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                $admission_number = htmlspecialchars($row['admission_number']);
                $qrFile = 'qrcodes/' . str_replace(['/', '\\'], '_', $admission_number) . '.png';
                $student_name = htmlspecialchars($row['student_name']);
            ?>
            <a href="student.php?admission_number=<?= urlencode($admission_number) ?>" class="qr-item">
                <img src="<?= $qrFile ?>" alt="QR Code for <?= $student_name ?>" onerror="this.src='placeholder.png';">
                <div class="name"><?= $student_name ?></div>
                <div>Admission No: <?= $admission_number ?></div>
            </a>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
