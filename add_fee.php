<?php
include 'db.php';

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admission_number = $_POST['admission_number'];
    $amount_paid = $_POST['amount_paid'];
    $paid_date = $_POST['paid_date'];

    $stmt = $conn->prepare("INSERT INTO fees (admission_number, paid_date, amount_paid) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $admission_number, $paid_date, $amount_paid);

    if ($stmt->execute()) {
        $message = "✅ Fees added successfully!";
        $messageClass = "success";
    } else {
        $message = "❌ Error: " . $stmt->error;
        $messageClass = "error";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Fee</title>
    <style>
        body { font-family: sans-serif; background: #f2f2f2; }
        .container { width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        h2 { text-align: center; color: #2c3e50; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        input, select { width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { width: 100%; padding: 10px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #2980b9; }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Student Fee</h2>

    <?php if (!empty($message)): ?>
        <div class="message <?= $messageClass ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="add_fee.php">
        <label>Admission Number</label>
        <input type="text" name="admission_number" required>

        <label>Fee Amount (₹)</label>
        <input type="number" name="amount_paid" min="0" required>

        <label>Fee Date</label>
        <input type="date" name="paid_date" required>

        <button type="submit">Add Fee</button>
    </form>
</div>

</body>
</html>
