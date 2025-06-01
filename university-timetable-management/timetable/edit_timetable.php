<?php
session_start();
$conn = new mysqli("localhost", "root", "", "uni_timetable_management_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = $error = "";

// Get timetable ID from URL
$timetable_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch existing timetable
$timetable = null;
if ($timetable_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM timetables WHERE timetable_id = ?");
    $stmt->bind_param("i", $timetable_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $timetable = $result->fetch_assoc();
    $stmt->close();

    if (!$timetable) {
        $error = "Invalid timetable ID.";
    }
} else {
    $error = "Timetable ID not provided.";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && $timetable) {
    $class_id = $_POST['class_id'];
    $course_id = $_POST['course_id'];
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $room_id = $_POST['room_id'];
    $day = $_POST['day_of_week'];
    $start_time = date("H:i:s", strtotime($_POST['start_time']));
    $end_time = date("H:i:s", strtotime($_POST['end_time']));

    // Conflict check (excluding current timetable)
    $stmt = $conn->prepare("
        SELECT * FROM timetables 
        WHERE timetable_id != ? 
        AND day_of_week = ? 
        AND (
            (teacher_id = ?)
            OR (room_id = ?)
            OR (class_id = ?)
        )
        AND NOT (end_time <= ? OR start_time >= ?)
    ");
    $stmt->bind_param("isiiiss", $timetable_id, $day, $teacher_id, $room_id, $class_id, $start_time, $end_time);
    $stmt->execute();
    $conflict = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($conflict) {
        $error = "Conflict detected: Room, Teacher, or Class is already booked at this time.";
    } else {
        $stmt = $conn->prepare("UPDATE timetables SET 
            class_id = ?, course_id = ?, teacher_id = ?, subject_id = ?, 
            day_of_week = ?, start_time = ?, end_time = ?, room_id = ?
            WHERE timetable_id = ?");
        $stmt->bind_param("iiiissssi", $class_id, $course_id, $teacher_id, $subject_id, $day, $start_time, $end_time, $room_id, $timetable_id);

        if ($stmt->execute()) {
            $success = "Timetable updated successfully.";
        } else {
            $error = "Error updating timetable: " . $stmt->error;
        }
        $stmt->close();
    }
}

function getOptions($conn, $table, $idField, $nameField, $selectedValue, $condition = "") {
    $options = "";
    $query = "SELECT $idField, $nameField FROM $table";
    if ($condition !== "") {
        $query .= " WHERE $condition";
    }
    $query .= " ORDER BY $nameField";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $id = htmlspecialchars($row[$idField]);
        $name = htmlspecialchars($row[$nameField]);
        $selected = $id == $selectedValue ? "selected" : "";
        $options .= "<option value='$id' $selected>$name</option>";
    }
    return $options;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Timetable</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 40px; }
        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 550px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }
        label { display: block; margin-top: 15px; }
        select, input[type="time"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .msg {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
        }
        .success { color: green; background: #e0ffe0; }
        .error { color: red; background: #ffe0e0; }
    </style>
</head>
<body>

<h2>Edit Timetable</h2>

<?php if ($timetable): ?>
<form method="POST">

    <label for="class_id">Class:</label>
    <select name="class_id" required>
        <?= getOptions($conn, 'classes', 'id', 'name', $timetable['class_id']) ?>
    </select>

    <label for="course_id">Course:</label>
    <select name="course_id" required>
        <?= getOptions($conn, 'courses', 'course_id', 'course_name', $timetable['course_id']) ?>
    </select>

    <label for="teacher_id">Teacher:</label>
    <select name="teacher_id" required>
        <?php
        $sql = "SELECT teacher_id, CONCAT(first_name, ' ', last_name) AS teacher_name FROM teachers ORDER BY first_name";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $id = $row['teacher_id'];
            $name = htmlspecialchars($row['teacher_name']);
            $selected = $id == $timetable['teacher_id'] ? "selected" : "";
            echo "<option value='$id' $selected>$name</option>";
        }
        ?>
    </select>

    <label for="subject_id">Subject:</label>
    <select name="subject_id" required>
        <?= getOptions($conn, 'subjects', 'subject_id', 'subject_name', $timetable['subject_id']) ?>
    </select>

    <label for="room_id">Room:</label>
    <select name="room_id" required>
        <?php
        $room_query = "SELECT room_id, room_number FROM classrooms WHERE status = 'Available' OR room_id = " . intval($timetable['room_id']);
        $room_result = $conn->query($room_query);
        while ($room = $room_result->fetch_assoc()) {
            $id = $room['room_id'];
            $room_number = htmlspecialchars($room['room_number']);
            $selected = ($id == $timetable['room_id']) ? "selected" : "";
            echo "<option value='$id' $selected>$room_number</option>";
        }
        ?>
    </select>

    <label for="day_of_week">Day of the Week:</label>
    <select name="day_of_week" required>
        <?php
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        foreach ($days as $d) {
            $selected = ($d == $timetable['day_of_week']) ? "selected" : "";
            echo "<option value='$d' $selected>$d</option>";
        }
        ?>
    </select>

    <label for="start_time">Start Time:</label>
    <input type="time" name="start_time" value="<?= $timetable['start_time'] ?>" required>

    <label for="end_time">End Time:</label>
    <input type="time" name="end_time" value="<?= $timetable['end_time'] ?>" required>

    <input type="submit" value="Update Timetable">
</form>
<?php endif; ?>

<?php if ($success): ?>
    <div class="msg success"><?= $success ?></div>
<?php elseif ($error): ?>
    <div class="msg error"><?= $error ?></div>
<?php endif; ?>
<script src="../assets/js/script.js"></script>
</body>
</html>
