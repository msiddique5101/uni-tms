<?php include 'include/header.php'; ?>

<div class="hero-section text-center text-white d-flex align-items-center">
    <div class="container">
        <h1 class="display-4">University Timetable Management</h1>
        <p class="lead">Simplify student scheduling, teacher assignments, and timetable management.</p>
        <a href="auth/login.php" class="btn btn-primary btn-lg me-2">Login</a>
        <a href="auth/register.php" class="btn btn-outline-light btn-lg">Register</a>
    </div>
</div>

<div class="container mt-5">
    <div class="row text-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                    <h5>Timetable Automation</h5>
                    <p>Automatically generate and manage university schedules efficiently.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                    <h5>Teacher Performance</h5>
                    <p>Monitor and evaluate teachers' performance with ease.</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <i class="fas fa-users fa-3x text-danger mb-3"></i>
                    <h5>Student Attendance</h5>
                    <p>Track student attendance and generate detailed reports.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'include/footer.php'; ?>
