<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "uni_timetable_management_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = $error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class_id = $_POST['class_id']; // <-- NEW
    $course_id = $_POST['course_id'];
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $room_number = $_POST['room_number'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Conflict check
    $stmt = $conn->prepare("SELECT * FROM timetables 
        WHERE (teacher_id = ? OR room_id = ?) 
        AND day_of_week = ? 
        AND (start_time < ? AND end_time > ?)");

    // Get the room_id based on selected room_number
    $room_stmt = $conn->prepare("SELECT room_id FROM classrooms WHERE room_number = ?");
    $room_stmt->bind_param("s", $room_number);
    $room_stmt->execute();
    $room_result = $room_stmt->get_result();

    if ($room_result->num_rows > 0) {
        $room_data = $room_result->fetch_assoc();
        $room_id = $room_data['room_id'];
        $room_stmt->close();

        $stmt = $conn->prepare("SELECT * FROM timetables 
    WHERE (teacher_id = ? OR room_id = ?) 
    AND day_of_week = ? 
    AND (start_time < ? AND end_time > ?)");

    $stmt->bind_param("iisss", $teacher_id, $room_id, $day_of_week, $end_time, $start_time);
    $stmt->execute();
    $conflict = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($conflict) {
        $error = "Conflict: Teacher or Room already scheduled at this time.";
    } else {
        $stmt = $conn->prepare("INSERT INTO timetables 
            (class_id, course_id, teacher_id, subject_id, day_of_week, start_time, end_time, room_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiissss", $class_id, $course_id, $teacher_id, $subject_id, $day, $start_time, $end_time, $room_id);

        if ($stmt->execute()) {
            $success = "Timetable created successfully.";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
    } else {
    $error = "Invalid room selection.";
    $room_stmt->close();
}
}

function getOptions($conn, $table, $idField, $nameField, $condition = "") {
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
        $options .= "<option value='$id'>$name</option>";
    }
    return $options;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Timetable</title>
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

<h2>Create Timetable</h2>
<form method="POST" action="">

    <label for="class_id">Class:</label>
    <select name="class_id" id="class_id" required>
        <option value="">-- Select Class --</option>
        <?= getOptions($conn, 'classes', 'id', 'name') ?>
    </select>

    <label for="course_id">Course:</label>
    <select name="course_id" id="course_id" required>
        <option value="">-- Select Course --</option>
        <?= getOptions($conn, 'courses', 'course_id', 'course_name') ?>
    </select>

    <label for="teacher_id">Teacher:</label>
    <select name="teacher_id" id="teacher_id" required>
        <option value="">-- Select Teacher --</option>
        <?php
            $sql = "SELECT teacher_id, CONCAT(first_name, ' ', last_name) AS teacher_name FROM teachers ORDER BY first_name";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $id = htmlspecialchars($row['teacher_id']);
                $name = htmlspecialchars($row['teacher_name']);
                echo "<option value='$id'>$name</option>";
            }
        ?>
    </select>

    <label for="subject_id">Subject:</label>
    <select name="subject_id" id="subject_id" required>
        <option value="">-- Select Subject --</option>
        <?= getOptions($conn, 'subjects', 'subject_id', 'subject_name') ?>
    </select>

    <label for="room_number">Room:</label>
    <select name="room_number" id="room_number" required>
        <option value="">-- Select Room (Only Available) --</option>
        <?= getOptions($conn, 'classrooms', 'room_number', 'room_id', "status = 'Available'") ?>
    </select>

    <label for="day_of_week">Day of the Week:</label>
    <select name="day_of_week" id="day_of_week" required>
        <option value="">-- Select Day --</option>
        <?php
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        foreach ($days as $d) {
            echo "<option value='$d'>$d</option>";
        }
        ?>
    </select>

    <label for="start_time">Start Time:</label>
    <input type="time" name="start_time" id="start_time" required>

    <label for="end_time">End Time:</label>
    <input type="time" name="end_time" id="end_time" required>

    <input type="submit" value="Create Timetable">
</form>

<?php if ($success): ?>
    <div class="msg success"><?= $success ?></div>
<?php elseif ($error): ?>
    <div class="msg error"><?= $error ?></div>
<?php endif; ?>
<script src="../assets/js/script.js"></script>
</body>
</html>
