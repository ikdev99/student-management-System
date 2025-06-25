<?php
// view/students/dashboard.php
session_start();
require_once('../../config/db.php');
require_once('../../model/Student.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../index.php");
    exit;
}

$studentModel = new Student($pdo);
$profile = $studentModel->getProfile($_SESSION['student_id']);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($profile['name']) ?></h1>

    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-info">
        <p><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($profile['phone']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($profile['address']) ?></p>
    </div>

    <nav>
        <ul>
            <li><a href="profile_editor.php">Edit Profile</a></li>
            <li><a href="academic_history.php">View Academic History</a></li>
            <li><a href="../../view/courses/registration_wizard.php">Course Registration</a></li>
            <li><a href="../../view/uploads/upload_form.php">Submit Assignments</a></li>
            <li><a href="../../logout.php">Logout</a></li>
        </ul>
    </nav>
</body>
</html>
