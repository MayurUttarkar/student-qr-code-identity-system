<?php 
include 'db.php'; 
session_start();
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

// Faculty account creation logic
$faculty_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_faculty'])) {
    $f_username = trim($_POST["faculty_username"]);
    $f_password = trim($_POST["faculty_password"]);

    $check = $conn->prepare("SELECT id FROM faculty_users WHERE username = ?");
    $check->bind_param("s", $f_username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $faculty_msg = "❌ Faculty username already exists.";
    } else {
        $hashed = md5($f_password);
        $stmt = $conn->prepare("INSERT INTO faculty_users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $f_username, $hashed);
        if ($stmt->execute()) {
            $faculty_msg = "✅ Faculty account created successfully.";
        } else {
            $faculty_msg = "❌ Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Students</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        div[style*="text-align: right"] a {
            color: #e74c3c;
            font-weight: bold;
            text-decoration: none;
            font-size: 16px;
        }

        div[style*="text-align: right"] a:hover {
            text-decoration: underline;
        }

        form {
            background-color: #ffffff;
            max-width: 700px;
            margin: 0 auto 40px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            color: #34495e;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-row div {
            width: 48%;
        }

        input[type="text"],
        input[type="password"],
        input[type="date"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #3498db;
            outline: none;
        }

        button {
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        a.file {
            display: inline-block;
            margin-top: 20px;
            margin-left: 10px;
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
            font-size: 15px;
            transition: color 0.3s ease;
        }

        a.file:hover {
            color: #21618c;
            text-decoration: underline;
        }

        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .section {
            display: none;
        }

        .error-msg {
            font-size: 12px;
            color: red;
            margin-top: 5px;
        }

        .attendance-box {
            border: 2px solid #27ae60;
            background-color: #ecf9f1;
            color: #2c3e50;
            font-weight: 600;
            transition: box-shadow 0.3s ease;
        }

        .attendance-box:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 5px rgba(39, 174, 96, 0.5);
        }

        @media (max-width: 768px) {
            form {
                padding: 20px;
            }

            button, a.file {
                width: 100%;
                text-align: center;
                margin-left: 0;
                margin-top: 10px;
            }

            .form-row div {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div style="text-align: right; margin-bottom: 20px;">
        <a href="logout.php">🚪 Logout</a>
    </div>

    <h2>Admin Panel</h2>

    <div style="text-align: center; margin-bottom: 20px;">
        <button onclick="toggleSection('faculty-section')">Create Faculty</button>
        <button onclick="toggleSection('fee-section')">Add Fee</button>
        <button onclick="toggleSection('attendance-section')">Update Attendance</button>
        <button onclick="toggleSection('result-section')">Add Result</button>
    </div>

    <h2>Register Student</h2>
    <form action="save_student.php" method="POST" id="studentForm" enctype="multipart/form-data" novalidate>
        <div class="form-row">
            <div>
                <label>Admission Number:</label>
                <input type="text" name="admission_number" required>
            </div>
            <div>
                <label>Student Name:</label>
                <input type="text" name="student_name" required>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label>Gender:</label>
                <select name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div>
                <label>Nationality:</label>
                <input type="text" name="nationality" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Mother Tongue:</label>
                <input type="text" name="mother_tongue" required>
            </div>
            <div>
                <label>Religion:</label>
                <input type="text" name="religion" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Caste:</label>
                <input type="text" name="caste" required>
            </div>
            <div>
                <label>Date of Birth:</label>
                <input type="date" name="dob" id="dob" required>
                <div class="error-msg" id="dob-error"></div>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Aadhaar Number:</label>
                <input type="text" name="aadhaar_number" id="aadhaar_number" required>
                <div class="error-msg" id="aadhaar-error"></div>
            </div>
            <div>
                <label>Student Category:</label>
                <input type="text" name="student_category" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Admission Date:</label>
                <input type="date" name="admission_date" id="admission_date" required>
                <div class="error-msg" id="admission-error"></div>
            </div>
            <div>
                <label>STS Number:</label>
                <input type="text" name="sts_number" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Class:</label>
                <select name="class" required>
                    <option value="6B1">6B1</option>
                    <option value="6B2">6B2</option>
                    <option value="6B3">6B3</option>
                </select>
            </div>
            <div>
                <label>Batch:</label>
                <input type="text" name="batch" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Father's Name:</label>
                <input type="text" name="father_name" required>
            </div>
            <div>
                <label>Mother's Name:</label>
                <input type="text" name="mother_name" required>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>Primary Mobile Number:</label>
                <input type="text" name="mobile_primary" id="mobile_primary" required>
                <div class="error-msg" id="mobile-error"></div>
            </div>
            <div>
                <label>Secondary Mobile Number (Optional):</label>
                <input type="text" name="mobile_secondary" id="mobile_secondary">
                <div class="error-msg" id="mobile2-error"></div>
            </div>
        </div>

        <div class="form-row">
            <div style="width: 100%;">
                <label>Permanent Address:</label>
                <input type="text" name="permanent_address" required>
            </div>
        </div>
        <div class="form-row">
    <div style="width: 100%;">
        <label>Upload Student Photo:</label>
        <input type="file" name="student_photo" accept="image/*" required>
        <div class="error-msg" id="photo-error"></div>
    </div>
</div>


        <button type="submit">Save Student</button>
        <a class="file" href="bulk_upload.php">📁 Bulk Upload Students</a>
    </form>

    <!-- Create Faculty Section -->
    <div id="faculty-section" class="section">
        <h2>Create Faculty Account</h2>
        <form method="POST">
            <label>Faculty Username:</label>
            <input type="text" name="faculty_username" required>
            <label>Password:</label>
            <input type="password" name="faculty_password" required>
            <button type="submit" name="create_faculty">Create Faculty</button>
            <?php if (!empty($faculty_msg)) : ?>
                <div class="message <?= strpos($faculty_msg, '✅') !== false ? 'success' : 'error' ?>">
                    <?= $faculty_msg ?>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Add Fee Section -->
    <div id="fee-section" class="section">
        <h2>Add Student Fee</h2>
        <form action="add_fee.php" method="POST">
            <label>Admission Number:</label>
            <input type="text" name="admission_number" required>
            <label>Fee Amount (₹):</label>
            <input type="number" name="amount_paid" required>
            <label>Fee Date:</label>
            <input type="date" name="paid_date" required>
            <button type="submit">Add Fee</button>
            <a class="file" href="bulk_upload_fees.php">📁 Bulk Upload Fees (CSV)</a>
        </form>
    </div>

    <!-- Attendance Section -->
    <div id="attendance-section" class="section">
        <h2>Update Attendance</h2>
        <form action="update_attendance.php" method="POST">
            <div class="form-row">
                <div>
                    <label>Admission Number:</label>
                    <input type="text" name="admission_number" required>
                </div>
                <div>
                    <label>Attendance Percentage (%):</label>
                    <input type="number" name="attendance_percentage" min="0" max="100" class="attendance-box" required>
                </div>
            </div>
            <button type="submit">Update Attendance</button>
            <a class="file" href="bulk_upload_attendance.php">📁 Bulk Upload Attendance (CSV)</a>
        </form>
    </div>

    <!-- Result Section -->
    <div id="result-section" class="section">
        <h2>Add Student Result</h2>
        <form action="add_result.php" method="POST">
            <div class="form-row">
                <div>
                    <label>Admission Number:</label>
                    <input type="text" name="admission_number" required>
                </div>
                <div>
                    <label>Academic Year:</label>
                    <select name="year" required>
                        <option value="">-- Select Year --</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label>Semester:</label>
                    <select name="semester" required>
                        <option value="">-- Select Semester --</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                    </select>
                </div>
                <div>
                    <label>Marks Obtained:</label>
                    <input type="number" name="marks_obtained" required>
                </div>
            </div>

            <button type="submit">Add Result</button>
            <a class="file" href="bulk_upload_result.php">📁 Bulk Upload Results</a>
        </form>
    </div>

    <!-- JavaScript for section toggling and validations -->
    <script>
        function toggleSection(sectionId) {
            document.querySelectorAll('.section').forEach(sec => sec.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }

        document.getElementById("mobile_primary").addEventListener("blur", function () {
            let val = this.value.trim();
            const error = document.getElementById("mobile-error");
            error.textContent = (val.length !== 10 || isNaN(val)) ? "Enter valid 10-digit mobile number" : "";
        });

        document.getElementById("mobile_secondary").addEventListener("blur", function () {
            let val = this.value.trim();
            const error = document.getElementById("mobile2-error");
            error.textContent = (val !== "" && (val.length !== 10 || isNaN(val))) ? "Enter valid 10-digit mobile number" : "";
        });

        document.getElementById("aadhaar_number").addEventListener("blur", function () {
            let val = this.value.trim();
            const error = document.getElementById("aadhaar-error");
            error.textContent = (val.length !== 12 || isNaN(val)) ? "Enter valid 12-digit Aadhaar number" : "";
        });

        document.getElementById("dob").addEventListener("blur", function () {
            const dob = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            const dayDiff = today.getDate() - dob.getDate();
            const is18 = (age > 18) || (age === 18 && (monthDiff > 0 || (monthDiff === 0 && dayDiff >= 0)));
            const error = document.getElementById("dob-error");
            error.textContent = is18 ? "" : "Student must be at least 18 years old";
        });

        document.getElementById("admission_date").addEventListener("blur", function () {
            const input = new Date(this.value);
            const today = new Date();
            const error = document.getElementById("admission-error");
            error.textContent = input > today ? "Admission date cannot be in the future" : "";
        });

        document.querySelector('input[name="student_photo"]').addEventListener("change", function () {
    const file = this.files[0];
    const error = document.getElementById("photo-error");

    if (!file || !file.type.startsWith("image/")) {
        error.textContent = "Please upload a valid image file.";
    } else {
        error.textContent = "";
    }
});

    </script>
</body>
</html>

