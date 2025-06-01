<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];

    $stmt = $conn->prepare("INSERT INTO classes (class_name) VALUES (?)");
    $stmt->bind_param("s", $class_name);

    if ($stmt->execute()) {
        echo "Class added successfully!";
    } else {
        echo "Error adding class.";
    }
}
?>
