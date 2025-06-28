<?php
class AuthController {
    public function login($email, $password) {
        require __DIR__ . '/../config/db.php';

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: view/admin/dashboard.php");
            } else {
                header("Location: view/students/dashboard.php");
            }
        } else {
            header("Location: index.php?error=Invalid credentials");
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header("Location: index.php");
    }
}
