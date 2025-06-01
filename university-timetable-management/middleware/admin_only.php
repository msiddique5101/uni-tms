<?php
include 'auth_check.php';

if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: ../unauthorized.php");
    exit;
}
