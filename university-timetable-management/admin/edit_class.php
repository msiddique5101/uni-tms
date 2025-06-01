<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_classes.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
if ($stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$class) {
    echo "<div class='alert alert-danger'>Class not found.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $stmt = $conn->prepare("UPDATE classes SET name = ?, updated_at = NOW() WHERE id = ?");
    if ($stmt === false) {
        echo "<div class='alert alert-danger'>Failed to prepare the UPDATE statement: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Class updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to update class: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    }
}
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-chalkboard"></i> Edit Class</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Class Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($class['name']) ?>" required>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Update Class</button>
                        <a href="manage_classes.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php
$conn->close();
include '../include/footer.php';
?>