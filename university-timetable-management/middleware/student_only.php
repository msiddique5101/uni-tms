<?php
include 'auth_check.php';

if ($_SESSION['user_role'] !== 'Student') {
    header("Location: ../unauthorized.php");
    exit;
}
