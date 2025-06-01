<?php
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php";
include 'include/header.php';
include 'include/navbar.php';
include 'include/sidebar.php';
include 'config/database.php';

$user_role = strtolower($_SESSION['user_role']);
$user_id = $_SESSION['user_id'] ?? 0;
$user_data = [];
$role_data = [];

if ($user_role === 'admin' || $user_role === 'teacher' || $user_role === 'student') {
    // Fetch user data from users table
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        echo "<div class='alert alert-danger'>User not found.</div>";
        include 'include/footer.php';
        exit;
    }
    $stmt->close();

    if ($user_role === 'admin') {
        // Fetch system stats
        $stats = [
            'teachers' => $conn->query("SELECT COUNT(*) AS count FROM teachers")->fetch_assoc()['count'],
            'students' => $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'],
            'courses' => $conn->query("SELECT COUNT(*) AS count FROM courses")->fetch_assoc()['count'],
            'classes' => $conn->query("SELECT COUNT(*) AS count FROM classes")->fetch_assoc()['count'],
        ];
        $role_data['stats'] = $stats;
    } elseif ($user_role === 'teacher') {
        // Map user_id to teacher_id via email
        $email = $user_data['email'];
        $stmt = $conn->prepare("SELECT teacher_id, first_name, last_name, department_id, specialization FROM teachers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $teacher = $result->fetch_assoc();
            $role_data['teacher'] = $teacher;
            // Fetch department name
            $stmt = $conn->prepare("SELECT department_name FROM departments WHERE department_id = ?");
            $stmt->bind_param("i", $teacher['department_id']);
            $stmt->execute();
            $dept_result = $stmt->get_result();
            $role_data['department'] = $dept_result->num_rows > 0 ? $dept_result->fetch_assoc()['department_name'] : 'N/A';
            $dept_result->close();
            // Fetch assigned courses
            $stmt = $conn->prepare("SELECT DISTINCT c.course_name, c.course_code 
                                    FROM timetables tt 
                                    JOIN courses c ON tt.course_id = c.course_id 
                                    WHERE tt.teacher_id = ?");
            $stmt->bind_param("i", $teacher['teacher_id']);
            $stmt->execute();
            $role_data['courses'] = $stmt->get_result();
        } else {
            echo "<div class='alert alert-danger'>Teacher profile not found.</div>";
            include 'include/footer.php';
            exit;
        }
        $stmt->close();
    } elseif ($user_role === 'student') {
        // Map user_id to student_id via email
        $email = $user_data['email'];
        $stmt = $conn->prepare("SELECT student_id, class_id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $role_data['student'] = $student;
            // Fetch class name
            $stmt = $conn->prepare("SELECT name FROM classes WHERE id = ?");
            $stmt->bind_param("i", $student['class_id']);
            $stmt->execute();
            $class_result = $stmt->get_result();
            $role_data['class'] = $class_result->num_rows > 0 ? $class_result->fetch_assoc()['name'] : 'N/A';
            $class_result->close();
            // Fetch enrolled courses
            $stmt = $conn->prepare("SELECT c.course_name, c.course_code 
                                    FROM student_courses sc 
                                    JOIN courses c ON sc.course_id = c.course_id 
                                    WHERE sc.student_id = ?");
            $stmt->bind_param("i", $student['student_id']);
            $stmt->execute();
            $role_data['courses'] = $stmt->get_result();
        } else {
            echo "<div class='alert alert-danger'>Student profile not found.</div>";
            include 'include/footer.php';
            exit;
        }
        $stmt->close();
    }
} else {
    echo "<div class='alert alert-danger'>Invalid user role.</div>";
    include 'include/footer.php';
    exit;
}
?>

<style>
body {
    background: #f7f7ff;
    margin-top: 20px;
}
.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 0 solid transparent;
    border-radius: .25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 6px 0 rgb(218 218 253 / 65%), 0 2px 6px 0 rgb(206 206 238 / 54%);
}
.me-2 {
    margin-right: .5rem !important;
}
</style>

