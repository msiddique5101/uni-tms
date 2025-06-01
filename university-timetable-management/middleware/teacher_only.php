<?php
include 'auth_check.php';

if ($_SESSION['user_role'] !== 'Teacher') {
    header("Location: ../unauthorized.php");
    exit;
}
