// Employee Management System - Main JavaScript with 3D Motion Effects

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    init3DBackground();
    initNavigation();
    initModals();
    initAnimations();
    initCharts();
    initSearch();
    initAIFeatures();
    initToastNotifications();
    initRealTimeUpdates();
});

// 3D Background Animation
function init3DBackground() {
    const container = document.querySelector('.bg-3d-container');
    if (!container) return;

    // Add mouse parallax effect
    document.addEventListener('mousemove', (e) => {
        const cubes = document.querySelectorAll('.bg-3d-cube');
        const mouseX = e.clientX / window.innerWidth - 0.5;
        const mouseY = e.clientY / window.innerHeight - 0.5;

        cubes.forEach((cube, index) => {
            const speed = (index + 1) * 0.5;
            const x = mouseX * speed * 20;
            const y = mouseY * speed * 20;
            cube.style.transform = `translate(${x}px, ${y}px) rotateX(${45 + mouseY * 10}deg) rotateY(${45 + mouseX * 10}deg)`;
        });
    });
}

// Navigation
function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';

    navItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && href.includes(currentPage)) {
            item.classList.add('active');
        }

        // Add 3D hover effect
        item.addEventListener('mouseenter', function () {
            this.style.transform = 'translateX(10px) scale(1.02)';
        });

        item.addEventListener('mouseleave', function () {
            this.style.transform = 'translateX(0) scale(1)';
        });
    });

    // Mobile menu toggle injection
    let menuToggle = document.querySelector('.menu-toggle');
    const pageHeader = document.querySelector('.page-header');
    const sidebar = document.querySelector('.sidebar');
    const pageTitle = document.querySelector('.page-title');

    // Create a container for the title and toggle to flex align them correctly
    if (!menuToggle && pageHeader && pageTitle) {
        menuToggle = document.createElement('button');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        
        const titleContainer = document.createElement('div');
        titleContainer.style.display = 'flex';
        titleContainer.style.alignItems = 'center';
        
        pageHeader.insertBefore(titleContainer, pageTitle);
        titleContainer.appendChild(menuToggle);
        titleContainer.appendChild(pageTitle);
    }

    let sidebarOverlay = document.querySelector('.sidebar-overlay');
    if (!sidebarOverlay && sidebar) {
        sidebarOverlay = document.createElement('div');
        sidebarOverlay.className = 'sidebar-overlay';
        document.body.appendChild(sidebarOverlay);
    }

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('active');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
        });
    }

    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }
}

// Modal Functions
function initModals() {
    const modals = document.querySelectorAll('.modal-overlay');
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalCloses = document.querySelectorAll('.modal-close, [data-close-modal]');

    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal');
            openModal(modalId);
        });
    });

    modalCloses.forEach(close => {
        close.addEventListener('click', () => {
            const modal = close.closest('.modal-overlay');
            closeModal(modal.id);
        });
    });

    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // 3D entrance animation
        const modalContent = modal.querySelector('.modal');
        modalContent.style.animation = 'none';
        setTimeout(() => {
            modalContent.style.animation = 'modalEntrance 0.4s ease-out';
        }, 10);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Scroll Animations
function initAnimations() {
    const animatedElements = document.querySelectorAll('.stat-card, .content-card, .ai-feature-card, .employee-card-3d');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    animatedElements.forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
}

// Charts with 3D Effect
function initCharts() {
    // Attendance Chart
    const attendanceChart = document.getElementById('attendanceChart');
    if (attendanceChart) {
        renderAttendanceChart(attendanceChart);
    }

    // Department Chart
    const departmentChart = document.getElementById('departmentChart');
    if (departmentChart) {
        renderDepartmentChart(departmentChart);
    }
}

function renderAttendanceChart(container) {
    const ctx = container.getContext('2d');
    const chartData = window.dashboardData?.attendance || { labels: [], present: [], absent: [] };

    const data = {
        labels: chartData.labels,
        datasets: [{
            label: 'Present',
            data: chartData.present,
            backgroundColor: 'rgba(14, 165, 233, 0.8)',
            borderColor: '#0ea5e9',
            borderWidth: 2
        }, {
            label: 'Absent',
            data: chartData.absent,
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderColor: '#ef4444',
            borderWidth: 2
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(14, 165, 233, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
}

function renderDepartmentChart(container) {
    const ctx = container.getContext('2d');
    const chartData = window.dashboardData?.departments || { labels: [], data: [] };

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: [
                    '#0ea5e9',
                    '#38bdf8',
                    '#22d3ee',
                    '#10b981',
                    '#8b5cf6',
                    '#f59e0b',
                    '#ec4899',
                    '#f43f5e'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'right'
                }
            },
            animation: {
                animateRotate: true,
                duration: 2000
            }
        }
    });
}


