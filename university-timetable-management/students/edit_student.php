<?php
include '../config/database.php';

// Validate student ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student ID.");
}

$id = $_GET['id'];

// Fetch student details safely
$stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, department FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $department = !empty($_POST['department']) ? $_POST['department'] : NULL;

    // Update student details
    $stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, email=?, department=? WHERE student_id=?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $department, $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Student updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating student: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Edit Student</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($student['student_id']); ?>">

        <div class="mb-3">
            <label class="form-label">First Name:</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Last Name:</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department (optional):</label>
            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($student['department'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Student</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
