<?php
session_start();

// 1. Redirect if the user is not logged in
if (!isset($_SESSION['user_role'])) {
    header("Location: /university-timetable-management/auth/login.php");
    exit();
}

// 2. Get the current page name (e.g., view_students.php)
$currentPage = str_replace($_SERVER['DOCUMENT_ROOT'] . '/university-timetable-management/', '', $_SERVER['SCRIPT_FILENAME']);


// 3. Load permissions config
$permissionsFile = $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/config/permissions.php";

if (!file_exists($permissionsFile)) {
    die("Permissions file not found.");
}
$permissions = include $permissionsFile;

// 4. Get the user's role
$role = strtolower($_SESSION['user_role']);


// 5. Check if role exists in permissions
if (!array_key_exists($role, $permissions)) {
    header("Location: /university-timetable-management/unauthorized.php");
    exit();
}

// 6. Check if current page is allowed for the role
if (!in_array($currentPage, $permissions[$role])) {
    header("Location: /university-timetable-management/unauthorized.php");
    exit();
}
?>