<?php
include '../include/header.php';
include '../config/database.php';

$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $credit_hours = $_POST['credit_hours'];
    $department_id = $_POST['department_id'];
    $teacher_id = $_POST['teacher_id'];
    $semester = $_POST['semester'];

    $insert_query = "INSERT INTO subjects (subject_code, subject_name, credit_hours, department_id, teacher_id, semester) 
                     VALUES ('$subject_code', '$subject_name', '$credit_hours', '$department_id', '$teacher_id', '$semester')";

    if ($conn->query($insert_query)) {
        $success_message = "Subject added successfully!";
    } else {
        $error_message = "Error adding subject: " . $conn->error;
    }
}
?>

<div class="container">
    <h2 class="mt-4">Add Subject</h2>
    <?php if ($error_message) { ?>
        <div class="alert alert-danger"><?= $error_message; ?></div>
    <?php } ?>
    <?php if ($success_message) { ?>
        <div class="alert alert-success"><?= $success_message; ?></div>
    <?php } ?>

    <form action="add_subject.php" method="POST">
        <div class="mb-3">
            <label>Subject Code</label>
            <input type="text" name="subject_code" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>Subject Name</label>
            <input type="text" name="subject_name" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>Credit Hours</label>
            <input type="number" name="credit_hours" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>Department ID</label>
            <input type="number" name="department_id" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>Teacher ID</label>
            <input type="number" name="teacher_id" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>Semester</label>
            <input type="number" name="semester" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-success">Add Subject</button>
        <a href="view_subjects.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include '../include/footer.php'; ?>
