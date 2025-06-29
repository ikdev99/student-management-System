<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /student-management/view/students/dashboard.php");
    exit;
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/controller/AuthController.php';
    $auth = new AuthController($pdo);
    $auth->login($_POST['email'], $_POST['password']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management System - Login</title>
    <link rel="stylesheet" href="/student-management/assets/css/style.css">
</head>
<body>
    <h2>Login</h2>
    <form method="POST" action="/student-management/index.php">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>

    <?php if (isset($_GET['error'])): ?>
        <p style="color:red"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>
</body>
</html>
