<?php
// controller/CourseController.php

session_start();

require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../model/Course.php');

$courseModel = new Course($pdo);

// Ensure user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$studentId = $_SESSION['student_id'];

// Handle AJAX request to fetch course catalog
if (isset($_GET['action']) && $_GET['action'] === 'catalog') {
    header('Content-Type: application/json');
    $courses = $courseModel->getAvailableCourses();
    echo json_encode($courses);
    exit;
}

// Handle POST request to register for courses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_courses'])) {
    $selectedCourses = $_POST['courses'] ?? [];

    // Validate courses array
    if (!is_array($selectedCourses) || count($selectedCourses) == 0) {
        $_SESSION['error'] = "No courses selected.";
        header("Location: ../view/courses/registration_wizard.php");
        exit;
    }

    // Check for schedule conflicts and prerequisites
    $conflicts = $courseModel->checkConflicts($studentId, $selectedCourses);
    $prereqIssues = $courseModel->checkPrerequisites($studentId, $selectedCourses);

    if (!empty($conflicts)) {
        $_SESSION['error'] = "Schedule conflicts detected: " . implode(", ", $conflicts);
        header("Location: ../view/courses/registration_wizard.php");
        exit;
    }

    if (!empty($prereqIssues)) {
        $_SESSION['error'] = "Prerequisite issues for courses: " . implode(", ", $prereqIssues);
        header("Location: ../view/courses/registration_wizard.php");
        exit;
    }

    // Proceed to register courses (some may need advisor approval)
    $success = $courseModel->registerCourses($studentId, $selectedCourses);

    if ($success) {
        $_SESSION['message'] = "Courses registered successfully. Await advisor approval if needed.";
        header("Location: ../view/courses/registration_wizard.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to register courses.";
        header("Location: ../view/courses/registration_wizard.php");
        exit;
    }
}

// Default redirect if no valid action
header("Location: ../view/courses/catalog.php");
exit;
?>
