<?php
include '../config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Student deleted successfully!'); window.location.href = 'view_students.php';</script>";
    } else {
        echo "<script>alert('Error deleting student!'); window.location.href = 'view_students.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('No student ID provided!'); window.location.href = 'view_students.php';</script>";
}
?>
