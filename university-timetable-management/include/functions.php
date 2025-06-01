<?php
function checkAccess($page) {
    session_start();
    $role = $_SESSION['role'] ?? null;
    if (!$role) {
        header("Location: /university-timetable-management/auth/login.php");
        exit();
    }

    $permissions = include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/config/permissions.php";
    if (!in_array($page, $permissions[$role])) {
        header("Location: /university-timetable-management/unauthorized.php");
        exit();
    }
}
