<?php
session_start();
include 'db.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

// Fetch all students
$result = $conn->query("SELECT * FROM students ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Students</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 30px;
        }

        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: auto;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #27ae60;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .action-btn {
            background-color: #3498db;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .action-btn:hover {
            background-color: #2980b9;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }

        .back {
            text-align: center;
            margin-top: 20px;
        }

        .back a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📋 All Registered Students</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <p class="message"><?= $_SESSION['success_message'] ?></p>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>DOB</th>
                    <th>Mobile</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['admission_number']) ?></td>
                        <td><?= htmlspecialchars($student['student_name']) ?></td>
                        <td><?= htmlspecialchars($student['class']) ?></td>
                        <td><?= htmlspecialchars($student['dob']) ?></td>
                        <td><?= htmlspecialchars($student['mobile_primary']) ?></td>
                        <td>
                            <a href="edit_student.php?admission_number=<?= urlencode($student['admission_number']) ?>" class="action-btn">✏️ Edit</a>
                            <a href="delete_student.php?admission_number=<?= urlencode($student['admission_number']) ?>" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>

                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No student records found.</p>
    <?php endif; ?>

    <div class="back">
        <a href="index.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
