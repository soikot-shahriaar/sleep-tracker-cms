/**
 * Sleep Form JavaScript
 * Sleep Tracker CMS
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeSleepForm();
});

/**
 * Initialize sleep form functionality
 */
function initializeSleepForm() {
    const bedtimeInput = document.getElementById('bedtime');
    const wakeTimeInput = document.getElementById('wake_time');
    const form = document.querySelector('.form');
    
    if (bedtimeInput && wakeTimeInput) {
        // Add real-time duration calculation
        bedtimeInput.addEventListener('change', calculateDuration);
        wakeTimeInput.addEventListener('change', calculateDuration);
        
        // Add input validation
        bedtimeInput.addEventListener('blur', validateTime);
        wakeTimeInput.addEventListener('blur', validateTime);
        
        // Calculate initial duration if both fields have values
        if (bedtimeInput.value && wakeTimeInput.value) {
            calculateDuration();
        }
    }
    
    if (form) {
        // Add form submission handling
        form.addEventListener('submit', handleFormSubmission);
        
        // Add auto-save functionality (optional)
        addAutoSave();
    }
    
    // Add sleep quality helper
    addSleepQualityHelper();
    
    // Add time input enhancements
    enhanceTimeInputs();
}

/**
 * Calculate and display sleep duration
 */
function calculateDuration() {
    const bedtimeInput = document.getElementById('bedtime');
    const wakeTimeInput = document.getElementById('wake_time');
    
    if (!bedtimeInput.value || !wakeTimeInput.value) {
        removeDurationDisplay();
        return;
    }
    
    const bedtime = bedtimeInput.value;
    const wakeTime = wakeTimeInput.value;
    
    // Calculate duration
    const duration = calculateSleepDuration(bedtime, wakeTime);
    
    // Display duration
    displayDuration(duration);
    
    // Validate duration
    validateDuration(duration);
}

/**
 * Calculate sleep duration between two times
 */
function calculateSleepDuration(bedtime, wakeTime) {
    const bedtimeDate = new Date(`2000-01-01 ${bedtime}:00`);
    let wakeTimeDate = new Date(`2000-01-01 ${wakeTime}:00`);
    
    // If wake time is earlier than bedtime, assume it's the next day
    if (wakeTimeDate < bedtimeDate) {
        wakeTimeDate = new Date(`2000-01-02 ${wakeTime}:00`);
    }
    
    const durationMs = wakeTimeDate - bedtimeDate;
    const durationHours = durationMs / (1000 * 60 * 60);
    
    return Math.round(durationHours * 100) / 100; // Round to 2 decimal places
}

/**
 * Display calculated duration
 */
function displayDuration(duration) {
    // Remove existing duration display
    removeDurationDisplay();
    
    // Create duration display element
    const durationDisplay = document.createElement('div');
    durationDisplay.id = 'duration-display';
    durationDisplay.className = 'duration-display';
    
    const hours = Math.floor(duration);
    const minutes = Math.round((duration - hours) * 60);
    
    let durationText = `${hours}h ${minutes.toString().padStart(2, '0')}m`;
    let className = 'duration-normal';
    let icon = '😴';
    
    // Add duration feedback
    if (duration < 6) {
        className = 'duration-short';
        icon = '😪';
        durationText += ' (Too short)';
    } else if (duration > 10) {
        className = 'duration-long';
        icon = '😴';
        durationText += ' (Very long)';
    } else if (duration >= 7 && duration <= 9) {
        className = 'duration-optimal';
        icon = '✨';
        durationText += ' (Optimal)';
    }
    
    durationDisplay.innerHTML = `
        <div class="duration-info ${className}">
            <span class="duration-icon">${icon}</span>
            <span class="duration-text">Sleep Duration: <strong>${durationText}</strong></span>
        </div>
    `;
    
    // Insert after wake time input
    const wakeTimeGroup = document.getElementById('wake_time').closest('.form-group');
    wakeTimeGroup.parentNode.insertBefore(durationDisplay, wakeTimeGroup.nextSibling);
}

/**
 * Remove duration display
 */
function removeDurationDisplay() {
    const existingDisplay = document.getElementById('duration-display');
    if (existingDisplay) {
        existingDisplay.remove();
    }
}

/**
 * Validate sleep duration
 */
function validateDuration(duration) {
    const errorContainer = document.querySelector('.alert.alert-error');
    
    if (duration < 0.5 || duration > 24) {
        if (!errorContainer) {
            showDurationError('Sleep duration must be between 0.5 and 24 hours');
        }
        return false;
    } else {
        // Remove error if it exists
        if (errorContainer && errorContainer.textContent.includes('Sleep duration')) {
            errorContainer.remove();
        }
        return true;
    }
}

/**
 * Show duration error
 */
