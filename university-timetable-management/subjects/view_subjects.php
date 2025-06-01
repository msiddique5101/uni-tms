<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';

// Fetch subjects with department and teacher names
$query = "SELECT s.subject_id, s.subject_code, s.subject_name, s.credit_hours, 
                 d.department_name, t.first_name AS teacher_first_name, t.last_name AS teacher_last_name, 
                 s.semester 
          FROM subjects s
          LEFT JOIN departments d ON s.department_id = d.department_id
          LEFT JOIN teachers t ON s.teacher_id = t.teacher_id";

$result = $conn->query($query);

// Check if query failed
if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<div class="content">
        <h2>Subjects List</h2>
        <a href="add_subject.php" class="btn btn-success" style="margin-bottom: 10px;">Add Subject</a>
        
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Credit Hours</th>
                <th>Department</th>
                <th>Teacher</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['subject_id']; ?></td>
                    <td><?= $row['subject_code']; ?></td>
                    <td><?= $row['subject_name']; ?></td>
                    <td><?= $row['credit_hours']; ?></td>
                    <td><?= $row['department_name'] ? $row['department_name'] : 'Not Assigned'; ?></td>
                    <td><?= $row['teacher_first_name'] . ' ' . $row['teacher_last_name']; ?></td>
                    <td><?= $row['semester']; ?></td>
                    <td>
                        <a href="edit_subject.php?id=<?= $row['subject_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm delete-subject" data-id="<?= $row['subject_id']; ?>">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on("click", ".delete-subject", function () {
    let subjectId = $(this).data("id");

    if (confirm("Are you sure you want to delete this subject?")) {
        $.ajax({
            url: "delete_subject.php",
            type: "POST",
            data: { id: subjectId },
            success: function (response) {
                if (response.trim() === "success") {
                    alert("Subject deleted successfully!");
                    location.reload();
                } else {
                    alert("Error deleting subject: " + response);
                }
            },
            error: function () {
                alert("AJAX Request Failed!");
            }
        });
    }
});
</script>

<?php include '../include/footer.php'; ?>
