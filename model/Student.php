<?php
// model/Student.php

class Student {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch student profile data
    public function getProfile($studentId) {
        $stmt = $this->pdo->prepare("SELECT id, name, email, phone, address FROM students WHERE id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update student's contact info
    public function updateContactInfo($studentId, $phone, $email, $address) {
        $stmt = $this->pdo->prepare("UPDATE students SET phone = ?, email = ?, address = ? WHERE id = ?");
        return $stmt->execute([$phone, $email, $address, $studentId]);
    }

    // Fetch academic history records
    public function getAcademicHistory($studentId) {
        $stmt = $this->pdo->prepare("SELECT course_name, grade, semester FROM academic_records WHERE student_id = ? ORDER BY semester DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
