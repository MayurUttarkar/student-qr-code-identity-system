<?php
session_start();
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Student to Edit</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            padding: 50px;
        }

        .container {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #2980b9;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1f6391;
        }

        .back {
            text-align: center;
            margin-top: 20px;
        }

        .back a {
            text-decoration: none;
            color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🔍 Search Student to Edit</h2>
    <form method="GET" action="edit_student.php">
        <input type="text" name="admission_number" placeholder="Enter Admission Number" required>
        <button type="submit">Edit Student</button>
    </form>

    <div class="back">
        <a href="index.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>
