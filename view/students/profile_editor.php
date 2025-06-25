<?php
session_start();
require_once('../../config/db.php');
require_once('../../model/Student.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../../index.php");
    exit;
}

$studentModel = new Student($pdo);
$student = $studentModel->getProfile($_SESSION['student_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
</head>
<body>
    <h2>Edit Your Contact Information</h2>
    <form method="POST" action="../../controller/StudentController.php">
        <input type="hidden" name="update_profile" value="1">

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>"><br><br>

        <label>Address:</label><br>
        <textarea name="address"><?= htmlspecialchars($student['address']) ?></textarea><br><br>

        <button type="submit">Save Changes</button>
        <a href="dashboard.php">Cancel</a>
    </form>
</body>
</html>
