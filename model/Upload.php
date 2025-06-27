<?php
// model/Upload.php

class Upload {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get latest version number for a student's uploaded file by original name
    public function getLatestVersion($studentId, $originalFileName) {
        $stmt = $this->pdo->prepare("SELECT MAX(version) as max_version FROM uploads WHERE student_id = ? AND original_name = ?");
        $stmt->execute([$studentId, $originalFileName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['max_version'] ?? 0;
    }

    // Save upload metadata
    public function saveUpload($studentId, $fileName, $originalName, $comments, $version) {
        $stmt = $this->pdo->prepare("INSERT INTO uploads (student_id, file_name, original_name, comments, version, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$studentId, $fileName, $originalName, $comments, $version]);
    }

    // Fetch uploads for a student (optional)
    public function getUploadsByStudent($studentId) {
        $stmt = $this->pdo->prepare("SELECT * FROM uploads WHERE student_id = ? ORDER BY submitted_at DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
