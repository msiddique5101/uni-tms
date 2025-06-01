<?php 
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/middleware/authorize.php"; 
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/include/header.php"; 
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/include/navbar.php"; 
include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/config/database.php"; 

$role = strtolower($_SESSION['user_role']);
$user_id = $_SESSION['user_id'];

?>


<div class="container mt-4">
    <h4 class="mb-3"><i class="fas fa-book-open"></i> Courses</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted">
                <?php if ($role === 'admin'): ?>
                    You can add, edit, or delete courses.
                <?php elseif ($role === 'student'): ?>
                    Here are the courses you're enrolled in.
                <?php elseif ($role === 'teacher'): ?>
                    Here are the courses you've been assigned.
                <?php endif; ?>
            </p>

            <?php if ($role === 'admin'): ?>
                <a href="add_course.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Add New Course</a>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Credits</th>
                            <th>Department</th>
                            <?php if ($role === 'admin'): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query based on role
                        if ($role === 'admin') {
                            $query = "SELECT course_id, course_code, course_name, credit_hours, department FROM courses";
                        } elseif ($role === 'student') {
                            $query = "SELECT c.course_id, c.course_code, c.course_name, c.credit_hours, c.department 
                                      FROM courses c
                                      JOIN student_courses sc ON sc.course_id = c.course_id
                                      WHERE sc.student_id = $user_id";
                        } elseif ($role === 'teacher') {
                            $query = "SELECT c.course_id, c.course_code, c.course_name, c.credit_hours, c.department 
                                      FROM courses c
                                      JOIN teacher_subjects ts ON ts.subject_id = c.course_id
                                      WHERE ts.teacher_id = $user_id";
                        } else {
                            $query = ""; // Unknown role
                        }

                        $result = $conn->query($query);
                        $count = 1;

                        if ($result && $result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= htmlspecialchars($row['credit_hours']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <?php if ($role === 'admin'): ?>
                            <td>
                                <a href="edit_course.php?id=<?= $row['course_id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                <a href="delete_course.php?id=<?= $row['course_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                        <tr><td colspan="<?= $role === 'admin' ? 6 : 5 ?>" class="text-center">No courses found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/university-timetable-management/include/footer.php"; ?>
