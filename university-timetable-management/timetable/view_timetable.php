<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/sidebar.php';
include '../include/navbar.php';
include '../config/database.php';

$user_role = strtolower($_SESSION['user_role']);
$user_id = $_SESSION['user_id'];

$sql = "";

if ($user_role === 'admin') {
    $sql = "SELECT tt.*, 
                u.name AS teacher_name, 
                c.name AS class_name, 
                s.subject_name, 
                r.room_number
            FROM timetables tt 
            JOIN users u ON tt.teacher_id = u.user_id 
            JOIN classes c ON tt.class_id = c.id 
            JOIN subjects s ON tt.subject_id = s.subject_id
            JOIN classrooms r ON tt.room_id = r.room_id";

} elseif ($user_role === 'teacher') {
    $sql = "SELECT tt.*, 
                u.name AS teacher_name, 
                c.name AS class_name, 
                s.subject_name, 
                r.room_number
            FROM timetables tt 
            JOIN users u ON tt.teacher_id = u.user_id 
            JOIN classes c ON tt.class_id = c.id 
            JOIN subjects s ON tt.subject_id = s.subject_id
            JOIN classrooms r ON tt.room_id = r.room_id
            WHERE tt.teacher_id = $user_id";

} elseif ($user_role === 'student') {
    $class_query = "SELECT class_id FROM students WHERE student_id = $user_id";
    $class_result = $conn->query($class_query);

    if ($class_result && $class_result->num_rows > 0) {
        $class_row = $class_result->fetch_assoc();
        $class_id = $class_row['class_id'];

        $sql = "SELECT tt.*, 
                    u.name AS teacher_name, 
                    c.name AS class_name, 
                    s.subject_name, 
                    r.room_number  
                FROM timetables tt 
                JOIN users u ON tt.teacher_id = u.user_id 
                JOIN classes c ON tt.class_id = c.id 
                JOIN subjects s ON tt.subject_id = s.subject_id
                JOIN classrooms r ON tt.room_id = r.room_id
                WHERE tt.class_id = $class_id";
    } else {
        echo "<div class='alert alert-danger'>Student class not found.</div>";
        include '../include/footer.php';
        exit;
    }

} else {
    echo "<div class='alert alert-danger'>Invalid user role.</div>";
    include '../include/footer.php';
    exit;
}
?>


<div class="container mt-4">
    <h4 class="mb-3"><i class="fas fa-calendar-alt"></i> Timetable</h4>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Timetable deleted successfully.</div>
    <?php elseif (isset($_GET['deleted_all'])): ?>
        <div class="alert alert-success">All timetable records have been terminated successfully.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
        <div class="alert alert-danger">Failed to delete timetable.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'missing_id'): ?>
        <div class="alert alert-danger">Invalid ID provided.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'terminate_failed'): ?>
        <div class="alert alert-danger">Failed to terminate the timetable.</div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
        <div class="alert alert-danger">Unauthorized action.</div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <?php if ($user_role === 'admin'): ?>
                <div class="mb-3 d-flex justify-content-end gap-2">
                    <a href="generate_overall_timetable.php" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Generate New Timetable
                    </a>
                    <a href="terminate_timetable.php" onclick="return confirm('Are you sure you want to terminate the current timetable?');" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Terminate Current Timetable
                    </a>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Day</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Teacher</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Room No</th>
                            <?php if ($user_role === 'admin') echo '<th>Actions</th>'; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            $count = 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $count++ . "</td>
                                        <td>" . htmlspecialchars($row['day_of_week']) . "</td>
                                        <td>" . htmlspecialchars($row['start_time']) . "</td>
                                        <td>" . htmlspecialchars($row['end_time']) . "</td>
                                        <td>" . htmlspecialchars($row['teacher_name']) . "</td>
                                        <td>" . htmlspecialchars($row['class_name']) . "</td>
                                        <td>" . htmlspecialchars($row['subject_name']) . "</td>
                                        <td>" . htmlspecialchars($row['room_number']) . "</td>";
                                if ($user_role === 'admin') {
                                    echo "<td>
                                            <a href='edit_timetable.php?id=" . $row['timetable_id'] . "' class='btn btn-sm btn-primary'><i class='fas fa-edit'></i></a>
                                            <a href='delete_timetable.php?id=" . $row['timetable_id'] . "' onclick=\"return confirm('Are you sure?')\" class='btn btn-sm btn-danger'><i class='fas fa-trash-alt'></i></a>
                                          </td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='" . ($user_role === 'admin' ? 9 : 8) . "' class='text-center'>No timetable records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php include '../include/footer.php'; ?>
