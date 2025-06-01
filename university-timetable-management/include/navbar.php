<?php include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/config/config.php"; ?>
<?php


    if (session_status() === PHP_SESSION_NONE) session_start();
    $current_page = basename($_SERVER['PHP_SELF']);
    $role = $_SESSION['user_role'] ?? 'guest'; // guest, student, teacher, admin

    // Determine dashboard path based on user role
        $dashboard_path = '';

        switch ($role) {
            case 'Admin':
                $dashboard_path = 'admin/dashboard.php';
                break;
            case 'Teacher':
                $dashboard_path = 'teachers/dashboard.php';
                break;
            case 'Student':
                $dashboard_path = 'students/dashboard.php';
                break;
                default:
                $dashboard_path = 'index.php'; // fallback if needed
                $course_path = 'auth/login';
                $timetable_path = 'auth/login';
                $attendance_path = 'auth/login';
                break;
            }

            $course_path = 'courses/courses.php';
            $timetable_path = 'timetable/view_timetable.php';
            $today_timetable_path = 'timetable/today_timetable.php';
            $attendance_path = 'attendance/attendance.php';
            
            
        $dashboard_file = basename($dashboard_path);
        $course_file = basename($course_path);
        $timetable_file = basename($timetable_path);
        $today_timetable_file = basename($today_timetable_path);
        $attendance_file = basename($attendance_path);

?>

<!-- Use BASE_URL for absolute paths -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/sidebar.css">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
    <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#" style="cursor: pointer;">
        <i class="fas fa-university"></i> UniPortal
    </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == $dashboard_file) ? 'active' : '' ?>" href="<?= BASE_URL.$dashboard_path ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == $course_file) ? 'active' : '' ?>" href="<?= BASE_URL.$course_path ?>">
                        <i class="fas fa-book-open"></i> Courses
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == $timetable_file) ? 'active' : '' ?>" href="<?= BASE_URL.$timetable_path ?>">
                        <i class="fas fa-calendar-alt"></i> Timetable
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == $today_timetable_file) ? 'active' : '' ?>" href="<?= BASE_URL.$today_timetable_path ?>">
                        <i class="fas fa-user-graduate"></i> Today's Schedule
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == $attendance_file) ? 'active' : '' ?>" href="<?= BASE_URL.$attendance_path ?>">
                        <i class="fas fa-check-circle"></i> Attendance
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'exams.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>exams/exams.php">
                        <i class="fas fa-file-alt"></i> Exams
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'results.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>results/results.php">
                        <i class="fas fa-chart-line"></i> Results
                    </a>
                </li>

                <?php if ($role == 'Admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'settings.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>settings.php">
                            <i class="fas fa-cogs"></i> Settings
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle text-white" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                        <img src="<?= BASE_URL ?>assets/images/profile.jpg" alt="Profile" class="rounded-circle" width="35" height="35">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</nav>