<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

// Get all students with performance data
try {
    $stmt = $pdo->query("
        SELECT s.*, 
               COUNT(ap.id) as marks_count,
               AVG((ap.marks_obtained / ap.total_marks) * 100) as avg_percentage
        FROM students s
        LEFT JOIN academic_performance ap ON s.student_id = ap.student_id
        GROUP BY s.student_id
        ORDER BY s.class, s.roll_number
    ");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Manage Marks Error: " . $e->getMessage());
    $students = [];
    $_SESSION['error'] = 'Unable to load student data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Marks - Student Performance System</title>
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
                    <i class="fas fa-cogs"></i>
                    Manage Student Marks
                </h1>
                <p>View and manage marks for all students in the system.</p>
            </header>
            
            <?php displayMessage(); ?>
            
            <!-- Quick Actions -->
            <div class="content-section">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="quick-actions">
                    <a href="add_student.php" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add New Student
                    </a>
                    <a href="marks_analysis.php" class="btn">
                        <i class="fas fa-chart-line"></i> Marks Analysis
                    </a>
                    <a href="performance_charts.php" class="btn">
                        <i class="fas fa-chart-pie"></i> View Charts
                    </a>
                </div>
            </div>
            
            <!-- Search Box -->
            <div class="content-section">
                <h2><i class="fas fa-search"></i> Search Students</h2>
                <div class="search-box">
                    <input type="text" id="searchInput" 
                           placeholder="Search by name, roll number, class, or section...">
                </div>
            </div>
            
            <!-- Students List -->
            <div class="content-section">
                <h2><i class="fas fa-users"></i> All Students</h2>
                
                <?php if (empty($students)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-graduate"></i>
                        <h3>No Students Found</h3>
                        <p>No students have been added to the system yet.</p>
                        <a href="add_student.php" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Add First Student
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table" id="studentsTable">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Name</th>
                                    <th>Class-Section</th>
                                    <th>Gender</th>
                                    <th>Marks Count</th>
                                    <th>Average %</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $avg_percentage = $student['avg_percentage'] ? round($student['avg_percentage'], 2) : 0;
                                    $status_class = $avg_percentage >= 40 ? 'badge-success' : ($avg_percentage > 0 ? 'badge-danger' : 'badge-info');
                                    $status_text = $avg_percentage >= 40 ? 'Passing' : ($avg_percentage > 0 ? 'Failing' : 'No Data');
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['full_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($student['email'] ?: 'No email'); ?></small>
                                    </td>
                                    <td>Class <?php echo htmlspecialchars($student['class']); ?>-<?php echo htmlspecialchars($student['section']); ?></td>
                                    <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $student['marks_count'] > 0 ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $student['marks_count']; ?> subjects
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($student['marks_count'] > 0): ?>
                                            <div class="progress-container">
                                                <div class="progress-bar" style="width: <?php echo min($avg_percentage, 100); ?>%">
                                                    <?php echo $avg_percentage; ?>%
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No Data</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="marks_analysis.php?student_id=<?php echo $student['student_id']; ?>" 
                                               class="btn btn-sm" title="View Analysis">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="add_marks.php?student_id=<?php echo $student['student_id']; ?>" 
                                               class="btn btn-sm btn-success" title="Add Marks">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="add_student.php?edit=<?php echo $student['student_id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit Student">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Export functionality
        function exportTableToCSV(filename) {
            const table = document.getElementById('studentsTable');
            const rows = table.querySelectorAll('tr');
            const csv = [];
            
            rows.forEach(row => {
                const rowData = [];
                const cols = row.querySelectorAll('th, td');
                
                cols.forEach(col => {
                    // Remove action buttons
                    if (!col.querySelector('.actions')) {
                        rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
                    }
                });
                
                if (rowData.length > 0) {
                    csv.push(rowData.join(','));
                }
            });
            
            const csvString = csv.join('\n');
            const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            
            if (navigator.msSaveBlob) {
                navigator.msSaveBlob(blob, filename);
            } else {
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
        
        // Add export button
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.page-header h1');
            if (header && document.querySelector('#studentsTable')) {
                const exportBtn = document.createElement('button');
                exportBtn.className = 'btn btn-sm';
                exportBtn.innerHTML = '<i class="fas fa-download"></i> Export CSV';
                exportBtn.style.marginLeft = '20px';
                exportBtn.onclick = () => exportTableToCSV('students_data.csv');
                header.appendChild(exportBtn);
            }
        });
    </script>
</body>
</html>