<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';

if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_students.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
if ($stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "<div class='alert alert-danger'>Student not found.</div>";
    exit;
}

$class_stmt = $conn->prepare("SELECT id, name FROM classes");
if ($class_stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for classes: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$class_stmt->execute();
$classes = $class_stmt->get_result();
$class_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $class_id = (int)$_POST['class_id'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $student['password'];

    $stmt = $conn->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, class_id = ?, password = ? WHERE student_id = ?");
    if ($stmt === false) {
        echo "<div class='alert alert-danger'>Failed to prepare the UPDATE statement: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        $stmt->bind_param("sssisi", $first_name, $last_name, $email, $class_id, $password, $id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Student updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to update student: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    }
}
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-users"></i> Edit Student</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="class_id" class="form-label">Class</label>
                        <select name="class_id" id="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php while ($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['id'] ?>" <?= $class['id'] == $student['class_id'] ? 'selected' : '' ?>><?= htmlspecialchars($class['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="New Password">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Update Student</button>
                        <a href="manage_students.php" class="btn btn-secondary">Cancel</a>
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