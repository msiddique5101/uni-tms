<?php
include '../config/database.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Add or update attendance
    $student_id = $_POST['student_id'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Present'; // Present, Absent, Late

    if (!$student_id) {
        echo json_encode(["error" => "Student ID is required"]);
        exit;
    }

    $query = "INSERT INTO attendance (student_id, date, status) VALUES ('$student_id', '$date', '$status')
              ON DUPLICATE KEY UPDATE status='$status'";
    $result = mysqli_query($conn, $query);

    echo json_encode(["message" => $result ? "Attendance recorded" : "Failed"]);
}

elseif ($method === 'GET') {
    // Get attendance records
    $student_id = $_GET['student_id'] ?? null;
    $query = "SELECT * FROM attendance" . ($student_id ? " WHERE student_id='$student_id'" : "");
    
    $result = mysqli_query($conn, $query);
    $attendance = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode($attendance);
}

mysqli_close($conn);
?>
