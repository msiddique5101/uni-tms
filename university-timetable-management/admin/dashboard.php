<?php
include '../middleware/admin_only.php';
include '../include/header.php';
include '../include/sidebar.php';
include '../include/navbar.php';
include '../config/database.php';

// Fetch counts from the database
$studentCount = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$teacherCount = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'];
$timetableCount = $conn->query("SELECT COUNT(*) AS total FROM timetables")->fetch_assoc()['total'];
?>

<!-- Dashboard Content -->
<div class="main-content wrapper flex-grow-1">
    <div class="container py-5">
        <div class="mb-4">
            <h2 class="fw-bold text-primary">Admin Dashboard</h2>
            <p class="text-muted">Welcome back, Admin! Here's an overview of the platform statistics.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column align-items-start">
                        <div class="mb-3">
                            <i class="fas fa-user-graduate fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">Total Students</h5>
                        <p class="fs-4 fw-semibold text-dark"><?= $studentCount ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column align-items-start">
                        <div class="mb-3">
                            <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                        </div>
                        <h5 class="card-title">Total Teachers</h5>
                        <p class="fs-4 fw-semibold text-dark"><?= $teacherCount ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column align-items-start">
                        <div class="mb-3">
                            <i class="fas fa-calendar-alt fa-2x text-warning"></i>
                        </div>
                        <h5 class="card-title">Total Timetables</h5>
                        <p class="fs-4 fw-semibold text-dark"><?= $timetableCount ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h4 class="fw-bold text-secondary mb-3">Quick Links</h4>
            <div class="list-group">
                <a href="../manage/teachers.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    Manage Teachers <i class="fas fa-arrow-right text-muted"></i>
                </a>
                <a href="../manage/students.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    Manage Students <i class="fas fa-arrow-right text-muted"></i>
                </a>
                <a href="../timetable/manage.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    Manage Timetable <i class="fas fa-arrow-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../include/footer.php'; ?>

<!-- <script src="../assets/js/script.js"></script> -->
