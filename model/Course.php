<?php
class Course {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAvailableCourses() {
        $stmt = $this->pdo->query("SELECT id, code AS course_code, name AS course_name, credits, schedule FROM courses WHERE active = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkConflicts($studentId, $courseIds) {
        $conflicts = [];
        $existingStmt = $this->pdo->prepare("SELECT c.schedule FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.student_id = ? AND r.status = 'approved'");
        $existingStmt->execute([$studentId]);
        $existingSchedules = $existingStmt->fetchAll(PDO::FETCH_ASSOC);

        $newStmt = $this->pdo->prepare("SELECT code, schedule FROM courses WHERE id IN (" . implode(',', array_fill(0, count($courseIds), '?')) . ")");
        $newStmt->execute($courseIds);
        $newCourses = $newStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($newCourses as $newCourse) {
            $newSchedule = json_decode($newCourse['schedule'], true);
            foreach ($existingSchedules as $existing) {
                $existSchedule = json_decode($existing['schedule'], true);
                if ($newSchedule['day'] === $existSchedule['day'] &&
                    !(strtotime($newSchedule['end']) <= strtotime($existSchedule['start']) ||
                      strtotime($newSchedule['start']) >= strtotime($existSchedule['end']))) {
                    $conflicts[] = "Conflict with {$newCourse['code']} on {$newSchedule['day']}";
                }
            }
        }
        return $conflicts;
    }

    public function checkPrerequisites($studentId, $courseIds) {
        $issues = [];
        $stmt = $this->pdo->prepare("SELECT code, prerequisites FROM courses WHERE id IN (" . implode(',', array_fill(0, count($courseIds), '?')) . ")");
        $stmt->execute($courseIds);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $completedStmt = $this->pdo->prepare("SELECT course_name FROM academic_records WHERE student_id = ? AND grade IN ('A', 'B', 'C')");
        $completedStmt->execute([$studentId]);
        $completedCourses = array_column($completedStmt->fetchAll(PDO::FETCH_ASSOC), 'course_name');

        foreach ($courses as $course) {
            $prereqs = explode(',', $course['prerequisites']);
            foreach ($prereqs as $prereq) {
                $prereq = trim($prereq);
                if ($prereq && !in_array($prereq, $completedCourses)) {
                    $issues[] = "Missing prerequisite {$prereq} for {$course['code']}";
                }
            }
        }
        return $issues;
    }

    public function registerCourses($studentId, $courseIds) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO registrations (student_id, course_id, status) VALUES (?, ?, ?)");
            foreach ($courseIds as $courseId) {
                $courseStmt = $this->pdo->prepare("SELECT prerequisites FROM courses WHERE id = ?");
                $courseStmt->execute([$courseId]);
                $prereqs = $courseStmt->fetchColumn();
                $status = empty($prereqs) ? 'approved' : 'pending';
                $stmt->execute([$studentId, $courseId, $status]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getPendingRegistrations() {
        $stmt = $this->pdo->query("SELECT r.id, s.name AS student_name, c.code AS course_code, c.name AS course_name 
                                   FROM registrations r 
                                   JOIN students s ON r.student_id = s.id 
                                   JOIN courses c ON r.course_id = c.id 
                                   WHERE r.status = 'pending'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveRegistration($registrationId) {
        $stmt = $this->pdo->prepare("UPDATE registrations SET status = 'approved' WHERE id = ?");
        return $stmt->execute([$registrationId]);
    }
}
?>
