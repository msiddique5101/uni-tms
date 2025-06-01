<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';
?>

<div class="content">
    <h2>Student List</h2>

    <!-- Add Student Button -->
    <a href="add_student.php" class="btn btn-success" style="margin-bottom: 10px;">+ Add Student</a>

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Name</th><?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/include/functions.php";
checkAccess(basename(__FILE__));
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/include/header.php";
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/include/navbar.php";
?>

<div class="container mt-4">
    <h4 class="mb-3"><i class="fas fa-user-graduate"></i> Students</h4>
    <!-- Page content goes here -->
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/include/footer.php"; ?>

            <th>Email</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT student_id, first_name, last_name, email, department FROM students");

        while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['student_id']; ?></td>
                <td><?= $row['first_name'] . ' ' . $row['last_name']; ?></td>
                <td><?= $row['email']; ?></td>
                <td><?= $row['department'] ?? 'N/A'; ?></td>
                <td>
                    <a href="edit_student.php?id=<?= $row['student_id']; ?>" class="btn btn-primary">Edit</a>
                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $row['student_id']; ?>)" class="btn btn-danger">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this student?")) {
        window.location.href = "delete_student.php?id=" + id;
    }
}
</script>
<script src="../assets/js/script.js"></script>
<?php include '../include/footer.php'; ?>
