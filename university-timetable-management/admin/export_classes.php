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
header('Content-Disposition: attachment;filename="classes_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Created At', 'Updated At']);

$result = $conn->query("SELECT id, name, created_at, updated_at FROM classes");
if ($result === false) {
    echo "Query failed: " . htmlspecialchars($conn->error);
    exit;
}

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['created_at'],
        $row['updated_at']
    ]);
}

fclose($output);
$conn->close();
?>