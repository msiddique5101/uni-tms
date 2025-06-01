<?php
include '../config/database.php';

header('Content-Type: application/json');
$result = $conn->query("SELECT * FROM timetable");

$timetable = [];
while ($row = $result->fetch_assoc()) {
    $timetable[] = $row;
}

echo json_encode($timetable);
?>
