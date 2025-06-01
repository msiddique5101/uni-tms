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

// Handle adding a student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $class_id = (int)$_POST['class_id'];
    $password_plain = $_POST['password'];
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
    $full_name = $first_name . ' ' . $last_name;

    // Check if email already exists in students or users
    $email_check = $conn->prepare("SELECT COUNT(*) FROM students WHERE email = ? UNION ALL SELECT COUNT(*) FROM users WHERE email = ?");
    if ($email_check === false) {
        echo "<div class='alert alert-danger'>Failed to prepare email check statement: " . htmlspecialchars($conn->error) . "</div>";
    } else {
        $email_check->bind_param("ss", $email, $email);
        $email_check->execute();
        $email_result = $email_check->get_result();
        $email_count = 0;
        while ($row = $email_result->fetch_row()) {
            $email_count += $row[0];
        }
        $email_check->close();

        if ($email_count > 0) {
            echo "<div class='alert alert-danger'>Email already exists in the system. Please use a different email.</div>";
        } else {
            $conn->begin_transaction();
            try {
                // Insert into users table first
                $user_stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'Student', NOW())");
                if ($user_stmt === false) {
                    throw new Exception("Failed to prepare the INSERT statement for users: " . htmlspecialchars($conn->error));
                }
                $user_stmt->bind_param("sss", $full_name, $email, $password_hashed);
                if (!$user_stmt->execute()) {
                    throw new Exception("Failed to add to users table: " . htmlspecialchars($user_stmt->error));
                }
                $user_stmt->close();

                // Insert into students table
                $stmt = $conn->prepare("INSERT INTO students (first_name, last_name, email, class_id, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                if ($stmt === false) {
                    throw new Exception("Failed to prepare the INSERT statement for students: " . htmlspecialchars($conn->error));
                }
                $stmt->bind_param("sssds", $first_name, $last_name, $email, $class_id, $password_hashed);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to add student: " . htmlspecialchars($stmt->error));
                }
                $stmt->close();

                $conn->commit();
                echo "<div class='alert alert-success'>Student added successfully.</div>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
            }
        }
    }
}

