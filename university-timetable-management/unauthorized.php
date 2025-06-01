<?php
session_start();
$role = $_SESSION['user_role'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Access Denied</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Update if your main CSS is elsewhere -->
    <style>
        body {
            background-color: #f2f2f2;
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
        }
        .container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        h1 {
            color: #d9534f;
        }
        p {
            margin-top: 15px;
            font-size: 18px;
        }
        a.button {
            display: inline-block;
            margin-top: 20px;
            background-color: #0275d8;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        a.button:hover {
            background-color: #025aa5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Access Denied</h1>
        <p>Your role <strong><?= htmlspecialchars($role) ?></strong> does not have permission to access this page.</p>
        <?php if (isset($_SESSION['user_role'])): ?>
            <a class="button" href="dashboard.php">Go to Dashboard</a>
        <?php else: ?>
            <a class="button" href="auth/login.php">Login Again</a>
        <?php endif; ?>
    </div>
</body>
</html>
