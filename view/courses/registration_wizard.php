<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../model/Course.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../index.php");
    exit;
}

$courseModel = new Course($pdo);
$courses = $courseModel->getAvailableCourses();
$registrations = $courseModel->getPendingRegistrations();

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Registration</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <h2>Course Registration</h2>

    <?php if ($message): ?>
        <p class="message success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="../../controller/CourseController.php">
        <input type="hidden" name="register_courses" value="1">
        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Course Code</th>
                    <th>Name</th>
                    <th>Credits</th>
                    <th>Schedule</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><input type="checkbox" name="courses[]" value="<?= $course['id'] ?>" 
                                   <?php if (in_array($course['id'], array_column($registrations, 'course_id'))): ?>disabled<?php endif; ?>></td>
                        <td><?= htmlspecialchars($course['course_code']) ?></td>
                        <td><?= htmlspecialchars($course['course_name']) ?></td>
                        <td><?= htmlspecialchars($course['credits']) ?></td>
                        <td><?= htmlspecialchars($course['schedule']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <button type="submit">Submit Registration</button>
    </form>

    <h3>Pending Registrations</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Status</th>
        </tr>
        <?php foreach ($registrations as $reg): ?>
            <?php if ($reg['student_name'] === $studentModel->getProfile($_SESSION['student_id'])['name']): ?>
                <tr>
                    <td><?= htmlspecialchars($reg['course_code']) ?></td>
                    <td><?= htmlspecialchars($reg['course_name']) ?></td>
                    <td><?= htmlspecialchars($reg['status']) ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>

    <br>
    <a href="conflict_checker.php">Check Conflicts</a><br>
    <a href="../students/dashboard.php">← Back to Dashboard</a>
</body>
</html>