<div class="container">
    <div class="main-body">
        <div class="row">
            <!-- Profile Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="https://bootdey.com/img/Content/avatar/avatar6.png" alt="Profile" class="rounded-circle p-1 bg-primary" width="110">
                            <div class="mt-3">
                                <h4><?= htmlspecialchars($user_data['name']) ?></h4>
                                <p class="text-secondary mb-1"><?= ucfirst($user_role) ?></p>
                                <?php if ($user_role === 'teacher' && isset($role_data['department'])): ?>
                                    <p class="text-muted font-size-sm"><?= htmlspecialchars($role_data['department']) ?></p>
                                <?php elseif ($user_role === 'student' && isset($role_data['class'])): ?>
                                    <p class="text-muted font-size-sm"><?= htmlspecialchars($role_data['class']) ?></p>
                                <?php else: ?>
                                    <p class="text-muted font-size-sm">University Administration</p>
                                <?php endif; ?>
                                <a href="<?= $user_role === 'admin' ? 'admin/dashboard.php' : ($user_role === 'teacher' ? 'teachers/dashboard.php' : 'students/dashboard.php') ?>" class="btn btn-primary">Back to Dashboard</a>
                            </div>
                        </div>
                        <hr class="my-4">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Email</h6>
                                <span class="text-secondary"><?= htmlspecialchars($user_data['email']) ?></span>
                            </li>
                            <?php if ($user_role === 'teacher' && isset($role_data['teacher']['specialization'])): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Specialization</h6>
                                    <span class="text-secondary"><?= htmlspecialchars($role_data['teacher']['specialization']) ?></span>
                                </li>
                            <?php elseif ($user_role === 'student' && isset($role_data['class'])): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <h6 class="mb-0"><i class="fas fa-chalkboard me-2"></i>Class</h6>
                                    <span class="text-secondary"><?= htmlspecialchars($role_data['class']) ?></span>
                                </li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0"><i class="fas fa-university me-2"></i>Institution</h6>
                                <span class="text-secondary">University Timetable System</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Details Card -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Full Name</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <span><?= htmlspecialchars($user_data['name']) ?></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Email</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <span><?= htmlspecialchars($user_data['email']) ?></span>
                            </div>
                        </div>
                        <?php if ($user_role === 'teacher' && isset($role_data['department'])): ?>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Department</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <span><?= htmlspecialchars($role_data['department']) ?></span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Specialization</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <span><?= htmlspecialchars($role_data['teacher']['specialization']) ?></span>
                                </div>
                            </div>
                        <?php elseif ($user_role === 'student' && isset($role_data['class'])): ?>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Class</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <span><?= htmlspecialchars($role_data['class']) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="d-flex align-items-center mb-3">
                            <?php echo $user_role === 'admin' ? 'System Statistics' : ($user_role === 'teacher' ? 'Assigned Courses' : 'Enrolled Courses'); ?>
                        </h5>
                        <?php if ($user_role === 'admin'): ?>
                            <p>Total Teachers: <span class="text-primary"><?= $role_data['stats']['teachers'] ?></span></p>
                            <div class="progress mb-3" style="height: 5px">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min($role_data['stats']['teachers'] * 10, 100) ?>%" aria-valuenow="<?= $role_data['stats']['teachers'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p>Total Students: <span class="text-primary"><?= $role_data['stats']['students'] ?></span></p>
                            <div class="progress mb-3" style="height: 5px">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= min($role_data['stats']['students'] * 5, 100) ?>%" aria-valuenow="<?= $role_data['stats']['students'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p>Total Courses: <span class="text-primary"><?= $role_data['stats']['courses'] ?></span></p>
                            <div class="progress mb-3" style="height: 5px">
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= min($role_data['stats']['courses'] * 10, 100) ?>%" aria-valuenow="<?= $role_data['stats']['courses'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p>Total Classes: <span class="text-primary"><?= $role_data['stats']['classes'] ?></span></p>
                            <div class="progress" style="height: 5px">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= min($role_data['stats']['classes'] * 10, 100) ?>%" aria-valuenow="<?= $role_data['stats']['classes'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        <?php elseif ($user_role === 'teacher' && isset($role_data['courses'])): ?>
                            <?php if ($role_data['courses']->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Course Name</th>
                                                <th>Course Code</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $count = 1; while ($course = $role_data['courses']->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $count++ ?></td>
                                                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No courses assigned.</p>
                            <?php endif; ?>
                        <?php elseif ($user_role === 'student' && isset($role_data['courses'])): ?>
                            <?php if ($role_data['courses']->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Course Name</th>
                                                <th>Course Code</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $count = 1; while ($course = $role_data['courses']->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $count++ ?></td>
                                                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No courses enrolled.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
<?php
$conn->close();
include 'include/footer.php';
?>