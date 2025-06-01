<?php
include '../config/database.php';

header('Content-Type: application/json');
$result = $conn->query("SELECT * FROM teachers");

$teachers = [];
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}

echo json_encode($teachers);
?>