// Search Functionality
function initSearch() {
    const searchInputs = document.querySelectorAll('.search-input');

    searchInputs.forEach(input => {
        let debounceTimer;

        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(e.target.value);
            }, 300);
        });
    });
}

function performSearch(query) {
    const tableRows = document.querySelectorAll('.data-table tbody tr');

    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(query.toLowerCase());

        if (match) {
            row.style.display = '';
            row.style.animation = 'fadeIn 0.3s ease-out';
        } else {
            row.style.display = 'none';
        }
    });
}

// AI Features
function initAIFeatures() {
    // Sentiment Analysis
    const sentimentBtn = document.getElementById('analyzeSentiment');
    if (sentimentBtn) {
        sentimentBtn.addEventListener('click', analyzeSentiment);
    }

    // Performance Prediction
    const predictBtn = document.getElementById('predictPerformance');
    if (predictBtn) {
        predictBtn.addEventListener('click', predictPerformance);
    }

    // Generate Job Description
    const jobDescBtn = document.getElementById('generateJobDesc');
    if (jobDescBtn) {
        jobDescBtn.addEventListener('click', generateJobDescription);
    }

    // Team Dynamics Analysis
    const teamBtn = document.getElementById('analyzeTeam');
    if (teamBtn) {
        teamBtn.addEventListener('click', analyzeTeamDynamics);
    }
}

async function analyzeSentiment() {
    const feedback = document.getElementById('feedbackText')?.value;
    if (!feedback) {
        showToast('Please enter feedback text', 'warning');
        return;
    }

    showLoading('Analyzing sentiment...');

    try {
        const response = await fetch('api/ai_sentiment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ feedback })
        });

        const result = await response.json();
        displayAIResult(result, 'sentiment');
    } catch (error) {
        showToast('Error analyzing sentiment', 'error');
    } finally {
        hideLoading();
    }
}

async function predictPerformance() {
    const employeeId = document.getElementById('predictEmployeeId')?.value;
    if (!employeeId) {
        showToast('Please select an employee', 'warning');
        return;
    }

    showLoading('Predicting performance...');

    try {
        const response = await fetch('api/ai_predict.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ employee_id: employeeId })
        });

        const result = await response.json();
        displayAIResult(result, 'prediction');
    } catch (error) {
        showToast('Error predicting performance', 'error');
    } finally {
        hideLoading();
    }
}

async function generateJobDescription() {
    const role = document.getElementById('jobRole')?.value;
    const requirements = document.getElementById('jobRequirements')?.value;

    if (!role || !requirements) {
        showToast('Please fill in all fields', 'warning');
        return;
    }

    showLoading('Generating job description...');

    try {
        const response = await fetch('api/ai_jobdesc.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role, requirements })
        });

        const result = await response.json();
        displayAIResult(result, 'jobdesc');
    } catch (error) {
        showToast('Error generating job description', 'error');
    } finally {
        hideLoading();
    }
}

async function analyzeTeamDynamics() {
    const departmentId = document.getElementById('teamDepartment')?.value;

    showLoading('Analyzing team dynamics...');

    try {
        const response = await fetch('api/ai_team.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ department_id: departmentId })
        });

        const result = await response.json();
        displayAIResult(result, 'team');
    } catch (error) {
        showToast('Error analyzing team dynamics', 'error');
    } finally {
        hideLoading();
    }
}

