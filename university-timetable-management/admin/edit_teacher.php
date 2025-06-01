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
    header("Location: manage_teachers.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
if ($stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement: " . htmlspecialchars($conn->error) . "</div>";
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    echo "<div class='alert alert-danger'>Teacher not found.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $teacher['password'];
    $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : NULL;
    $specialization = $conn->real_escape_string($_POST['specialization']);

    $stmt = $conn->prepare("UPDATE teachers SET first_name = ?, last_name = ?, email = ?, password = ?, department_id = ?, specialization = ?, updated_at = NOW() WHERE teacher_id = ?");
    if ($stmt === false) {
        echo "<div class='alert alert-danger'>Failed to prepare the UPDATE statement: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        $stmt->bind_param("sssissi", $first_name, $last_name, $email, $password, $department_id, $specialization, $id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Teacher updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to update teacher: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    }
}
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-chalkboard-teacher"></i> Edit Teacher</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" value="<?= htmlspecialchars($teacher['first_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" value="<?= htmlspecialchars($teacher['last_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($teacher['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="New Password">
                    </div>
                    <div class="col-md-6">
                        <label for="specialization" class="form-label">Specialization</label>
                        <input type="text" name="specialization" id="specialization" class="form-control" value="<?= htmlspecialchars($teacher['specialization']) ?>">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Update Teacher</button>
                        <a href="manage_teachers.php" class="btn btn-secondary">Cancel</a>
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