<?php
include '../config/database.php';

$result = $conn->query("SELECT id, class_name FROM classes");
?>

<table>
    <tr>
        <th>Class Name</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['class_name']; ?></td>
        <td>
            <a href="edit_class.php?id=<?= $row['id']; ?>">Edit</a>
            <a href="delete_class.php?id=<?= $row['id']; ?>">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>
