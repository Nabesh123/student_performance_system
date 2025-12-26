<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_number = sanitize($_POST['roll_number']);
    $full_name = sanitize($_POST['full_name']);
    $class = sanitize($_POST['class']);
    $section = sanitize($_POST['section']);
    $gender = sanitize($_POST['gender']);
    $dob = $_POST['dob'];
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    // Validation
    if (empty($roll_number) || empty($full_name) || empty($class) || empty($section) || empty($gender) || empty($dob)) {
        $error = 'Please fill all required fields.';
    } elseif ($email && !validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($phone && !validatePhone($phone)) {
        $error = 'Please enter a valid phone number.';
    } elseif (studentExists($pdo, $roll_number)) {
        $error = 'Student with this roll number already exists.';
    } else {
        try {
            // Calculate age from DOB
            $birthDate = new DateTime($dob);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            
            if ($age < 5) {
                $error = 'Student must be at least 5 years old.';
            } else {
                // Insert student
                $stmt = $pdo->prepare("
                    INSERT INTO students 
                    (roll_number, full_name, class, section, gender, date_of_birth, email, phone, address) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$roll_number, $full_name, $class, $section, $gender, $dob, $email, $phone, $address])) {
                    $success = 'Student added successfully!';
                    $_POST = []; // Clear form
                } else {
                    $error = 'Failed to add student. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("Add Student Error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Student Performance System</title>
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
                        <a href="add_student.php" class="nav-link active">
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
                    <i class="fas fa-user-plus"></i>
                    Add New Student
                </h1>
                <p>Fill in the student details below to add them to the system.</p>
            </header>
            
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="content-section">
                <div class="form-container">
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="roll_number">Roll Number *</label>
                                <input type="text" id="roll_number" name="roll_number" required
                                       placeholder="Enter roll number" 
                                       value="<?php echo $_POST['roll_number'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" required
                                       placeholder="Enter full name"
                                       value="<?php echo $_POST['full_name'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="class">Class *</label>
                                <select id="class" name="class" required>
                                    <option value="">Select Class</option>
                                    <option value="9" <?php echo ($_POST['class'] ?? '') == '9' ? 'selected' : ''; ?>>9th Grade</option>
                                    <option value="10" <?php echo ($_POST['class'] ?? '') == '10' ? 'selected' : ''; ?>>10th Grade</option>
                                    <option value="11" <?php echo ($_POST['class'] ?? '') == '11' ? 'selected' : ''; ?>>11th Grade</option>
                                    <option value="12" <?php echo ($_POST['class'] ?? '') == '12' ? 'selected' : ''; ?>>12th Grade</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="section">Section *</label>
                                <select id="section" name="section" required>
                                    <option value="">Select Section</option>
                                    <option value="A" <?php echo ($_POST['section'] ?? '') == 'A' ? 'selected' : ''; ?>>A</option>
                                    <option value="B" <?php echo ($_POST['section'] ?? '') == 'B' ? 'selected' : ''; ?>>B</option>
                                    <option value="C" <?php echo ($_POST['section'] ?? '') == 'C' ? 'selected' : ''; ?>>C</option>
                                    <option value="D" <?php echo ($_POST['section'] ?? '') == 'D' ? 'selected' : ''; ?>>D</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="gender">Gender *</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($_POST['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($_POST['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($_POST['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="dob">Date of Birth *</label>
                                <input type="date" id="dob" name="dob" required
                                       max="<?php echo date('Y-m-d', strtotime('-5 years')); ?>"
                                       value="<?php echo $_POST['dob'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email"
                                       placeholder="Enter email address"
                                       value="<?php echo $_POST['email'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       placeholder="Enter phone number"
                                       value="<?php echo $_POST['phone'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"
                                      placeholder="Enter student's address"><?php echo $_POST['address'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Add Student
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset Form
                            </button>
                            <a href="dashboard.php" class="btn">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Age calculation
        document.getElementById('dob').addEventListener('change', function() {
            const dob = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            
            if (age < 5) {
                alert('Student must be at least 5 years old.');
                this.value = '';
            }
        });
    </script>
</body>
</html>