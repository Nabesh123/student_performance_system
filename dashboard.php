<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Get dashboard statistics
try {
    // Total students
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
    $total_students = $stmt->fetch()['total'];
    
    // Total classes
    $stmt = $pdo->query("SELECT COUNT(DISTINCT class) as total FROM students");
    $total_classes = $stmt->fetch()['total'];
    
    // Performance statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_records,
            AVG((marks_obtained / total_marks) * 100) as avg_percentage,
            SUM(CASE WHEN (marks_obtained / total_marks) * 100 >= 40 THEN 1 ELSE 0 END) as passed
        FROM academic_performance
    ");
    $performance = $stmt->fetch();
    
    $pass_rate = 0;
    if ($performance['total_records'] > 0) {
        $pass_rate = round(($performance['passed'] / $performance['total_records']) * 100, 2);
        $avg_percentage = round($performance['avg_percentage'], 2);
    }
    
    // Recent students
    $stmt = $pdo->query("SELECT * FROM students ORDER BY student_id DESC LIMIT 5");
    $recent_students = $stmt->fetchAll();
    
    // Recent marks
    $stmt = $pdo->query("
        SELECT ap.*, s.full_name, s.roll_number 
        FROM academic_performance ap
        JOIN students s ON ap.student_id = s.student_id
        ORDER BY ap.created_at DESC 
        LIMIT 5
    ");
    $recent_marks = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $_SESSION['error'] = 'Unable to load dashboard statistics.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Performance System</title>
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
                        <a href="dashboard.php" class="nav-link active">
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
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard Overview
                </h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Here's what's happening with your students.</p>
            </header>
            
            <?php displayMessage(); ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Total Students</h3>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>Active Students</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-chalkboard"></i> Classes</h3>
                    <div class="stat-number"><?php echo $total_classes; ?></div>
                    <div class="stat-trend">
                        <i class="fas fa-book"></i>
                        <span>Active Classes</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-chart-line"></i> Pass Rate</h3>
                    <div class="stat-number"><?php echo $pass_rate; ?>%</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up trend-up"></i>
                        <span>Overall Performance</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-percentage"></i> Average Score</h3>
                    <div class="stat-number"><?php echo $avg_percentage ?? 0; ?>%</div>
                    <div class="stat-trend">
                        <i class="fas fa-chart-bar"></i>
                        <span>Class Average</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Students -->
            <div class="content-section">
                <h2><i class="fas fa-user-clock"></i> Recently Added Students</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Roll No.</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Email</th>                           
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_students): ?>
                                <?php foreach ($recent_students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td>Class <?php echo htmlspecialchars($student['class']); ?></td>
                                    <td><?php echo htmlspecialchars($student['section']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email'] ?: 'N/A'); ?></td>

                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No students found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>