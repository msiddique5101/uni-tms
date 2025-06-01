<?php
include '../config/database.php';

$student_id = $_GET['student_id'];
$result = $conn->query("SELECT * FROM reports WHERE student_id = $student_id");

if ($row = $result->fetch_assoc()) {
    echo "<h2>Report for Student ID: " . $student_id . "</h2>";
    echo "<p>Subject: " . $row['subject'] . "</p>";
    echo "<p>Grade: " . $row['grade'] . "</p>";
} else {
    echo "No report found.";
}
?>


<?php
include '../include/header.php';
include '../include/navbar.php';
include '../include/sidebar.php';
?>

<div class="content">
    <h2>Generate Reports</h2>
    <form action="generate_report_process.php" method="POST">
        <select name="report_type">
            <option value="student_performance">Student Performance</option>
            <option value="teacher_performance">Teacher Performance</option>
            <option value="attendance">Attendance Report</option>
        </select>
        <button type="submit">Generate</button>
    </form>
</div>

<?php include '../include/footer.php'; ?>
