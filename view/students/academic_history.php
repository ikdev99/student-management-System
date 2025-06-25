<?php
session_start();
require_once('../../config/db.php');
require_once('../../model/Student.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../index.php");
    exit;
}

$studentModel = new Student($pdo);
$history = $studentModel->getAcademicHistory($_SESSION['student_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Academic History</title>
</head>
<body>
    <h2>Your Academic History</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Course</th>
            <th>Grade</th>
            <th>Semester</th>
        </tr>
        <?php foreach ($history as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['course_name']) ?></td>
                <td><?= htmlspecialchars($row['grade']) ?></td>
                <td><?= htmlspecialchars($row['semester']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
