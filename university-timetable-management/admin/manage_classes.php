<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
include '../config/database.php';

// Check if the user is an admin
if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Search and pagination parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Handle class addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_class'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $stmt = $conn->prepare("INSERT INTO classes (name, created_at, updated_at) VALUES (?, NOW(), NOW())");
    if ($stmt === false) {
        die("Prepare failed for INSERT: " . $conn->error);
    }
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_classes.php");
    exit;
}

// Handle class deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed for DELETE: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_classes.php");
    exit;
}

// Fetch classes with search and pagination
$sql = "SELECT * FROM classes WHERE name LIKE ? LIMIT ? OFFSET ?";
$search_param = "%$search%";
error_log("SQL: $sql, Search Param: $search_param, Limit: $limit, Offset: $offset");
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed for SELECT: " . $conn->error);
}
$stmt->bind_param("sii", $search_param, $limit, $offset);
$stmt->execute();
$classes = $stmt->get_result();
$stmt->close();

// Fetch total number of classes for pagination
$total_sql = "SELECT COUNT(*) as total FROM classes WHERE name LIKE ?";
$stmt = $conn->prepare($total_sql);
if ($stmt === false) {
    die("Prepare failed for COUNT: " . $conn->error);
}
$stmt->bind_param("s", $search_param);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_pages = ceil($total_result['total'] / $limit);
$stmt->close();
?>

<div class="wrapper">
    <div class="container mt-4">
        <h4 class="mb-3"><i class="fas fa-chalkboard"></i> Manage Classes</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Add Class Form -->
                <div class="mb-4">
                    <form method="POST" class="row g-3">
                        <div class="col-auto">
                            <input type="text" name="name" class="form-control" placeholder="Class Name" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_class" class="btn btn-primary">Add Class</button>
                        </div>
                    </form>
                </div>

                <!-- Search -->
                <div class="mb-3">
                    <form method="GET" class="row g-2">
                        <div class="col-auto">
                            <input type="text" name="search" class="form-control" placeholder="Search Classes" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                        </div>
                    </form>
                </div>

                <!-- Classes Table -->
                <div class="table-responsive">
                    <table class="table table-striped timetable-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($classes->num_rows > 0): ?>
                                <?php while ($row = $classes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                                        <td><?= htmlspecialchars($row['updated_at']) ?></td>
                                        <td>
                                            <a href="edit_class.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No classes found.</td></tr>
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
                    <a href="export_classes.php" class="btn btn-success"><i class="fas fa-download"></i> Export to CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<?php
$conn->close();
include '../include/footer.php';
?>