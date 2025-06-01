<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $department_id = !empty($_POST['department_id']) ? $_POST['department_id'] : null;
    $specialization = $_POST['specialization'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO teachers (first_name, last_name, email, department_id, specialization, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $email, $department_id, $specialization, $password);

    if ($stmt->execute()) {
        // Redirect to view_teachers.php before any output is sent
        header("Location: view_teachers.php");
        exit();
    } else {
        $error_message = "Error adding teacher.";
    }
}

// Include UI components after handling form submission
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
?>

<div class="container mt-5">
    <h2 class="mb-4">Add Teacher</h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message; ?></div>
    <?php endif; ?>

    <form method="post" class="p-4 border rounded shadow bg-light">
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department ID</label>
            <input type="text" name="department_id" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Specialization</label>
            <input type="text" name="specialization" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Add Teacher</button>
    </form>
</div>

<?php include '../include/footer.php'; ?>