// Handle updating a student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $student_id = (int)$_POST['student_id'];
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $class_id = (int)$_POST['class_id'];
    $password_plain = $_POST['password'];
    $password_hashed = !empty($password_plain) ? password_hash($password_plain, PASSWORD_DEFAULT) : null;
    $full_name = $first_name . ' ' . $last_name;

    $conn->begin_transaction();
    try {
        // Update students table
        $student_stmt = $conn->prepare("UPDATE students SET first_name = ?, last_name = ?, email = ?, class_id = ?, password = ? WHERE student_id = ?");
        if ($student_stmt === false) {
            throw new Exception("Failed to prepare the UPDATE statement for students: " . htmlspecialchars($conn->error));
        }
        $student_stmt->bind_param("sssdsd", $first_name, $last_name, $email, $class_id, $password_hashed ?: $conn->real_escape_string($_POST['current_password']), $student_id);
        if (!$student_stmt->execute()) {
            throw new Exception("Failed to update student: " . htmlspecialchars($student_stmt->error));
        }
        $student_stmt->close();

        // Update users table
        $user_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE email = ? AND role = 'Student'");
        if ($user_stmt === false) {
            throw new Exception("Failed to prepare the UPDATE statement for users: " . htmlspecialchars($conn->error));
        }
        $current_email = $conn->real_escape_string($_POST['current_email']);
        $user_stmt->bind_param("ssss", $full_name, $email, $password_hashed ?: $conn->real_escape_string($_POST['current_password']), $current_email);
        if (!$user_stmt->execute()) {
            throw new Exception("Failed to update user: " . htmlspecialchars($user_stmt->error));
        }
        $user_stmt->close();

        $conn->commit();
        echo "<div class='alert alert-success'>Student updated successfully.</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}

// Handle deleting a student
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $conn->begin_transaction();
    try {
        // Get the student's email
        $email_stmt = $conn->prepare("SELECT email FROM students WHERE student_id = ?");
        if ($email_stmt === false) {
            throw new Exception("Failed to prepare the SELECT statement for email: " . htmlspecialchars($conn->error));
        }
        $email_stmt->bind_param("i", $id);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result()->fetch_assoc();
        $email_stmt->close();

        if (!$email_result) {
            throw new Exception("Student not found.");
        }

        $email = $email_result['email'];

        // Delete from students table
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
        if ($stmt === false) {
            throw new Exception("Failed to prepare the DELETE statement for students: " . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("i", $id);
        if (!$student_stmt->execute()) {
            throw new Exception("Failed to delete student: " . htmlspecialchars($stmt->error));
        }
        $stmt->close();

        // Delete from users table
        $user_stmt = $conn->prepare("DELETE FROM users WHERE email = ? AND role = 'Student'");
        if ($user_stmt === false) {
            throw new Exception("Failed to prepare the DELETE statement for users: " . htmlspecialchars($conn->error));
        }
        $user_stmt->bind_param("s", $email);
        if (!$user_stmt->execute()) {
            throw new Exception("Failed to delete from users table: " . htmlspecialchars($user_stmt->error));
        }
        $user_stmt->close();

        $conn->commit();
        echo "<div class='alert alert-success'>Student deleted successfully.</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
    }
}

// Fetch students for display
$sql = "SELECT s.*, c.name AS class_name FROM students s JOIN classes c ON s.class_id = c.id WHERE CONCAT(s.first_name, ' ', s.last_name) LIKE ? LIMIT ? OFFSET ?";
$search_param = "%$search%";
$stmt = $conn->prepare($sql);
$students = null;
if ($stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement: " . htmlspecialchars($conn->error) . "</div>";
} else {
    $stmt->bind_param("sii", $search_param, $limit, $offset);
    $stmt->execute();
    $students = $stmt->get_result();
    $stmt->close();
}

$total_sql = "SELECT COUNT(*) as total FROM students WHERE CONCAT(first_name, ' ', last_name) LIKE ?";
$stmt = $conn->prepare($total_sql);
$total_pages = 1;
if ($stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the COUNT statement: " . htmlspecialchars($conn->error) . "</div>";
} else {
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $total_result = $stmt->get_result()->fetch_assoc();
    $total_pages = ceil($total_result['total'] / $limit);
    $stmt->close();
}

$class_stmt = $conn->prepare("SELECT id, name FROM classes");
$classes = null;
if ($class_stmt === false) {
    echo "<div class='alert alert-danger'>Failed to prepare the SELECT statement for classes: " . htmlspecialchars($conn->error) . "</div>";
} else {
    $class_stmt->execute();
    $classes = $class_stmt->get_result();
    $class_stmt->close();
}
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-users"></i> Manage Students</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Add Student Form -->
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
                            <select name="class_id" class="form-control" required>
                                <option value="">Select Class</option>
                                <?php if ($classes && $classes->num_rows > 0): ?>
                                    <?php while ($class = $classes->fetch_assoc()): ?>
                                        <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                        </div>
                    </form>
                </div>

                <!-- Search -->
                <div class="mb-3">
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <input type="text" name="search" class="form-control" placeholder="Search Students" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </div>
                    </form>
                </div>

                <!-- Students Table -->
                <div class="table-responsive">
                    <table class="table table-striped timetable-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Class</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students && $students->num_rows > 0): ?>
                                <?php while ($row = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                                        <td><?= htmlspecialchars($row['first_name']) ?></td>
                                        <td><?= htmlspecialchars($row['last_name']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                                        <td>
                                            <a href="edit_student.php?id=<?= $row['student_id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="?delete=<?= $row['student_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No students found.</td></tr>
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
                    <a href="export_students.php" class="btn btn-success"><i class="fas fa-download"></i> Export to CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php $conn->close(); include '../include/footer.php'; ?>