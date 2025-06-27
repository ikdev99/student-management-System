<?php
// controller/UploadController.php

session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../model/Upload.php');

// Check if user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../index.php");
    exit;
}

$uploadModel = new Upload($pdo);
$studentId = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_upload'])) {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "File upload failed.";
        header("Location: ../view/uploads/upload_form.php");
        exit;
    }

    $file = $_FILES['file'];
    $comments = $_POST['comments'] ?? '';

    // Create uploads directory if not exists
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Check if previous versions exist for this student + original file name to version correctly
    $latestVersion = $uploadModel->getLatestVersion($studentId, $file['name']);
    $newVersion = $latestVersion + 1;

    // Generate unique file name to prevent conflicts
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFileName = $studentId . '_' . time() . '_v' . $newVersion . '.' . $fileExt;

    $targetPath = $uploadDir . $uniqueFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Save metadata to DB
        $saved = $uploadModel->saveUpload($studentId, $uniqueFileName, $file['name'], $comments, $newVersion);

        if ($saved) {
            $_SESSION['message'] = "File uploaded successfully as version {$newVersion}.";
        } else {
            $_SESSION['error'] = "Failed to save upload info.";
        }
    } else {
        $_SESSION['error'] = "Error moving uploaded file.";
    }

    header("Location: ../view/uploads/upload_form.php");
    exit;
}

// Redirect back if accessed without POST
header("Location: ../view/uploads/upload_form.php");
exit;
?>
