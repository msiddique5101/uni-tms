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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch departments for dropdown
$departments_result = $conn->query("SELECT department_id, department_name FROM departments");

// Add Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : NULL;
    $specialization = $conn->real_escape_string($_POST['specialization']);

    // Check if email already exists
    $email_check = $conn->prepare("SELECT COUNT(*) FROM teachers WHERE email = ?");
    if ($email_check) {
        $email_check->bind_param("s", $email);
        $email_check->execute();
        $email_count = $email_check->get_result()->fetch_row()[0];
        $email_check->close();

        if ($email_count > 0) {
            echo "<div class='alert alert-danger'>Email already exists. Please use a different email.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO teachers (first_name, last_name, email, password, department_id, specialization, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            if ($stmt) {
                $stmt->bind_param("sssiss", $first_name, $last_name, $email, $password, $department_id, $specialization);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>Teacher added successfully.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Failed to add teacher: " . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            } else {
                echo "<div class='alert alert-danger'>Insert statement failed: " . htmlspecialchars($conn->error) . "</div>";
            }
        }
    }
}

// Delete Teacher
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM teachers WHERE teacher_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Teacher deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to delete teacher: " . htmlspecialchars($stmt->error) . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Delete statement failed: " . htmlspecialchars($conn->error) . "</div>";
    }
}

// Fetch teachers
$sql = "SELECT teacher_id, first_name, last_name, email, specialization, department_id, created_at, updated_at FROM teachers WHERE CONCAT(first_name, ' ', last_name) LIKE ? LIMIT ? OFFSET ?";
$search_param = "%$search%";
$stmt = $conn->prepare($sql);
$teachers = null;
if ($stmt) {
    $stmt->bind_param("sii", $search_param, $limit, $offset);
    $stmt->execute();
    $teachers = $stmt->get_result();
    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>Failed to fetch teachers: " . htmlspecialchars($conn->error) . "</div>";
}

// Total pages
$total_sql = "SELECT COUNT(*) as total FROM teachers WHERE CONCAT(first_name, ' ', last_name) LIKE ?";
$stmt = $conn->prepare($total_sql);
$total_pages = 1;
if ($stmt) {
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $total_result = $stmt->get_result()->fetch_assoc();
    $total_pages = ceil($total_result['total'] / $limit);
    $stmt->close();
}
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Add Teacher Form -->
                <div class="mb-4">
                    <form method="POST" class="row g-3">
                        <div class="col-auto">
                            <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                        </div>
                        <div class="col-auto">
                            <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                        </div>
                        <div class="col-auto">
                            <input type="email" name="email" class="form-control" placeholder="Email" required>
                        </div>
                        <div class="col-auto">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-auto">
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php while ($row = $departments_result->fetch_assoc()): ?>
                                    <option value="<?= $row['department_id'] ?>"><?= htmlspecialchars($row['department_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="text" name="specialization" class="form-control" placeholder="Specialization">
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_teacher" class="btn btn-primary">Add Teacher</button>
                        </div>
                    </form>
                </div>

                <!-- Search -->
                <div class="mb-3">
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <input type="text" name="search" class="form-control" placeholder="Search Teachers" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </div>
                    </form>
                </div>

                <!-- Teachers Table -->
                <div class="table-responsive">
                    <table class="table table-striped timetable-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Specialization</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($teachers && $teachers->num_rows > 0): ?>
                                <?php while ($row = $teachers->fetch_assoc()): ?>
                                    <?php
                                    $dept_name = "N/A";
                                    $dept_id = $row['department_id'];
                                    $dept_q = $conn->query("SELECT department_name FROM departments WHERE department_id = $dept_id");
                                    if ($dept_q && $dept_q->num_rows > 0) {
                                        $dept_name = $dept_q->fetch_assoc()['department_name'];
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['teacher_id']) ?></td>
                                        <td><?= htmlspecialchars($row['first_name']) ?></td>
                                        <td><?= htmlspecialchars($row['last_name']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($dept_name) ?></td>
                                        <td><?= htmlspecialchars($row['specialization']) ?: 'N/A' ?></td>
                                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                                        <td><?= htmlspecialchars($row['updated_at']) ?></td>
                                        <td>
                                            <a href="edit_teacher.php?id=<?= $row['teacher_id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="?delete=<?= $row['teacher_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center">No teachers found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>

                <!-- Export -->
                <div class="mt-3">
                    <a href="export_teachers.php" class="btn btn-success"><i class="fas fa-download"></i> Export to CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php $conn->close(); include '../include/footer.php'; ?>
