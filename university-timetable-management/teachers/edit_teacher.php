<?php
include '../config/database.php';

// Validate teacher ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid teacher ID.");
}

$id = $_GET['id'];

// Fetch teacher details safely
$stmt = $conn->prepare("SELECT teacher_id, first_name, last_name, email, department_id, specialization FROM teachers WHERE teacher_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    die("Teacher not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : NULL;
    $specialization = $_POST['specialization'];

    // Update teacher details
    $stmt = $conn->prepare("UPDATE teachers SET first_name=?, last_name=?, email=?, department_id=?, specialization=? WHERE teacher_id=?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $department_id, $specialization, $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Teacher updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating teacher: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher</title>
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
    <h2 class="text-center mb-4">Edit Teacher</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= htmlspecialchars($teacher['teacher_id']); ?>">

        <div class="mb-3">
            <label class="form-label">First Name:</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($teacher['first_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Last Name:</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($teacher['last_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department ID:</label>
            <input type="number" name="department_id" class="form-control" value="<?= htmlspecialchars($teacher['department_id'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Specialization:</label>
            <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($teacher['specialization']); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Teacher</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
