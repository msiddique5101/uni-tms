<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $timetable_id = $_GET['id'] ?? null;

    if ($timetable_id) {
        $stmt = $conn->prepare("DELETE FROM timetables WHERE timetable_id = ?");
        $stmt->bind_param("i", $timetable_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: view_timetable.php?deleted=1");
            exit();
        } else {
            $stmt->close();
            header("Location: view_timetable.php?error=delete_failed");
            exit();
        }
    } else {
        header("Location: view_timetable.php?error=missing_id");
        exit();
    }
} else {
    header("Location: view_timetable.php?error=invalid_method");
    exit();
}
