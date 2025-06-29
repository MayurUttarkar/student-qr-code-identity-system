<?php
session_start();
include 'db.php'; 
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    $sql = "SELECT * FROM admin_users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $hashed_password = md5($password); // Match hashed password
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION["admin_logged_in"] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f1f5f9;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        margin-top: 40px;
        color: #2c3e50;
    }

    form {
        background: #ffffff;
        max-width: 400px;
        margin: 30px auto;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #34495e;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-sizing: border-box;
        margin-bottom: 20px;
        font-size: 16px;
    }

    button {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        background: #3498db;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    button:hover {
        background: #2980b9;
    }

    p {
        text-align: center;
        font-weight: bold;
    }
</style>
</head>
<body>
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label>Username:</label>
        <input type="text" name="username" required><br><br>
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
        <p style="text-align:center;">Don't have an account? <a href="signup.php">Sign up here</a></p>
    </form>
</body>
</html>
