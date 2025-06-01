<?php
include '../config/database.php';

$id = $_GET['id'];
$conn->query("DELETE FROM classes WHERE id=$id");
echo "Class deleted successfully.";
?>
