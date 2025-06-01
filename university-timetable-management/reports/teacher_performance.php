<?php
include '../config/database.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Submit teacher performance rating
    $teacher_id = $_POST['teacher_id'] ?? '';
    $rating = $_POST['rating'] ?? 0;
    $feedback = $_POST['feedback'] ?? '';

    if (!$teacher_id || $rating < 1 || $rating > 5) {
        echo json_encode(["error" => "Valid teacher ID and rating (1-5) required"]);
        exit;
    }

    $query = "INSERT INTO teacher_performance (teacher_id, rating, feedback) VALUES ('$teacher_id', '$rating', '$feedback')";
    $result = mysqli_query($conn, $query);

    echo json_encode(["message" => $result ? "Rating submitted" : "Failed"]);
}

elseif ($method === 'GET') {
    // Get performance ratings
    $teacher_id = $_GET['teacher_id'] ?? null;
    $query = "SELECT * FROM teacher_performance" . ($teacher_id ? " WHERE teacher_id='$teacher_id'" : "");

    $result = mysqli_query($conn, $query);
    $performance = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode($performance);
}

mysqli_close($conn);
?>
