<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../config/database.php';

if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="timetables_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Course', 'Subject', 'Teacher', 'Day', 'Start Time', 'End Time', 'Room', 'Class']);

$sql = "SELECT t.timetable_id, c.course_name, s.name AS subject_name, CONCAT(tch.first_name, ' ', tch.last_name) AS teacher_name, 
        t.day_of_week, t.start_time, t.end_time, r.room_number, cl.name AS class_name 
        FROM timetables t 
        JOIN courses c ON t.course_id = c.course_id 
        JOIN subjects s ON t.subject_id = s.subject_id 
        JOIN teachers tch ON t.teacher_id = tch.teacher_id 
        JOIN classes cl ON t.class_id = cl.id 
        JOIN classrooms r ON t.room_id = r.room_id";
$result = $conn->query($sql);
if ($result === false) {
    echo "Query failed: " . htmlspecialchars($conn->error);
    exit;
}

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['timetable_id'],
        $row['course_name'],
        $row['subject_name'],
        $row['teacher_name'],
        $row['day_of_week'],
        $row['start_time'],
        $row['end_time'],
        $row['room_number'],
        $row['class_name']
    ]);
}

fclose($output);
$conn->close();
?>