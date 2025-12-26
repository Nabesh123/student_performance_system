<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$students = getAllStudents($pdo);
$selected_student = null;
$analysis = [];
$performance_data = [];

// Handle student selection
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    $selected_student = getStudentById($pdo, $student_id);
    
    if ($selected_student) {
        $performance_data = getStudentPerformance($pdo, $student_id);
        $stats = calculateStudentStats($pdo, $student_id);
        
        if (!empty($performance_data)) {
            $analysis = [
                'stats' => $stats,
                'subjects' => $performance_data
            ];
        }
    }
}

// Handle marks addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_marks'])) {
    $student_id = intval($_POST['student_id']);
    $subject = sanitize($_POST['subject']);
    $marks_obtained = intval($_POST['marks_obtained']);
    $total_marks = intval($_POST['total_marks']);
    $semester = sanitize($_POST['semester']);
    
    if (empty($subject) || $marks_obtained < 0 || $total_marks <= 0) {
        $_SESSION['error'] = 'Please fill all fields correctly.';
        header("Location: marks_analysis.php?student_id=$student_id");
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
        header("Location: marks_analysis.php?student_id=$student_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks Analysis - Student Performance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
                        <a href="marks_analysis.php" class="nav-link active">
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
                        <a href="manage_marks.php" class="nav-link">
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
                    <i class="fas fa-chart-line"></i>
                    Marks Analysis & Prediction
                </h1>
                <p>Analyze student performance and predict academic outcomes.</p>
            </header>
            
            <?php displayMessage(); ?>
            
            <!-- Student Selection -->
            <div class="content-section">
                <h2><i class="></i> Select Student</h2>
                <div class="search-box">
                    <form method="GET" action="">
                        <select name="student_id" required onchange="this.form.submit()">
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>"
                                    <?php echo (isset($_GET['student_id']) && $_GET['student_id'] == $student['student_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['roll_number'] . ' - ' . $student['full_name'] . ' (Class ' . $student['class'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            
            <?php if ($selected_student): ?>
                <!-- Student Information -->
                <div class="content-section">
                    <h2><i class="fas fa-user-graduate"></i> Student Information</h2>
                    <div class="card-grid">
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-id-card"></i> Personal Details</h3>
                            </div>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($selected_student['full_name']); ?></p>
                            <p><strong>Roll No:</strong> <?php echo htmlspecialchars($selected_student['roll_number']); ?></p>
                            <p><strong>Class:</strong> Class <?php echo htmlspecialchars($selected_student['class']); ?> - Section <?php echo htmlspecialchars($selected_student['section']); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars($selected_student['gender']); ?></p>
                            <p><strong>Date of Birth:</strong> <?php echo date('F j, Y', strtotime($selected_student['date_of_birth'])); ?></p>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-address-book"></i> Contact Information</h3>
                            </div>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($selected_student['email'] ?: 'Not Provided'); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($selected_student['phone'] ?: 'Not Provided'); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($selected_student['address'] ?: 'Not Provided'); ?></p>
                            <p><strong>Enrollment Date:</strong> <?php echo date('F j, Y', strtotime($selected_student['enrollment_date'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Add Marks Form -->
                <div class="content-section">
                    <h2><i class="fas fa-plus-circle"></i> Add New Marks</h2>
                    <form method="POST" action="" class="form-container">
                        <input type="hidden" name="student_id" value="<?php echo $selected_student['student_id']; ?>">
                        <input type="hidden" name="add_marks" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="subject">Subject *</label>
                                <input type="text" id="subject" name="subject" required 
                                       placeholder="e.g., Mathematics, Science, English">
                            </div>
                            
                            <div class="form-group">
                                <label for="semester">Semester *</label>
                                <select id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="Semester 1">Semester 1</option>
                                    <option value="Semester 2">Semester 2</option>
                                    <option value="Final Exam">Final Exam</option>
                                    <option value="Mid Term">Mid Term</option>
                                    <option value="Unit Test">Unit Test</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="marks_obtained">Marks Obtained *</label>
                                <input type="number" id="marks_obtained" name="marks_obtained" 
                                       min="0" max="100" required 
                                       placeholder="Enter marks (0-100)">
                            </div>
                            
                            <div class="form-group">
                                <label for="total_marks">Total Marks</label>
                                <input type="number" id="total_marks" name="total_marks" 
                                       value="100" min="1" max="200" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Grade Prediction</label>
                            <div id="gradePrediction" class="alert info">
                                Enter marks to see predicted grade
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Add Marks
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php if (!empty($analysis)): ?>
                    <!-- Performance Statistics -->
                    <div class="content-section">
                        <h2><i class="fas fa-chart-bar"></i> Performance Statistics</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3><i class="fas fa-book"></i> Total Subjects</h3>
                                <div class="stat-number"><?php echo $analysis['stats']['total_subjects']; ?></div>
                            </div>
                            
                            <div class="stat-card">
                                <h3><i class="fas fa-percentage"></i> Average Percentage</h3>
                                <div class="stat-number"><?php echo $analysis['stats']['avg_percentage']; ?>%</div>
                            </div>
                            
                            <div class="stat-card <?php echo $analysis['stats']['status'] == 'Pass' ? 'pass' : 'fail'; ?>">
                                <h3><i class="fas fa-chart-line"></i> Overall Status</h3>
                                <div class="stat-number"><?php echo $analysis['stats']['status']; ?></div>
                            </div>
                            
                            <div class="stat-card">
                                <h3><i class="fas fa-graduation-cap"></i> Overall Grade</h3>
                                <div class="stat-number"><?php echo $analysis['stats']['overall_grade']; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Marks Details -->
                    <div class="content-section">
                        <h2><i class="fas fa-list-alt"></i> Subject-wise Marks</h2>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Semester</th>
                                        <th>Marks Obtained</th>
                                        <th>Total Marks</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analysis['subjects'] as $subject): 
                                        $percentage = ($subject['marks_obtained'] / $subject['total_marks']) * 100;
                                        $status = $percentage >= 40 ? 'Pass' : 'Fail';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['semester']); ?></td>
                                        <td><?php echo $subject['marks_obtained']; ?></td>
                                        <td><?php echo $subject['total_marks']; ?></td>
                                        <td><?php echo round($percentage, 2); ?>%</td>
                                        <td>
                                            <span class="badge badge-success"><?php echo $subject['grade']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $status == 'Pass' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Performance Prediction -->
                    <div class="content-section">
                        <h2><i class="fas fa-crystal-ball"></i> Performance Prediction</h2>
                        <div class="card-grid">
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-chart-pie"></i> Current Performance</h3>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar" style="width: <?php echo $analysis['stats']['avg_percentage']; ?>%">
                                        <?php echo $analysis['stats']['avg_percentage']; ?>%
                                    </div>
                                </div>
                                <p><strong>Classification:</strong> 
                                    <?php
                                    $avg = $analysis['stats']['avg_percentage'];
                                    if ($avg >= 75) echo 'Excellent';
                                    elseif ($avg >= 60) echo 'Good';
                                    elseif ($avg >= 40) echo 'Average';
                                    else echo 'Needs Improvement';
                                    ?>
                                </p>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h3><i class="fas fa-trophy"></i> Predictions</h3>
                                </div>
                                <p><strong>Final Exam Prediction:</strong> 
                                    <?php echo $analysis['stats']['status'] == 'Pass' ? 'Likely to Pass' : 'Needs Attention'; ?>
                                </p>
                                <p><strong>Subjects Passed:</strong> <?php echo $analysis['stats']['passed_subjects']; ?> out of <?php echo $analysis['stats']['total_subjects']; ?></p>
                                <p><strong>Pass Percentage:</strong> <?php echo $analysis['stats']['pass_percentage']; ?>%</p>
                                <p><strong>Recommendation:</strong> 
                                    <?php
                                    if ($analysis['stats']['avg_percentage'] >= 75) {
                                        echo 'Continue excellent work';
                                    } elseif ($analysis['stats']['avg_percentage'] >= 60) {
                                        echo 'Focus on weak subjects';
                                    } elseif ($analysis['stats']['avg_percentage'] >= 40) {
                                        echo 'Needs regular practice';
                                    } else {
                                        echo 'Requires special attention';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="content-section">
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h3>No Marks Recorded</h3>
                            <p>This student doesn't have any marks recorded yet. Use the form above to add marks.</p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="content-section">
                    <div class="empty-state">
                        <i class="fas fa-user-graduate"></i>
                        <h3>Select a Student</h3>
                        <p>Please select a student from the dropdown above to view or add marks.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Grade prediction
        const marksInput = document.getElementById('marks_obtained');
        const totalMarksInput = document.getElementById('total_marks');
        const gradePrediction = document.getElementById('gradePrediction');
        
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
        
        if (marksInput && totalMarksInput) {
            marksInput.addEventListener('input', updateGradePrediction);
            totalMarksInput.addEventListener('input', updateGradePrediction);
        }
    </script>
</body>
</html>