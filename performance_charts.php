<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Get statistics for charts
try {
    $class_stats = getClassStats($pdo);
    $subject_stats = getSubjectStats($pdo);
    
    // Prepare data for charts
    $class_labels = [];
    $class_percentages = [];
    $class_students = [];
    
    $subject_labels = [];
    $subject_scores = [];
    $subject_counts = [];
    
    foreach ($class_stats as $class) {
        $class_labels[] = 'Class ' . $class['class'];
        $class_percentages[] = round($class['avg_percentage'] ?? 0, 2);
        $class_students[] = $class['total_students'];
    }
    
    foreach ($subject_stats as $subject) {
        $subject_labels[] = $subject['subject'];
        $subject_scores[] = round($subject['avg_score'] ?? 0, 2);
        $subject_counts[] = $subject['total_students'];
    }
    
} catch (PDOException $e) {
    error_log("Charts Error: " . $e->getMessage());
    $class_stats = [];
    $subject_stats = [];
    $_SESSION['error'] = 'Unable to load chart data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Charts - Student Performance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a href="performance_charts.php" class="nav-link active">
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
                    <i class="fas fa-chart-pie"></i>
                    Performance Charts & Analytics
                </h1>
                <p>Visual analysis of student performance across classes and subjects.</p>
            </header>
            
            <?php displayMessage(); ?>
            
            <!-- Class Performance Chart -->
            <div class="content-section">
                <h2><i class="fas fa-chart-bar"></i> Class-wise Performance</h2>
                <div class="chart-container">
                    <canvas id="classPerformanceChart"></canvas>
                </div>
            </div>
            
            <!-- Subject Performance Chart -->
            <div class="content-section">
                <h2><i class="fas fa-chart-line"></i> Subject-wise Average Scores</h2>
                <div class="chart-container">
                    <canvas id="subjectPerformanceChart"></canvas>
                </div>
            </div>
            
            <!-- Detailed Statistics -->
            <div class="content-section">
                <h2><i class="fas fa-table"></i> Detailed Statistics</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Total Students</th>
                                <th>Total Records</th>
                                <th>Average Percentage</th>
                                <th>Pass Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($class_stats): ?>
                                <?php foreach ($class_stats as $class): 
                                    $pass_rate = $class['total_records'] > 0 ? 
                                        round(($class['passed_records'] / $class['total_records']) * 100, 2) : 0;
                                ?>
                                <tr>
                                    <td>Class <?php echo htmlspecialchars($class['class']); ?></td>
                                    <td><?php echo $class['total_students']; ?></td>
                                    <td><?php echo $class['total_records']; ?></td>
                                    <td><?php echo round($class['avg_percentage'] ?? 0, 2); ?>%</td>
                                    <td>
                                        <div class="progress-container">
                                            <div class="progress-bar" style="width: <?php echo $pass_rate; ?>%">
                                                <?php echo $pass_rate; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No data available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Subject Statistics -->
            <div class="content-section">
                <h2><i class="fas fa-book"></i> Subject Statistics</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Total Students</th>
                                <th>Average Score</th>
                                <th>Minimum Score</th>
                                <th>Maximum Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($subject_stats): ?>
                                <?php foreach ($subject_stats as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['subject']); ?></td>
                                    <td><?php echo $subject['total_students']; ?></td>
                                    <td><?php echo round($subject['avg_score'] ?? 0, 2); ?>%</td>
                                    <td><?php echo round($subject['min_score'] ?? 0, 2); ?>%</td>
                                    <td><?php echo round($subject['max_score'] ?? 0, 2); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No subject data available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Class Performance Chart
            const classCtx = document.getElementById('classPerformanceChart').getContext('2d');
            const classChart = new Chart(classCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($class_labels); ?>,
                    datasets: [{
                        label: 'Average Percentage',
                        data: <?php echo json_encode($class_percentages); ?>,
                        backgroundColor: 'rgba(67, 97, 238, 0.7)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Average: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Percentage (%)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Classes'
                            }
                        }
                    }
                }
            });
            
            // Subject Performance Chart
            const subjectCtx = document.getElementById('subjectPerformanceChart').getContext('2d');
            const subjectChart = new Chart(subjectCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($subject_labels); ?>,
                    datasets: [{
                        label: 'Average Score',
                        data: <?php echo json_encode($subject_scores); ?>,
                        backgroundColor: [
                            'rgba(67, 97, 238, 0.8)',
                            'rgba(114, 9, 183, 0.8)',
                            'rgba(76, 201, 240, 0.8)',
                            'rgba(248, 150, 30, 0.8)',
                            'rgba(247, 37, 133, 0.8)',
                            'rgba(46, 204, 113, 0.8)',
                            'rgba(155, 89, 182, 0.8)',
                            'rgba(241, 196, 15, 0.8)',
                            'rgba(230, 126, 34, 0.8)',
                            'rgba(231, 76, 60, 0.8)'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    return label + ': ' + value.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                }
            });
            
            // Make charts responsive on window resize
            window.addEventListener('resize', function() {
                classChart.resize();
                subjectChart.resize();
            });
        });
    </script>
</body>
</html>