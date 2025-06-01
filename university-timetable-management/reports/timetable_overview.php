<?php
include '../config/database.php';

header("Content-Type: application/json");

$class_id = $_GET['class_id'] ?? null;
$teacher_id = $_GET['teacher_id'] ?? null;
$day = $_GET['day'] ?? null;

$query = "SELECT * FROM timetable WHERE 1=1";

if ($class_id) $query .= " AND class_id='$class_id'";
if ($teacher_id) $query .= " AND teacher_id='$teacher_id'";
if ($day) $query .= " AND day='$day'";

$result = mysqli_query($conn, $query);
$timetable = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($timetable);

mysqli_close($conn);
?>
