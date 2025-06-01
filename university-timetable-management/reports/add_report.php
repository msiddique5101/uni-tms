<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $subject = $_POST['subject'];
    $grade = $_POST['grade'];

    $stmt = $conn->prepare("INSERT INTO reports (student_id, subject, grade) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $subject, $grade);

    if ($stmt->execute()) {
        echo "Report added successfully!";
    } else {
        echo "Error adding report.";
    }
}
?>
