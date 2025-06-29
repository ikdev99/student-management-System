<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/Student.php';

// Check if vendor/autoload.php exists for QR code library
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] vendor/autoload.php not found, QR code generation disabled\n", FILE_APPEND);
}

$studentModel = new Student($pdo);

if (!isset($_SESSION['user_id'])) {
    file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Unauthorized access to StudentController\n", FILE_APPEND);
    header("Location: /student-management/index.php");
    exit;
}

$studentId = $_SESSION['student_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if ($_SESSION['user_role'] !== 'student' && $_SESSION['user_role'] !== 'admin') {
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Unauthorized profile update attempt\n", FILE_APPEND);
        header("Location: /student-management/index.php");
        exit;
    }

    $studentId = $_POST['student_id'] ?? $studentId;
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

    file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Attempting profile update for student ID: $studentId, Email: $email\n", FILE_APPEND);

    $success = $studentModel->updateContactInfo($studentId, $name, $email, $phone, $address);

    if ($success) {
        $_SESSION['message'] = "Profile updated successfully.";
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Profile updated successfully for student ID: $studentId\n", FILE_APPEND);
    } else {
        $_SESSION['error'] = "Failed to update profile.";
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Profile update failed for student ID: $studentId\n", FILE_APPEND);
    }

    header($_SESSION['user_role'] === 'admin' ? "Location: /student-management/view/admin/student_search.php" : "Location: /student-management/view/students/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_academic']) && $_SESSION['user_role'] === 'admin') {
    $studentId = $_POST['student_id'];
    $academicRecord = filter_var($_POST['academic_record'], FILTER_SANITIZE_STRING);

    file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Attempting academic record update for student ID: $studentId\n", FILE_APPEND);

    $success = $studentModel->updateAcademicRecord($studentId, $academicRecord);

    if ($success) {
        $_SESSION['message'] = "Academic record updated.";
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Academic record updated for student ID: $studentId\n", FILE_APPEND);
    } else {
        $_SESSION['error'] = "Failed to update academic record.";
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Academic record update failed for student ID: $studentId\n", FILE_APPEND);
    }
    header("Location: /student-management/view/admin/student_search.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'academic_history') {
    header('Content-Type: application/json');
    $history = $studentModel->getAcademicHistory($studentId);
    file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Fetched academic history for student ID: $studentId\n", FILE_APPEND);
    echo json_encode($history);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'search' && $_SESSION['user_role'] === 'admin') {
    $query = filter_var($_GET['query'], FILTER_SANITIZE_STRING);
    $students = $studentModel->searchStudents($query);
    file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Admin search performed with query: $query\n", FILE_APPEND);
    header('Content-Type: application/json');
    echo json_encode($students);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'generate_qr' && $studentId) {
    $profile = $studentModel->getProfile($studentId);
    if (!$profile) {
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Failed to fetch profile for student ID: $studentId\n", FILE_APPEND);
        header("Location: /student-management/view/students/dashboard.php?error=Profile not found");
        exit;
    }

    $qrData = "Student ID: $studentId, Email: " . $profile['email'];
    $qrPath = __DIR__ . '/../Uploads/qr_' . $studentId . '.png';

    // Ensure Uploads directory exists
    $uploadDir = __DIR__ . '/../Uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Created Uploads directory\n", FILE_APPEND);
    }

    // Generate QR code only if library is available
    if (class_exists('\chillerlan\QRCode\QRCode')) {
        try {
            $qrCode = new \chillerlan\QRCode\QRCode();
            $qrCode->render($qrData, $qrPath);
            file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] QR code generated for student ID: $studentId at $qrPath\n", FILE_APPEND);

            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="student_id_' . $studentId . '.png"');
            readfile($qrPath);
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] QR code generation failed: " . $e->getMessage() . "\n", FILE_APPEND);
            header("Location: /student-management/view/students/dashboard.php?error=QR code generation failed: " . urlencode($e->getMessage()));
        }
    } else {
        file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] QR code library not found for student ID: $studentId\n", FILE_APPEND);
        header("Location: /student-management/view/students/dashboard.php?error=QR code library not installed");
    }
    exit;
}

file_put_contents(__DIR__ . '/../debug.log', "[" . date('Y-m-d H:i:s') . "] Invalid action or missing student ID\n", FILE_APPEND);
header("Location: /student-management/view/students/dashboard.php");
exit;
?>
