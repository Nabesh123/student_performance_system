<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Redirect if no student_id
if (!isset($_GET['student_id'])) {
    $_SESSION['error'] = 'No student selected.';
    header('Location: manage_marks.php');
    exit();
}

$student_id = intval($_GET['student_id']);
$student = getStudentById($pdo, $student_id);

if (!$student) {
    $_SESSION['error'] = 'Student not found.';
    header('Location: manage_marks.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject']);
    $marks_obtained = intval($_POST['marks_obtained']);
    $total_marks = intval($_POST['total_marks']);
    $semester = sanitize($_POST['semester']);
    
    if (empty($subject) || $marks_obtained < 0 || $total_marks <= 0) {
        $_SESSION['error'] = 'Please fill all fields correctly.';
        header("Location: add_marks.php?student_id=$student_id");
        exit();
    }
    
    // Calculate grade
    $percentage = ($marks_obtained / $total_marks) * 100;
    $grade = calculateGrade($percentage);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO academic_performance 
            (student_id, subject, marks_obtained, total_marks, grade, semester) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$student_id, $subject, $marks_obtained, $total_marks, $grade, $semester])) {
            $_SESSION['success'] = 'Marks added successfully!';
            header("Location: marks_analysis.php?student_id=$student_id");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Add Marks Error: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to add marks. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Marks - Student Performance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .quick-actions {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .quick-actions .btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Performance</h2>
                <p>Management System</p>
            </div>
            
            <nav class="nav-menu">
                <ul>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="add_student.php" class="nav-link">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Student</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="marks_analysis.php" class="nav-link">
                            <i class="fas fa-chart-line"></i>
                            <span>Marks Analysis</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="performance_charts.php" class="nav-link">
                            <i class="fas fa-chart-pie"></i>
                            <span>Performance Charts</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_marks.php" class="nav-link active">
                            <i class="fas fa-cogs"></i>
                            <span>Manage Marks</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo getUserInitials($_SESSION['username']); ?>
                    </div>
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                        <p>Administrator</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="page-header">
                <h1>
                    <i class="fas fa-plus-circle"></i>
                    Add Marks for <?php echo htmlspecialchars($student['full_name']); ?>
                </h1>
                <p>Roll No: <?php echo htmlspecialchars($student['roll_number']); ?> | 
                   Class: <?php echo htmlspecialchars($student['class'] . '-' . $student['section']); ?></p>
            </header>
            
            <?php displayMessage(); ?>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="marks_analysis.php?student_id=<?php echo $student_id; ?>" class="btn">
                    <i class="fas fa-eye"></i> View Student Analysis
                </a>
                <a href="manage_marks.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Back to All Students
                </a>
            </div>
            
            <!-- Add Marks Form -->
            <div class="content-section">
                <form method="POST" action="" class="form-container">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select Subject</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Science">Science</option>
                                <option value="English">English</option>
                                <option value="Social Studies">Social Studies</option>
                                <option value="Hindi">Hindi</option>
                                <option value="Physics">Physics</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Biology">Biology</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject_other">If Other, specify:</label>
                            <input type="text" id="subject_other" name="subject_other" 
                                   placeholder="Enter subject name">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="semester">Semester/Exam *</label>
                            <select id="semester" name="semester" required>
                                <option value="">Select Exam Type</option>
                                <option value="Semester 1">Semester 1</option>
                                <option value="Semester 2">Semester 2</option>
                                <option value="Final Exam">Final Exam</option>
                                <option value="Mid Term">Mid Term</option>
                                <option value="Unit Test 1">Unit Test 1</option>
                                <option value="Unit Test 2">Unit Test 2</option>
                                <option value="Pre-Board">Pre-Board</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="exam_date">Exam Date</label>
                            <input type="date" id="exam_date" name="exam_date" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="marks_obtained">Marks Obtained *</label>
                            <input type="number" id="marks_obtained" name="marks_obtained" 
                                   min="0" max="100" required 
                                   placeholder="0-100">
                        </div>
                        
                        <div class="form-group">
                            <label for="total_marks">Total Marks</label>
                            <input type="number" id="total_marks" name="total_marks" 
                                   value="100" min="1" max="200" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Grade Prediction:</label>
                        <div id="gradePrediction" class="alert info">
                            Enter marks to see predicted grade
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Marks
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset Form
                        </button>
                        <a href="marks_analysis.php?student_id=<?php echo $student_id; ?>" class="btn">
                            <i class="fas fa-arrow-left"></i> Back to Analysis
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Grade prediction
        const marksInput = document.getElementById('marks_obtained');
        const totalMarksInput = document.getElementById('total_marks');
        const gradePrediction = document.getElementById('gradePrediction');
        const subjectSelect = document.getElementById('subject');
        const subjectOther = document.getElementById('subject_other');
        
        function updateGradePrediction() {
            if (marksInput && totalMarksInput && gradePrediction) {
                const marks = parseFloat(marksInput.value);
                const total = parseFloat(totalMarksInput.value);
                
                if (marks && total && total > 0) {
                    const percentage = (marks / total) * 100;
                    let grade = 'F';
                    let color = '#f72585';
                    
                    if (percentage >= 85) { grade = 'A+'; color = '#4cc9f0'; }
                    else if (percentage >= 75) { grade = 'A'; color = '#4361ee'; }
                    else if (percentage >= 65) { grade = 'B+'; color = '#7209b7'; }
                    else if (percentage >= 55) { grade = 'B'; color = '#f8961e'; }
                    else if (percentage >= 40) { grade = 'C'; color = '#f8961e'; }
                    
                    gradePrediction.innerHTML = `
                        <strong>Predicted Grade:</strong> <span style="color: ${color}; font-weight: bold;">${grade}</span><br>
                        <strong>Percentage:</strong> ${percentage.toFixed(2)}%<br>
                        <strong>Status:</strong> ${percentage >= 40 ? 'PASS' : 'FAIL'}
                    `;
                } else {
                    gradePrediction.innerHTML = 'Enter marks to see predicted grade';
                }
            }
        }
        
        // Handle subject selection
        subjectSelect.addEventListener('change', function() {
            if (this.value === 'Other') {
                subjectOther.required = true;
                subjectOther.style.display = 'block';
            } else {
                subjectOther.required = false;
                subjectOther.style.display = 'none';
            }
        });
        
        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (subjectSelect.value === 'Other' && !subjectOther.value.trim()) {
                e.preventDefault();
                alert('Please specify the subject name');
                subjectOther.focus();
            }
        });
        
        // Initialize
        if (marksInput && totalMarksInput) {
            marksInput.addEventListener('input', updateGradePrediction);
            totalMarksInput.addEventListener('input', updateGradePrediction);
        }
    </script>
</body>
</html>