function showDurationError(message) {
    const form = document.querySelector('.form');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error';
    errorDiv.textContent = message;
    
    form.insertBefore(errorDiv, form.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

/**
 * Validate time input
 */
function validateTime(event) {
    const input = event.target;
    const value = input.value;
    
    if (value && !isValidTime(value)) {
        input.setCustomValidity('Please enter a valid time in HH:MM format');
        input.reportValidity();
    } else {
        input.setCustomValidity('');
    }
}

/**
 * Check if time is valid
 */
function isValidTime(time) {
    const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
    return timeRegex.test(time);
}

/**
 * Handle form submission
 */
function handleFormSubmission(event) {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Add loading state
    if (submitButton) {
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="loading"></span> Saving...';
        
        // Restore button after 3 seconds (in case of error)
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }, 3000);
    }
    
    // Validate duration one more time
    const bedtime = document.getElementById('bedtime').value;
    const wakeTime = document.getElementById('wake_time').value;
    
    if (bedtime && wakeTime) {
        const duration = calculateSleepDuration(bedtime, wakeTime);
        if (!validateDuration(duration)) {
            event.preventDefault();
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    }
}

/**
 * Add sleep quality helper
 */
function addSleepQualityHelper() {
    const qualitySelect = document.getElementById('sleep_quality');
    
    if (!qualitySelect) return;
    
    // Add quality descriptions
    const qualityDescriptions = {
        'poor': 'Restless sleep, frequent awakenings, feeling tired',
        'fair': 'Some interruptions, moderate rest',
        'good': 'Mostly restful sleep with minor interruptions',
        'excellent': 'Deep, uninterrupted sleep, feeling refreshed'
    };
    
    qualitySelect.addEventListener('change', function() {
        const selectedValue = this.value;
        const description = qualityDescriptions[selectedValue];
        
        // Remove existing helper
        const existingHelper = document.getElementById('quality-helper');
        if (existingHelper) {
            existingHelper.remove();
        }
        
        if (description) {
            const helper = document.createElement('div');
            helper.id = 'quality-helper';
            helper.className = 'quality-helper';
            helper.innerHTML = `<small>${description}</small>`;
            
            this.parentNode.appendChild(helper);
        }
    });
}

/**
 * Enhance time inputs
 */
function enhanceTimeInputs() {
    const timeInputs = document.querySelectorAll('input[type="time"]');
    
    timeInputs.forEach(input => {
        // Add placeholder for better UX
        input.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary-color)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = 'var(--border-color)';
        });
        
        // Add quick time buttons
        addQuickTimeButtons(input);
    });
}

/**
 * Add quick time selection buttons
 */
function addQuickTimeButtons(input) {
    if (input.id === 'bedtime') {
        const quickTimes = ['22:00', '22:30', '23:00', '23:30', '00:00'];
        addTimeButtons(input, quickTimes, 'Common bedtimes');
    } else if (input.id === 'wake_time') {
        const quickTimes = ['06:00', '06:30', '07:00', '07:30', '08:00'];
        addTimeButtons(input, quickTimes, 'Common wake times');
    }
}

/**
 * Add time selection buttons
 */
function addTimeButtons(input, times, label) {
    const container = document.createElement('div');
    container.className = 'quick-time-buttons';
    container.innerHTML = `<small>${label}:</small>`;
    
    times.forEach(time => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-small btn-secondary quick-time-btn';
        button.textContent = time;
        button.addEventListener('click', function() {
            input.value = time;
            calculateDuration();
            input.focus();
        });
        container.appendChild(button);
    });
    
    input.parentNode.appendChild(container);
}

/**
 * Add auto-save functionality
 */
function addAutoSave() {
    const form = document.querySelector('.form');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            // Save to localStorage
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            localStorage.setItem('sleep-form-draft', JSON.stringify(data));
            
            // Show auto-save indicator
            showAutoSaveIndicator();
        });
    });
    
    // Load saved data on page load
    loadAutoSavedData();
}

/**
 * Load auto-saved data
 */
function loadAutoSavedData() {
    const savedData = localStorage.getItem('sleep-form-draft');
    
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            const form = document.querySelector('.form');
            
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input && !input.value) {
                    input.value = data[key];
                }
            });
            
            // Calculate duration if times are loaded
            calculateDuration();
        } catch (error) {
            console.error('Error loading auto-saved data:', error);
        }
    }
}

/**
 * Show auto-save indicator
 */
function showAutoSaveIndicator() {
    // Remove existing indicator
    const existing = document.getElementById('autosave-indicator');
    if (existing) {
        existing.remove();
    }
    
    const indicator = document.createElement('div');
    indicator.id = 'autosave-indicator';
    indicator.className = 'autosave-indicator';
    indicator.textContent = 'Draft saved';
    
    document.body.appendChild(indicator);
    
    // Auto-hide after 2 seconds
    setTimeout(() => {
        indicator.remove();
    }, 2000);
}

// Add CSS for new elements
const style = document.createElement('style');
style.textContent = `
    .duration-display {
        margin: 1rem 0;
        padding: 1rem;
        border-radius: var(--radius-md);
        background: var(--bg-secondary);
        border-left: 4px solid var(--primary-color);
    }
    
    .duration-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .duration-icon {
        font-size: 1.5rem;
    }
    
    .duration-optimal {
        border-left-color: var(--success-color);
        background: rgba(76, 175, 80, 0.1);
    }
    
    .duration-short {
        border-left-color: var(--warning-color);
        background: rgba(255, 152, 0, 0.1);
    }
    
    .duration-long {
        border-left-color: var(--info-color);
        background: rgba(33, 150, 243, 0.1);
    }
    
    .quality-helper {
        margin-top: 0.5rem;
    }
    
    .quality-helper small {
        color: var(--text-secondary);
        font-style: italic;
    }
    
    .quick-time-buttons {
        margin-top: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        align-items: center;
    }
    
    .quick-time-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .autosave-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--success-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @media (max-width: 768px) {
        .quick-time-buttons {
            justify-content: center;
        }
        
        .duration-info {
            flex-direction: column;
            text-align: center;
            gap: 0.25rem;
        }
    }
`;
document.head.appendChild(style);

