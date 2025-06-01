<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_timetables.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM timetables WHERE timetable_id = ?");
if ($stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$timetable = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$timetable) {
    echo "<div class='alert alert-danger'>Timetable entry not found.</div>";
    exit;
}

$course_stmt = $conn->prepare("SELECT course_id, course_name FROM courses");
if ($course_stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for courses: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$course_stmt->execute();
$courses = $course_stmt->get_result();
$course_stmt->close();

$subject_stmt = $conn->prepare("SELECT subject_id, name FROM subjects");
if ($subject_stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for subjects: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$subject_stmt->execute();
$subjects = $subject_stmt->get_result();
$subject_stmt->close();

$teacher_stmt = $conn->prepare("SELECT teacher_id, CONCAT(first_name, ' ', last_name) AS teacher_name FROM teachers");
if ($teacher_stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for teachers: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$teacher_stmt->execute();
$teachers = $teacher_stmt->get_result();
$teacher_stmt->close();

$class_stmt = $conn->prepare("SELECT id, name FROM classes");
if ($class_stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for classes: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$class_stmt->execute();
$classes = $class_stmt->get_result();
$class_stmt->close();

$room_stmt = $conn->prepare("SELECT room_id, room_number FROM classrooms");
if ($room_stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for rooms: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$room_stmt->execute();
$rooms = $room_stmt->get_result();
$room_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)$_POST['course_id'];
    $subject_id = (int)$_POST['subject_id'];
    $teacher_id = (int)$_POST['teacher_id'];
    $day_of_week = $conn->real_escape_string($_POST['day_of_week']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $room_id = (int)$_POST['room_id'];
    $class_id = (int)$_POST['class_id'];

    $stmt = $conn->prepare("UPDATE timetables SET course_id = ?, subject_id = ?, teacher_id = ?, day_of_week = ?, start_time = ?, end_time = ?, room_id = ?, class_id = ? WHERE timetable_id = ?");
    if ($stmt === false) {
        echo "<div class='alert alert-danger'>Failed to prepare the UPDATE statement: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        $stmt->bind_param("iiiissiii", $course_id, $subject_id, $teacher_id, $day_of_week, $start_time, $end_time, $room_id, $class_id, $id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Timetable entry updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to update timetable entry: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    }
}
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-calendar-alt"></i> Edit Timetable</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="course_id" class="form-label">Course</label>
                        <select name="course_id" id="course_id" class="form-control" required>
                            <option value="">Select Course</option>
                            <?php while ($course = $courses->fetch_assoc()): ?>
                                <option value="<?= $course['course_id'] ?>" <?= $course['course_id'] == $timetable['course_id'] ? 'selected' : '' ?>><?= htmlspecialchars($course['course_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-control" required>
                            <option value="">Select Subject</option>
                            <?php while ($subject = $subjects->fetch_assoc()): ?>
                                <option value="<?= $subject['subject_id'] ?>" <?= $subject['subject_id'] == $timetable['subject_id'] ? 'selected' : '' ?>><?= htmlspecialchars($subject['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select name="teacher_id" id="teacher_id" class="form-control" required>
                            <option value="">Select Teacher</option>
                            <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                <option value="<?= $teacher['teacher_id'] ?>" <?= $teacher['teacher_id'] == $timetable['teacher_id'] ? 'selected' : '' ?>><?= htmlspecialchars($teacher['teacher_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="day_of_week" class="form-label">Day of Week</label>
                        <select name="day_of_week" id="day_of_week" class="form-control" required>
                            <option value="">Select Day</option>
                            <option value="Monday" <?= $timetable['day_of_week'] == 'Monday' ? 'selected' : '' ?>>Monday</option>
                            <option value="Tuesday" <?= $timetable['day_of_week'] == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                            <option value="Wednesday" <?= $timetable['day_of_week'] == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                            <option value="Thursday" <?= $timetable['day_of_week'] == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                            <option value="Friday" <?= $timetable['day_of_week'] == 'Friday' ? 'selected' : '' ?>>Friday</option>
                            <option value="Saturday" <?= $timetable['day_of_week'] == 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                            <option value="Sunday" <?= $timetable['day_of_week'] == 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" value="<?= htmlspecialchars($timetable['start_time']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" value="<?= htmlspecialchars($timetable['end_time']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="room_id" class="form-label">Room</label>
                        <select name="room_id" id="room_id" class="form-control" required>
                            <option value="">Select Room</option>
                            <?php while ($room = $rooms->fetch_assoc()): ?>
                                <option value="<?= $room['room_id'] ?>" <?= $room['room_id'] == $timetable['room_id'] ? 'selected' : '' ?>><?= htmlspecialchars($room['room_number']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class_id" class="form-label">Class</label>
                        <select name="class_id" id="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['id'] ?>" <?= $class['id'] == $timetable['class_id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Update Timetable</button>
                        <a href="manage_timetables.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php
$conn->close();
include '../include/footer.php';
?>