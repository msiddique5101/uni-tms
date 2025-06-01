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
header('Content-Disposition: attachment;filename="teachers_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Specialization', 'Created At', 'Updated At']);

$result = $conn->query("SELECT teacher_id, first_name, last_name, email, specialization, created_at, updated_at FROM teachers");
if ($result === false) {
    echo "Query failed: " . htmlspecialchars($conn->error);
    exit;
}

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['teacher_id'],
        $row['first_name'],
        $row['last_name'],
        $row['email'],
        $row['specialization'] ?: 'N/A',
        $row['created_at'],
        $row['updated_at']
    ]);
}

fclose($output);
$conn->close();
?>