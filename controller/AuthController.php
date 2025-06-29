<?php
class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($email, $password) {
        // Debug log setup
        $logFile = __DIR__ . '/../debug.log';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Login attempt for email: $email\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        $stmt = $this->pdo->prepare("SELECT u.id, u.email, u.password, u.role, s.id as student_id 
                                     FROM users u 
                                     LEFT JOIN students s ON u.email = s.email 
                                     WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Debug log user fetch result
        if ($user) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] User found: ID {$user['id']}, Role {$user['role']}, Password {$user['password']}\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] No user found for email: $email\n", FILE_APPEND);
            header("Location: /student-management/index.php?error=Invalid credentials");
            exit;
        }

        // Plain text password check to match current DB
        if ($password === $user['password']) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Password match successful for email: $email\n", FILE_APPEND);
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['student_id'] = $user['student_id'] ?? null;

            if ($user['role'] === 'admin') {
                header("Location: /student-management/view/admin/dashboard.php");
            } else {
                header("Location: /student-management/view/students/dashboard.php");
            }
        } else {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Password match failed. Provided: $password, Expected: {$user['password']}\n", FILE_APPEND);
            header("Location: /student-management/index.php?error=Invalid credentials");
        }
        exit;
    }

    public function logout() {
        session_start();
        session_destroy();
        header("Location: /student-management/index.php");
        exit;
    }
}
?>
