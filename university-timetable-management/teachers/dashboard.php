<?php
include '../middleware/teacher_only.php';
include '../include/header.php';
include '../include/sidebar.php';
include '../include/navbar.php';
?>

<div class="main-content wrapper flex-grow-1">
    <div class="container mt-4">
        <h3 class="mb-4">Teacher Dashboard</h3>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Assigned Courses</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Web Development - Section A</li>
                    <li>Data Structures - Section C</li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Class Schedule</h5>
            </div>
            <div class="card-body">
                <p>Today: Web Dev (10am), DS (2pm)</p>
                <a href="../timetable/view_timetable.php" class="btn btn-outline-primary btn-sm mt-2">View Full Schedule</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Student Performance</h5>
            </div>
            <div class="card-body">
                <p>Coming soon: Charts and analytics on student performance.</p>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/script.js"></script>

<?php include '../include/footer.php'; ?>
