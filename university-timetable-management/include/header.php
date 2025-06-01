<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php
$base_path = (basename(dirname($_SERVER['SCRIPT_NAME'])) == "students" || 
              basename(dirname($_SERVER['SCRIPT_NAME'])) == "teachers" || 
              basename(dirname($_SERVER['SCRIPT_NAME'])) == "subjects") ? "../" : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Timetable Management</title>
    <link rel="stylesheet" href="<?= $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/YOUR-FONT-AWESOME-KEY.js" crossorigin="anonymous"></script>

</head>
<script src="<?= $base_path; ?>assets/js/script.js"></script>

<body>
