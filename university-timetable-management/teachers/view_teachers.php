<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';

// Fetch teachers with department name
$query = "SELECT t.teacher_id, t.first_name, t.last_name, t.email, d.department_name 
          FROM teachers t
          LEFT JOIN departments d ON t.department_id = d.department_id";
$result = $conn->query($query);
?>

<div class="content">
        <h2>Teacher List</h2>
        <a href="add_teacher.php" class="btn btn-success" style="margin-bottom: 10px;">Add Teacher</a>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr id="row-<?= $row['teacher_id']; ?>">
                    <td><?= $row['teacher_id']; ?></td>
                    <td><?= $row['first_name'] . ' ' . $row['last_name']; ?></td>
                    <td><?= $row['email']; ?></td>
                    <td><?= $row['department_name'] ? $row['department_name'] : 'Not Assigned'; ?></td>
                    <td>
                        <a href="edit_teacher.php?id=<?= $row['teacher_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm delete-teacher" data-id="<?= $row['teacher_id']; ?>">Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $(".delete-teacher").click(function () {
            let teacherId = $(this).data("id");

            if (confirm("Are you sure you want to delete this teacher?")) {
                $.ajax({
                    url: "delete_teacher.php",
                    type: "POST",
                    data: { id: teacherId },
                    success: function (response) {
                        if (response.trim() === "success") {
                            $("#row-" + teacherId).fadeOut();
                        } else {
                            alert("Error deleting teacher.");
                        }
                    },
                    error: function () {
                        alert("An error occurred. Please try again.");
                    }
                });
            }
        });
    });
</script>

<?php include '../include/footer.php'; ?>
