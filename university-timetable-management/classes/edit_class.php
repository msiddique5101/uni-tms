<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $class_name = $_POST['class_name'];

    $stmt = $conn->prepare("UPDATE classes SET class_name=? WHERE id=?");
    $stmt->bind_param("si", $class_name, $id);

    if ($stmt->execute()) {
        echo "Class updated successfully!";
    } else {
        echo "Error updating class.";
    }
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM classes WHERE id=$id");
$class = $result->fetch_assoc();
?>
<form method="post">
    <input type="hidden" name="id" value="<?= $class['id']; ?>">
    <input type="text" name="class_name" value="<?= $class['class_name']; ?>" required>
    <button type="submit">Update</button>
</form>
