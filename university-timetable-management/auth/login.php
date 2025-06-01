<?php
session_start();
include '../config/database.php';

// Redirect user if already logged in
if (isset($_SESSION['user_role'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'Admin') {
        header("Location: ../admin/dashboard.php");
        exit;
    } elseif ($role === 'Teacher') {
        header("Location: ../teachers/dashboard.php");
        exit;
    } elseif ($role === 'Student') {
        header("Location: ../students/dashboard.php");
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_role'] = $row['role'];

            // Redirect immediately
            if ($row['role'] === 'Admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($row['role'] === 'Teacher') {
                header("Location: ../teachers/dashboard.php");
            } elseif ($row['role'] === 'Student') {
                header("Location: ../students/dashboard.php");
            }
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<?php include '../include/header.php'; ?>
<link rel="stylesheet" href="../assets/css/auth.css">

<div class="auth-container">
    <div class="auth-box">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required class="form-control">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required class="form-control">
            </div>
            <button type="submit" class="btn btn-success btn-block">Login</button>
        </form>
        <!-- <p class="auth-link">Don't have an account? <a href="register.php">Register</a></p> -->
    </div>
</div>

<?php include '../include/footer.php'; ?>
