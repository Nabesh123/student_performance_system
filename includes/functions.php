<?php
// Include config
require_once 'config.php';

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number
function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

// Function to check if student exists
function studentExists($pdo, $roll_number, $student_id = null) {
    $sql = "SELECT student_id FROM students WHERE roll_number = ?";
    $params = [$roll_number];
    
    if ($student_id) {
        $sql .= " AND student_id != ?";
        $params[] = $student_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount() > 0;
}

// Function to get student by ID
function getStudentById($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetch();
}

// Function to get all students
function getAllStudents($pdo) {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY class, roll_number");
    return $stmt->fetchAll();
}

// Function to get student performance
function getStudentPerformance($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM academic_performance 
        WHERE student_id = ? 
        ORDER BY semester, subject
    ");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll();
}

// Function to calculate student statistics
function calculateStudentStats($pdo, $student_id) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_subjects,
            SUM(marks_obtained) as total_obtained,
            SUM(total_marks) as total_max,
            AVG((marks_obtained / total_marks) * 100) as avg_percentage,
            SUM(CASE WHEN (marks_obtained / total_marks) * 100 >= 40 THEN 1 ELSE 0 END) as passed_subjects
        FROM academic_performance 
        WHERE student_id = ?
    ");
    $stmt->execute([$student_id]);
    $stats = $stmt->fetch();
    
    if ($stats['total_subjects'] > 0) {
        $stats['avg_percentage'] = round($stats['avg_percentage'], 2);
        $stats['pass_percentage'] = round(($stats['passed_subjects'] / $stats['total_subjects']) * 100, 2);
        $stats['overall_grade'] = calculateGrade($stats['avg_percentage']);
        $stats['status'] = $stats['avg_percentage'] >= 40 ? 'Pass' : 'Fail';
    }
    
    return $stats;
}

// Function to get class statistics
function getClassStats($pdo) {
    $stmt = $pdo->query("
        SELECT 
            s.class,
            COUNT(DISTINCT s.student_id) as total_students,
            COUNT(ap.id) as total_records,
            AVG((ap.marks_obtained / ap.total_marks) * 100) as avg_percentage,
            SUM(CASE WHEN (ap.marks_obtained / ap.total_marks) * 100 >= 40 THEN 1 ELSE 0 END) as passed_records
        FROM students s
        LEFT JOIN academic_performance ap ON s.student_id = ap.student_id
        GROUP BY s.class
        ORDER BY s.class
    ");
    return $stmt->fetchAll();
}

// Function to get subject statistics
function getSubjectStats($pdo) {
    $stmt = $pdo->query("
        SELECT 
            subject,
            COUNT(*) as total_students,
            AVG((marks_obtained / total_marks) * 100) as avg_score,
            MIN((marks_obtained / total_marks) * 100) as min_score,
            MAX((marks_obtained / total_marks) * 100) as max_score
        FROM academic_performance 
        GROUP BY subject
        ORDER BY avg_score DESC
    ");
    return $stmt->fetchAll();
}
?>