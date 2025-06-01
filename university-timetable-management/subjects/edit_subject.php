<?php
include '../config/database.php';

// Validate subject ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid subject ID.");
}

$id = $_GET['id'];

// Fetch subject details safely
$stmt = $conn->prepare("SELECT subject_id, subject_code, subject_name, credit_hours, department_id, teacher_id, semester FROM subjects WHERE subject_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();

if (!$subject) {
    die("Subject not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $credit_hours = $_POST['credit_hours'];
    $department_id = $_POST['department_id'];
    $teacher_id = $_POST['teacher_id'];
    $semester = $_POST['semester'];

    // Update subject details
    $stmt = $conn->prepare("UPDATE subjects SET subject_code=?, subject_name=?, credit_hours=?, department_id=?, teacher_id=?, semester=? WHERE subject_id=?");
    $stmt->bind_param("sssiiii", $subject_code, $subject_name, $credit_hours, $department_id, $teacher_id, $semester, $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Subject updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating subject: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject</title>
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
    <h2 class="text-center mb-4">Edit Subject</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($subject['subject_id']); ?>">

        <div class="mb-3">
            <label class="form-label">Subject Code:</label>
            <input type="text" name="subject_code" class="form-control" value="<?= htmlspecialchars($subject['subject_code']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subject Name:</label>
            <input type="text" name="subject_name" class="form-control" value="<?= htmlspecialchars($subject['subject_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Credit Hours:</label>
            <input type="number" name="credit_hours" class="form-control" value="<?= htmlspecialchars($subject['credit_hours']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department ID:</label>
            <input type="number" name="department_id" class="form-control" value="<?= htmlspecialchars($subject['department_id']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Teacher ID:</label>
            <input type="number" name="teacher_id" class="form-control" value="<?= htmlspecialchars($subject['teacher_id']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Semester:</label>
            <input type="number" name="semester" class="form-control" value="<?= htmlspecialchars($subject['semester']); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Subject</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