function displayAIResult(result, type) {
    const resultContainer = document.getElementById('aiResult');
    if (!resultContainer) return;

    let html = '';

    switch (type) {
        case 'sentiment':
            html = `
                <div class="ai-result">
                    <h4>Sentiment Analysis Result</h4>
                    <div class="sentiment-score">
                        <div class="score-circle" style="--score: ${result.score}%"></div>
                        <span>Positivity: ${result.score}%</span>
                    </div>
                    <p>${result.analysis}</p>
                    <div class="suggestions">
                        <h5>Suggestions:</h5>
                        <ul>${result.suggestions.map(s => `<li>${s}</li>`).join('')}</ul>
                    </div>
                </div>
            `;
            break;

        case 'prediction':
            html = `
                <div class="ai-result">
                    <h4>Performance Prediction</h4>
                    <div class="prediction-chart">
                        <canvas id="predictionChart"></canvas>
                    </div>
                    <p>${result.prediction}</p>
                </div>
            `;
            break;

        case 'jobdesc':
            html = `
                <div class="ai-result">
                    <h4>Generated Job Description</h4>
                    <div class="job-desc-content">
                        ${result.job_description}
                    </div>
                    <button class="btn btn-primary" onclick="copyJobDesc()">Copy to Clipboard</button>
                </div>
            `;
            break;

        case 'team':
            html = `
                <div class="ai-result">
                    <h4>Team Dynamics Analysis</h4>
                    <div class="team-metrics">
                        ${result.metrics.map(m => `
                            <div class="metric">
                                <span class="metric-name">${m.name}</span>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${m.value}%"></div>
                                </div>
                                <span class="metric-value">${m.value}%</span>
                            </div>
                        `).join('')}
                    </div>
                    <p>${result.analysis}</p>
                </div>
            `;
            break;
    }

    resultContainer.innerHTML = html;
    resultContainer.classList.add('fade-in');
}

// Toast Notifications
function initToastNotifications() {
    // Check for session messages
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
}

function showToast(message, type = 'info') {
    const container = document.querySelector('.toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas ${getToastIcon(type)}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toastSlide 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

function getToastIcon(type) {
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    return icons[type] || icons.info;
}

// Loading Overlay
function showLoading(message = 'Loading...') {
    let overlay = document.querySelector('.loading-overlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>${message}</p>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    overlay.classList.add('active');
}

function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Real-time Updates
function initRealTimeUpdates() {
    // Update clock
    updateClock();
    setInterval(updateClock, 1000);

    // Simulate real-time notifications
    setInterval(checkNotifications, 30000);

    // Update attendance status
    updateAttendanceStatus();
}

function updateClock() {
    const clockElements = document.querySelectorAll('.live-clock');
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    clockElements.forEach(el => {
        el.textContent = timeString;
    });
}

async function checkNotifications() {
    try {
        const response = await fetch('api/check_notifications.php');
        const notifications = await response.json();

        notifications.forEach(notification => {
            showToast(notification.message, notification.type);
        });

        updateNotificationBadge(notifications.length);
    } catch (error) {
        console.error('Error checking notifications:', error);
    }
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function updateAttendanceStatus() {
    const statusElements = document.querySelectorAll('.attendance-status');
    const now = new Date();
    const hour = now.getHours();

    let status = 'present';
    if (hour < 9) status = 'not-checked-in';
    else if (hour >= 18) status = 'checked-out';

    statusElements.forEach(el => {
        el.setAttribute('data-status', status);
    });
}

// Employee Card 3D Effect
function initEmployeeCards() {
    const cards = document.querySelectorAll('.employee-card-3d');

    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
    });
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;

            // Add shake animation
            field.style.animation = 'shake 0.5s ease-out';
            setTimeout(() => {
                field.style.animation = '';
            }, 500);
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

// Data Export
function exportToCSV(data, filename) {
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', filename);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function convertToCSV(data) {
    const headers = Object.keys(data[0]);
    const rows = data.map(obj => headers.map(header => obj[header]).join(','));
    return [headers.join(','), ...rows].join('\n');
}

// Print Function
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Print</title>
                <link rel="stylesheet" href="assets/css/style.css">
            </head>
            <body>
                ${element.innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Add CSS animations dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes modalEntrance {
        0% {
            opacity: 0;
            transform: scale(0.9) translateY(20px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .loading-content {
        text-align: center;
    }
    
    .loading-content p {
        margin-top: 15px;
        color: var(--text-primary);
        font-weight: 500;
    }
    
    .form-input.error {
        border-color: var(--danger-color);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
    }
`;
document.head.appendChild(style);
