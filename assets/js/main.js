// Main JavaScript for Student Performance System

document.addEventListener('DOMContentLoaded', function() {
    // ===== MOBILE MENU TOGGLE =====
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
        
        // Close menu when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    document.body.classList.remove('menu-open');
                }
            }
        });
    }
    
    // ===== PASSWORD VISIBILITY TOGGLE =====
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            this.innerHTML = type === 'password' ? '👁️' : '👁️‍🗨️';
        });
    });
    
    // ===== FORM VALIDATION =====
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                    
                    // Create error message
                    let errorMsg = field.nextElementSibling;
                    if (!errorMsg || !errorMsg.classList.contains('error-msg')) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-msg';
                        errorMsg.textContent = 'This field is required';
                        errorMsg.style.color = '#f72585';
                        errorMsg.style.fontSize = '0.85rem';
                        errorMsg.style.marginTop = '5px';
                        field.parentNode.insertBefore(errorMsg, field.nextSibling);
                    }
                } else {
                    field.classList.remove('error');
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-msg')) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    });
    
    // ===== REAL-TIME FORM VALIDATION =====
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-msg')) {
                errorMsg.remove();
            }
        });
        
        // Email validation
        if (input.type === 'email') {
            input.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('error');
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-msg';
                    errorMsg.textContent = 'Please enter a valid email address';
                    errorMsg.style.color = '#f72585';
                    errorMsg.style.fontSize = '0.85rem';
                    errorMsg.style.marginTop = '5px';
                    this.parentNode.insertBefore(errorMsg, this.nextSibling);
                }
            });
        }
        
        // Phone validation
        if (input.type === 'tel') {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9+-]/g, '');
            });
        }
    });
    
    // ===== AUTO-CALCULATE GRADES =====
    const marksInputs = document.querySelectorAll('input[name="marks_obtained"], input[name="total_marks"]');
    marksInputs.forEach(input => {
        input.addEventListener('input', function() {
            const form = this.closest('form');
            if (!form) return;
            
            const marksObtained = form.querySelector('input[name="marks_obtained"]');
            const totalMarks = form.querySelector('input[name="total_marks"]');
            const gradeDisplay = form.querySelector('#grade_display, .grade-prediction');
            
            if (marksObtained && totalMarks && gradeDisplay) {
                const marks = parseFloat(marksObtained.value);
                const total = parseFloat(totalMarks.value);
                
                if (marks && total && total > 0) {
                    const percentage = (marks / total) * 100;
                    let grade = 'F';
                    let color = '#f72585';
                    
                    if (percentage >= 85) { grade = 'A+'; color = '#4cc9f0'; }
                    else if (percentage >= 75) { grade = 'A'; color = '#4361ee'; }
                    else if (percentage >= 65) { grade = 'B+'; color = '#7209b7'; }
                    else if (percentage >= 55) { grade = 'B'; color = '#f8961e'; }
                    else if (percentage >= 40) { grade = 'C'; color = '#f8961e'; }
                    
                    if (gradeDisplay.id === 'grade_display') {
                        gradeDisplay.value = grade + ' (' + percentage.toFixed(2) + '%)';
                    } else {
                        gradeDisplay.innerHTML = `
                            <span style="color: ${color}; font-weight: bold; font-size: 1.2em;">
                                ${grade} (${percentage.toFixed(2)}%)
                            </span>
                            <br>
                            <small>Status: ${percentage >= 40 ? 'PASS' : 'FAIL'}</small>
                        `;
                    }
                }
            }
        });
    });
    
    // ===== SEARCH FUNCTIONALITY =====
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr, .card-grid .card');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    if (row.classList.contains('card')) {
                        row.style.animation = 'fadeInUp 0.6s ease';
                    }
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // ===== DATA TABLE SORTING =====
    const tableHeaders = document.querySelectorAll('.table th[data-sort]');
    tableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const column = this.dataset.sort;
            const isAsc = this.classList.contains('sort-asc');
            
            // Clear other sort classes
            tableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Toggle sort direction
            this.classList.toggle('sort-asc', !isAsc);
            this.classList.toggle('sort-desc', isAsc);
            
            // Sort table
            sortTable(table, column, isAsc);
        });
    });
    
    function sortTable(table, column, isAsc) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.querySelector(`td:nth-child(${getColumnIndex(column)})`).textContent;
            const bValue = b.querySelector(`td:nth-child(${getColumnIndex(column)})`).textContent;
            
            // Try to parse as number
            const aNum = parseFloat(aValue);
            const bNum = parseFloat(bValue);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAsc ? bNum - aNum : aNum - bNum;
            }
            
            // Otherwise sort as string
            return isAsc ? bValue.localeCompare(aValue) : aValue.localeCompare(bValue);
        });
        
        // Re-append rows in sorted order
        rows.forEach(row => tbody.appendChild(row));
    }
    
    function getColumnIndex(columnName) {
        const headers = document.querySelectorAll('.table th');
        for (let i = 0; i < headers.length; i++) {
            if (headers[i].dataset.sort === columnName) {
                return i + 1;
            }
        }
        return 1;
    }
    
    // ===== PROGRESS BAR ANIMATION =====
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const percentage = parseFloat(bar.textContent);
        if (!isNaN(percentage)) {
            // Set color based on percentage
            if (percentage >= 75) bar.style.background = 'linear-gradient(135deg, #4cc9f0, #4895ef)';
            else if (percentage >= 60) bar.style.background = 'linear-gradient(135deg, #4361ee, #3a56d4)';
            else if (percentage >= 40) bar.style.background = 'linear-gradient(135deg, #f8961e, #f3722c)';
            else bar.style.background = 'linear-gradient(135deg, #f72585, #e63946)';
        }
    });
    
    // ===== AUTO-HIDE ALERTS =====
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateX(-20px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // ===== CONFIRM DELETE =====
    const deleteButtons = document.querySelectorAll('.btn-delete, a[href*="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // ===== PRINT FUNCTIONALITY =====
    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // ===== EXPORT FUNCTIONALITY =====
    const exportButtons = document.querySelectorAll('.btn-export');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const table = document.querySelector('.table');
            if (table) {
                exportTableToCSV(table, 'student_data.csv');
            }
        });
    });
    
    function exportTableToCSV(table, filename) {
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        rows.forEach(row => {
            const rowData = [];
            const cols = row.querySelectorAll('th, td');
            
            cols.forEach(col => {
                rowData.push('"' + col.textContent.replace(/"/g, '""') + '"');
            });
            
            csv.push(rowData.join(','));
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
    
    // ===== CHARTS INITIALIZATION =====
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // ===== RESPONSIVE TABLE =====
    makeTableResponsive();
});

function initializeCharts() {
    // Class Performance Chart
    const classChartEl = document.getElementById('classPerformanceChart');
    if (classChartEl) {
        const ctx = classChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Class 9', 'Class 10', 'Class 11', 'Class 12'],
                datasets: [{
                    label: 'Average Percentage',
                    data: [75, 82, 68, 90],
                    backgroundColor: 'rgba(67, 97, 238, 0.7)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    }
                }
            }
        });
    }
    
    // Subject Performance Chart
    const subjectChartEl = document.getElementById('subjectPerformanceChart');
    if (subjectChartEl) {
        const ctx = subjectChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Mathematics', 'Science', 'English', 'Social Studies', 'Computer'],
                datasets: [{
                    data: [85, 78, 92, 75, 88],
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.8)',
                        'rgba(114, 9, 183, 0.8)',
                        'rgba(76, 201, 240, 0.8)',
                        'rgba(248, 150, 30, 0.8)',
                        'rgba(247, 37, 133, 0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

function makeTableResponsive() {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        wrapper.style.overflowX = 'auto';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
}

// Window resize handler
window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('active');
            document.body.classList.remove('menu-open');
        }
    }
});