<?php
include '../config/database.php';

header('Content-Type: application/json');
$result = $conn->query("SELECT * FROM students");

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode($students);
?>
