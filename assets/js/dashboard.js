/**
 * Dashboard JavaScript
 * Sleep Tracker CMS
 */

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
});

/**
 * Load dashboard statistics via API
 */
async function loadDashboardStats() {
    try {
        const response = await fetch('api/dashboard-stats.php');
        const result = await response.json();
        
        if (result.success) {
            updateDashboardStats(result.data);
            loadRecentRecords(result.data.recent_records);
        } else {
            console.error('Failed to load dashboard stats:', result.error);
            showError('Failed to load dashboard statistics');
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
        showError('Error loading dashboard statistics');
    }
}

/**
 * Update dashboard statistics display
 */
function updateDashboardStats(data) {
    // Update stat numbers
    updateElement('total-records', data.total_records);
    updateElement('avg-sleep', data.avg_sleep);
    updateElement('week-avg', data.week_avg);
    updateElement('avg-quality', data.avg_quality);
    
    // Add fade-in animation
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
}

/**
 * Load recent sleep records
 */
function loadRecentRecords(records) {
    const container = document.getElementById('recent-records');
    
    if (!container) return;
    
    if (records.length === 0) {
        container.innerHTML = '<p>No recent sleep records found. <a href="add-sleep.php">Add your first record</a>.</p>';
        return;
    }
    
    let html = '<div class="recent-records-list">';
    
    records.forEach(record => {
        html += `
            <div class="recent-record-item">
                <div class="record-date">${record.sleep_date}</div>
                <div class="record-details">
                    <span class="record-duration">${record.duration}</span>
                    <span class="quality-badge ${record.quality_class}">${record.quality}</span>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    html += '<div class="recent-actions"><a href="sleep-log.php" class="btn btn-secondary">View All Records</a></div>';
    
    container.innerHTML = html;
}

/**
 * Update element content with animation
 */
function updateElement(id, content) {
    const element = document.getElementById(id);
    if (element) {
        // Add loading animation
        element.innerHTML = '<span class="loading"></span>';
        
        // Update content after short delay
        setTimeout(() => {
            element.textContent = content;
            element.classList.add('fade-in');
        }, 300);
    }
}

/**
 * Show error message
 */
function showError(message) {
    const container = document.querySelector('.dashboard');
    if (container) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error';
        errorDiv.textContent = message;
        container.insertBefore(errorDiv, container.firstChild);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
}

/**
 * Format duration for display
 */
function formatDuration(hours) {
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    return `${h}h ${m.toString().padStart(2, '0')}m`;
}

/**
 * Add hover effects to stat cards
 */
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 15px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-4px)';
            this.style.boxShadow = '0 10px 15px rgba(0, 0, 0, 0.1)';
        });
    });
});

/**
 * Add click animations to buttons
 */
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Add CSS for ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .recent-records-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .recent-record-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: var(--bg-secondary);
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
    }
    
    .recent-record-item:hover {
        background: var(--bg-tertiary);
        transform: translateX(4px);
    }
    
    .record-date {
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .record-details {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .record-duration {
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .recent-actions {
        margin-top: 1rem;
        text-align: center;
    }
`;
document.head.appendChild(style);

