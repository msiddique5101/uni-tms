<?php
include '../config/database.php';
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: timetable.php?error=unauthorized");
    exit;
}

$sql = "DELETE FROM timetables"; // or TRUNCATE TABLE timetables;
if ($conn->query($sql) === TRUE) {
    header("Location: timetable.php?deleted_all=true");
} else {
    header("Location: timetable.php?error=terminate_failed");
}
$conn->close();
?>
