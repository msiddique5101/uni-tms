<?php
// Ensure session is started (this should already be handled in authorize.php)
if (!isset($_SESSION)) {
    session_start();
}
?>

<aside class="sidebar bg-dark text-white vh-100 position-fixed">
    <ul class="nav flex-column p-3">
        <!-- Common Navigation Links for All Roles -->
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="view_students.php" class="nav-link text-white">
                <i class="fas fa-user-graduate"></i> Students
            </a>
        </li>
        <li class="nav-item">
            <a href="view_teachers.php" class="nav-link text-white">
                <i class="fas fa-chalkboard-teacher"></i> Teachers
            </a>
        </li>
        <li class="nav-item">
            <a href="../timetable/view_timetable.php" class="nav-link text-white">
                <i class="fas fa-calendar"></i> Timetable
            </a>
        </li>
        <li class="nav-item">
            <a href="subjects/view_subjects.php" class="nav-link text-white">
                <i class="fas fa-book"></i> Subjects
            </a>
        </li>
        <li class="nav-item">
            <a href="/" class="nav-link text-white">
                <i class="fas fa-file-alt"></i> Results
            </a>
        </li>

        <!-- Admin-Only Section -->
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
            <li class="nav-item">
                <!-- Collapsible Admin Panel Section -->
                <a class="nav-link text-white" data-bs-toggle="collapse" href="#adminPanel" role="button" aria-expanded="false" aria-controls="adminPanel">
                    <i class="fas fa-user-shield"></i> Admin Panel
                </a>
                <div class="collapse" id="adminPanel">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a href="manage_classes.php" class="nav-link text-white">
                                <i class="fas fa-chalkboard"></i> Manage Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_students.php" class="nav-link text-white">
                                <i class="fas fa-users"></i> Manage Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_teachers.php" class="nav-link text-white">
                                <i class="fas fa-chalkboard-teacher"></i> Manage Teachers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_timetables.php" class="nav-link text-white">
                                <i class="fas fa-calendar-alt"></i> Manage Timetables
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <!-- Logout Link (Visible to All Roles) -->
        <li class="nav-item mt-auto">
            <a href="../auth/logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</aside>

<!-- Ensure Bootstrap JS is included for the collapse functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>