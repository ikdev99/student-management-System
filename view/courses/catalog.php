<?php
session_start();
require_once('../../config/db.php');
require_once('../../model/Course.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../index.php");
    exit;
}

$courseModel = new Course($pdo);
$courses = $courseModel->getAvailableCourses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Catalog</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Course Catalog</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Schedule</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= htmlspecialchars($course['course_code']) ?></td>
                <td><?= htmlspecialchars($course['course_name']) ?></td>
                <td><?= (int)$course['credits'] ?></td>
                <td><?= htmlspecialchars($course['schedule']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <a href="registration_wizard.php">→ Register for Courses</a><br>
    <a href="../students/dashboard.php">← Back to Dashboard</a>
</body>
</html>
