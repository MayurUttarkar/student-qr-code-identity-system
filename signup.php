<?php
include 'db.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Check if user already exists
    $check = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $message = "❌ Username already taken. Try another.";
    } else {
        $hashed_password = md5($password); // Store hashed password
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $message = "✅ Account created successfully. <a href='login.php'>Login now</a>.";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Signup</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            padding: 40px;
        }

        form {
            max-width: 400px;
            background: #fff;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        label {
            display: block;
            margin-top: 15px;
            color: #34495e;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #219150;
        }

        .message {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Admin Signup</h2>

<form method="post" action="">
    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <button type="submit">Sign Up</button>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</form>

</body>
</html>
