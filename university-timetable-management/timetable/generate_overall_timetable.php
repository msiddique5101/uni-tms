<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
require_once '../config/database.php';
require_once '../include/header.php';
require_once '../include/sidebar.php';
require_once '../include/navbar.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$message = '';
$success = false;

// Check if timetable already exists
$result = $conn->query("SELECT COUNT(*) as count FROM timetables");
$row = $result->fetch_assoc();
$timetableExists = $row['count'] > 0;

// Handle timetable generation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate']) && !$timetableExists) {
    try {
        $stmt = $conn->prepare("INSERT INTO timetables (course_id, subject_id, teacher_id, day_of_week, start_time, end_time, room_id, class_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($_POST['timetable'] as $entry) {
            $stmt->bind_param(
                "iiissssi",
                $entry['course_id'],
                $entry['subject_id'],
                $entry['teacher_id'],
                $entry['day'],
                $entry['start_time'],
                $entry['end_time'],
                $entry['room'],
                $entry['class_id']
            );
            $stmt->execute();
        }

        $success = true;
        $message = "Timetable generated successfully!";
        $timetableExists = true;
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle timetable termination
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['terminate'])) {
    $conn->query("DELETE FROM timetables");
    $message = "Previous timetable terminated.";
    $timetableExists = false;
}
?>

<div class="container mt-4">
    <h2 class="mb-4">Generate Overall Timetable</h2>

    <?php if ($message): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($timetableExists): ?>
        <div class="alert alert-warning">
            A timetable already exists. Please terminate the current one before generating a new timetable.
        </div>
        <form method="POST">
            <button type="submit" name="terminate" class="btn btn-danger">Terminate Existing Timetable</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <div id="timetable-entries">
                <!-- Dynamic timetable fields inserted here -->
            </div>
            <button type="button" class="btn btn-secondary mt-3" onclick="addTimetableRow()">Add Row</button>
            <br><br>
            <button type="submit" name="generate" class="btn btn-primary">Generate Timetable</button>
        </form>

        <script>
            let index = 0;

            function addTimetableRow() {
                const row = `
                    <div class="card mb-3 p-3 border">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Course ID</label>
                                <input type="number" name="timetable[${index}][course_id]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Subject ID</label>
                                <input type="number" name="timetable[${index}][subject_id]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Teacher ID</label>
                                <input type="number" name="timetable[${index}][teacher_id]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Class ID</label>
                                <input type="number" name="timetable[${index}][class_id]" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label>Day</label>
                                <select name="timetable[${index}][day]" class="form-control" required>
                                    <option>Monday</option>
                                    <option>Tuesday</option>
                                    <option>Wednesday</option>
                                    <option>Thursday</option>
                                    <option>Friday</option>
                                    <option>Saturday</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Start Time</label>
                                <input type="time" name="timetable[${index}][start_time]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>End Time</label>
                                <input type="time" name="timetable[${index}][end_time]" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>Room ID</label>
                                <input type="text" name="timetable[${index}][room]" class="form-control" required>
                            </div>
                        </div>
                    </div>`;
                document.getElementById('timetable-entries').insertAdjacentHTML('beforeend', row);
                index++;
            }
        </script>
    <?php endif; ?>
</div>
<script src="../assets/js/script.js"></script>
<?php require_once '../include/footer.php'; ?>
