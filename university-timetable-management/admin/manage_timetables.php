<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';

if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $course_id = (int)$_POST['course_id'];
    $subject_id = (int)$_POST['subject_id'];
    $teacher_id = (int)$_POST['teacher_id'];
    $day_of_week = $conn->real_escape_string($_POST['day_of_week']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    $room_number = $conn->real_escape_string($_POST['room_id']);
    $class_id = (int)$_POST['class_id'];

    // Validate time range
    if (strtotime($end_time) <= strtotime($start_time)) {
        echo "<div class='alert alert-danger'>End time must be after start time.</div>";
    } else {
        // Fetch room_id from classrooms using room_number
        $stmt = $conn->prepare("SELECT room_id FROM classrooms WHERE room_number = ?");
        if ($stmt === false) {
            echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for room: " . htmlspecialchars($conn->error) . "</div>";
            exit;
        }
        $stmt->bind_param("s", $room_number);
        $stmt->execute();
        $stmt->bind_result($room_id);
        $stmt->fetch();
        $stmt->close();

        if (!$room_id) {
            echo "<div class='alert alert-danger'>Invalid room number.</div>";
            exit;
        }

        // Check for conflicts (same room, day, and overlapping time)
        $conflict_sql = "SELECT COUNT(*) FROM timetables 
                        WHERE room_id = ? AND day_of_week = ? 
                        AND ((start_time <= ? AND end_time >= ?) OR (start_time <= ? AND end_time >= ?))";
        $conflict_stmt = $conn->prepare($conflict_sql);
        if ($conflict_stmt === false) {
            echo "<div class='alert alert-danger'>Failed to prepare conflict check statement: " . htmlspecialchars($conn->error) . "</div>";
            exit;
        }
        $conflict_stmt->bind_param("ssssss", $room_id, $day_of_week, $start_time, $start_time, $end_time, $end_time);
        $conflict_stmt->execute();
        $conflict_count = $conflict_stmt->get_result()->fetch_row()[0];
        $conflict_stmt->close();

        if ($conflict_count > 0) {
            echo "<div class='alert alert-danger'>Room or Teacher is already booked for the selected time and day.</div>";
        } else {
            // Insert the timetable entry
            $stmt = $conn->prepare("INSERT INTO timetables (course_id, subject_id, teacher_id, day_of_week, start_time, end_time, room_id, class_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                echo "<div class='alert alert-danger'>Failed to prepare the INSERT statement: " . htmlspecialchars($conn->error) . "</div>";
                exit;
            }
            $stmt->bind_param("iiissssi", $course_id, $subject_id, $teacher_id, $day_of_week, $start_time, $end_time, $room_id, $class_id);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Timetable entry added successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Failed to add timetable entry: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM timetables WHERE timetable_id = ?");
    if ($stmt === false) {
        echo "<div class='alert alert-danger'>Failed to prepare the DELETE statement: " . htmlspecialchars($conn->error) . "</div>";
        exit;
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Timetable entry deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to delete timetable entry: " . htmlspecialchars($stmt->error) . "</div>";
    }
    $stmt->close();
    header("Location: manage_timetables.php");
    exit;
}

// Search filter
$search_param = "%{$search}%";

// Timetable list
$list_sql = "
    SELECT t.*, 
           c.course_name, 
           s.subject_name AS subject_name, 
           CONCAT(tch.first_name, ' ', tch.last_name) AS teacher_name, 
           cl.name AS class_name, 
           r.room_number 
    FROM timetables t 
    JOIN courses c ON t.course_id = c.course_id 
    JOIN subjects s ON t.subject_id = s.subject_id 
    JOIN teachers tch ON t.teacher_id = tch.teacher_id 
    JOIN classes cl ON t.class_id = cl.id 
    JOIN classrooms r ON t.room_id = r.room_id 
    WHERE cl.name LIKE ?
    ORDER BY t.day_of_week, t.start_time 
    LIMIT $limit OFFSET $offset
";

$list_stmt = $conn->prepare($list_sql);
if (!$list_stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$list_stmt->bind_param("s", $search_param);
$list_stmt->execute();
$timetables = $list_stmt->get_result();
$list_stmt->close();

// Total pages
$count_sql = "
    SELECT COUNT(*) AS total 
    FROM timetables t 
    JOIN classes cl ON t.class_id = cl.id 
    WHERE cl.name LIKE ?
";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $search_param);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();
$total_pages = (int)ceil($total_rows / $limit);

// Dropdowns
$course_stmt = $conn->prepare("SELECT course_id, course_name FROM courses");
$course_stmt->execute();
$courses = $course_stmt->get_result();
$course_stmt->close();

$subject_stmt = $conn->prepare("SELECT subject_id, subject_name FROM subjects");
$subject_stmt->execute();
$subjects = $subject_stmt->get_result();
$subject_stmt->close();

$teacher_stmt = $conn->prepare("SELECT teacher_id, CONCAT(first_name, ' ', last_name) AS teacher_name FROM teachers");
$teacher_stmt->execute();
$teachers = $teacher_stmt->get_result();
$teacher_stmt->close();

$class_stmt = $conn->prepare("SELECT id, name FROM classes");
$class_stmt->execute();
$classes = $class_stmt->get_result();
$class_stmt->close();

$room_stmt = $conn->prepare("SELECT room_id, room_number FROM classrooms");
$room_stmt->execute();
$rooms = $room_stmt->get_result();
$room_stmt->close();
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-calendar-alt"></i> Manage Timetables</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Add Timetable -->
                <div class="mb-4">
                    <form method="POST" class="row g-3">
                        <div class="col-auto">
                            <select name="course_id" class="form-control" required>
                                <option value="">Select Course</option>
                                <?php while ($course = $courses->fetch_assoc()): ?>
                                    <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="subject_id" class="form-control" required>
                                <option value="">Select Subject</option>
                                <?php while ($subject = $subjects->fetch_assoc()): ?>
                                    <option value="<?= $subject['subject_id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="teacher_id" class="form-control" required>
                                <option value="">Select Teacher</option>
                                <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                    <option value="<?= $teacher['teacher_id'] ?>"><?= htmlspecialchars($teacher['teacher_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="day_of_week" class="form-control" required>
                                <option value="">Select Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-auto">
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                        <div class="col-auto">
                            <select name="room_id" class="form-control" required>
                                <option value="">Select Room</option>
                                <?php while ($room = $rooms->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($room['room_number']) ?>"><?= htmlspecialchars($room['room_number']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="class_id" class="form-control" required>
                                <option value="">Select Class</option>
                                <?php while ($class = $classes->fetch_assoc()): ?>
                                    <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_timetable" class="btn btn-primary">Add Timetable</button>
                        </div>
                    </form>
                </div>

                <!-- Search -->
                <div class="mb-3">
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <input type="text" name="search" class="form-control" placeholder="Search by Class Name" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </div>
                    </form>
                </div>

                <!-- Timetable Table -->
                <div class="table-responsive">
                    <table class="table table-striped timetable-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Course</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Room</th>
                                <th>Class</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($timetables->num_rows > 0): ?>
                                <?php while ($row = $timetables->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['timetable_id']) ?></td>
                                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                                        <td><?= htmlspecialchars($row['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                                        <td><?= htmlspecialchars($row['day_of_week']) ?></td>
                                        <td><?= htmlspecialchars($row['start_time']) ?></td>
                                        <td><?= htmlspecialchars($row['end_time']) ?></td>
                                        <td><?= htmlspecialchars($row['room_number']) ?></td>
                                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                                        <td>
                                            <a href="edit_timetable.php?id=<?= $row['timetable_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="manage_timetables.php?delete=<?= $row['timetable_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this timetable?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="10" class="text-center">No timetables found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <!-- Export -->
                <div class="mt-3">
                    <a href="export_timetables.php" class="btn btn-success"><i class="fas fa-download"></i> Export to CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php include '../include/footer.php'; ?>