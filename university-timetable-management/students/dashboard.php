<?php
include '../middleware/student_only.php';
include '../config/database.php'; // Ensure this sets $conn
include '../include/header.php';
include '../include/sidebar.php';
include '../include/navbar.php';
?>

<div class="main-content wrapper flex-grow-1">
    <div class="container mt-4">
        <h3 class="mb-4">Student Dashboard</h3>

        <!-- Today's Classes -->
        <?php
        $student_id = $_SESSION['user_id'] ?? null;
        if (!$student_id) {
            echo "<div class='card-body'><ul><li>Please log in to see your classes.</li></ul></div>";
            return;
        }

        $today_day = date('l'); // e.g., Monday, Tuesday

        // Step 1: Get enrolled course IDs for the student
        $course_ids = [];
        $course_sql = "SELECT course_id FROM student_courses WHERE student_id = ?";
        $stmt_courses = $conn->prepare($course_sql);
        if ($stmt_courses) {
            $stmt_courses->bind_param("i", $student_id);
            $stmt_courses->execute();
            $res_courses = $stmt_courses->get_result();
            while ($row = $res_courses->fetch_assoc()) {
                $course_ids[] = $row['course_id'];
            }
            $stmt_courses->close();
        } else {
            echo "<div class='card-body'><ul><li>Error fetching courses: " . $conn->error . "</li></ul></div>";
            return;
        }

        if (empty($course_ids)) {
            echo "<div class='card-body'><ul><li>You are not enrolled in any courses.</li></ul></div>";
            return;
        }

        // Step 2: Build placeholders for course IDs in SQL
        $placeholders = implode(',', array_fill(0, count($course_ids), '?'));

        // Step 3: Prepare SQL to get today's classes for enrolled courses
        $sql = "SELECT 
                    TIME_FORMAT(t.start_time, '%h:%i %p') AS formatted_start,
                    TIME_FORMAT(t.end_time, '%h:%i %p') AS formatted_end,
                    c.course_name,
                    r.room_number
                FROM timetables t
                JOIN courses c ON t.course_id = c.course_id
                JOIN classrooms r ON t.room_id = r.room_id
                WHERE t.day_of_week = ? 
                  AND t.course_id IN ($placeholders)
                ORDER BY t.start_time ASC";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo "<div class='card-body'><ul><li>Error: " . $conn->error . "</li></ul></div>";
            return;
        }

        // Step 4: Bind parameters dynamically
        $types = 's' . str_repeat('i', count($course_ids));
        $params = array_merge([$today_day], $course_ids);

        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        // Step 5: Execute and fetch results
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<div class="card shadow-sm mb-4">';
        echo '<div class="card-header bg-info text-white">';
        echo "<h5 class='mb-0'>Today's Classes</h5>";
        echo '</div><div class="card-body"><ul>';

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['formatted_start']} - {$row['formatted_end']} | {$row['course_name']} | Room: {$row['room_number']}</li>";
            }
        } else {
            echo "<li>No classes scheduled for today.</li>";
        }

        echo '</ul></div></div>';
        $stmt->close();
        ?>

        <!-- My Timetable -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">My Timetable</h5>
            </div>
            <div class="card-body">
                <a href="../timetable/view_timetable.php" class="btn btn-outline-success btn-sm">View Full Timetable</a>
            </div>
        </div>

        <!-- My Grades -->
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">My Grades</h5>
            </div>
            <div class="card-body">
                <p>Check your latest exam results and GPA.</p>
                <a href="../grades/view.php" class="btn btn-outline-warning btn-sm">View Grades</a>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php include '../include/footer.php'; ?>
