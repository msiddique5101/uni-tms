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
header('Content-Disposition: attachment;filename="students_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Class', 'Created At']);

$sql = "SELECT s.student_id, s.first_name, s.last_name, s.email, c.name AS class_name, s.created_at 
        FROM students s 
        JOIN classes c ON s.class_id = c.id";
$result = $conn->query($sql);
if ($result === false) {
    echo "Query failed: " . htmlspecialchars($conn->error);
    exit;
}

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_id'],
        $row['first_name'],
        $row['last_name'],
        $row['email'],
        $row['class_name'],
        $row['created_at']
    ]);
}

fclose($output);
$conn->close();
?>