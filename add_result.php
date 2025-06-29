<?php
include 'db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admission_number = $_POST['admission_number'];
    $year = $_POST['year'];
    $semester = $_POST['semester'];
    $marks_obtained = $_POST['marks_obtained'];

    $stmt = $conn->prepare("INSERT INTO results (admission_number, year, semester, marks_obtained) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $admission_number, $year, $semester, $marks_obtained);

    if ($stmt->execute()) {
        $message = "Result added successfully!";
        $messageClass = "success";
    } else {
        $message = "Error: " . $stmt->error;
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
    <title>Add Result</title>
    <style>
        body { font-family: sans-serif; background: #f2f2f2; }
        .container { width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        input, select { width: 100%; padding: 10px; margin-bottom: 10px; }
        button { width: 100%; padding: 10px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #2980b9; }
    </style>
</head>
<body>

<div class="container">
    <h2>Add Student Result</h2>

    <?php if (isset($message)): ?>
        <div class="message <?= $messageClass ?>"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="add_result.php">
        <label>Admission Number</label>
        <input type="text" name="admission_number" required>

        <label>Year</label>
        <select name="year" required>
            <option value="1st">1st Year</option>
            <option value="2nd">2nd Year</option>
            <option value="3rd">3rd Year</option>
        </select>

        <label>Semester</label>
        <select name="semester" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
        </select>

        <label>Marks Obtained</label>
        <input type="number" name="marks_obtained" min="0" max="100" required>

        <button type="submit">Add Result</button>
    </form>
</div>

</body>
</html>
