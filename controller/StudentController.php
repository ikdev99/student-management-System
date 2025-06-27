<?php
// controller/StudentController.php

session_start();

require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../model/Student.php');

$studentModel = new Student($pdo);

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$studentId = $_SESSION['student_id'];

// Handle POST request for updating profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

    $success = $studentModel->updateContactInfo($studentId, $phone, $email, $address);

    if ($success) {
        $_SESSION['message'] = "Profile updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update profile.";
    }

    header("Location: ../view/students/dashboard.php");
    exit;
}

// Handle GET request for academic history (AJAX or direct)
if (isset($_GET['action']) && $_GET['action'] === 'academic_history') {
    header('Content-Type: application/json');
    $history = $studentModel->getAcademicHistory($studentId);
    echo json_encode($history);
    exit;
}

// Otherwise, redirect to dashboard
header("Location: ../view/students/dashboard.php");
exit;
?>
