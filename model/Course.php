<?php
// model/Course.php

class Course {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get list of available courses
    public function getAvailableCourses() {
        $stmt = $this->pdo->query("SELECT id, course_code, course_name, credits, schedule FROM courses WHERE active = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check for schedule conflicts for student
    public function checkConflicts($studentId, $courseIds) {
        // This is simplified. You would compare schedules of selected courses with student's existing schedule.
        // Return array of conflicting course names or empty array if none.
        return [];
    }

    // Check prerequisites
    public function checkPrerequisites($studentId, $courseIds) {
        // Check if student meets prerequisites for selected courses.
        // Return array of course names that fail prerequisites or empty array if none.
        return [];
    }

    // Register courses for the student
    public function registerCourses($studentId, $courseIds) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO registrations (student_id, course_id, status) VALUES (?, ?, 'pending')");
            foreach ($courseIds as $courseId) {
                $stmt->execute([$studentId, $courseId]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
