/**
 * LEAVE RMS - Login Frontend JavaScript
 *
 * Handles user authentication, form validation, and login processing
 * for the LEAVE RMS system.
 *
 * @author System Administrator
 * @version 2.0
 */

// =============================================================================
// INITIALIZATION & CONFIGURATION
// =============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // DOM element references
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');
    
    // Initialize event listeners
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // =============================================================================
    // LOGIN HANDLERS
    // =============================================================================
    
    /**
     * Handles login form submission
     * @param {Event} event - Form submission event
     */
    function handleLogin(event) {
        event.preventDefault();
        
        // Get form data
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Validate form data
        if (!username || !password) {
            showError('Please enter both username and password');
            return;
        }
        
        // Clear previous error messages
        clearError();
        
        // Authenticate with PHP API
        loginWithPHPAPI(username, password)
            .then(success => {
                if (success) {
                    // Redirect to main application
                    window.location.href = 'index.html';
                }
            })
            .catch(error => {
                let errorMsg = 'Login failed. Please check your credentials and try again.';
                if (typeof error === 'object' && error !== null) {
                    if (error.message) errorMsg = error.message;
                } else if (typeof error === 'string') {
                    errorMsg = error;
                    error = { message: error };
                }
                console.error('Login failed:', error);
                showError(errorMsg);
            });
    }
    
    // =============================================================================
    // API INTEGRATION
    // =============================================================================
    
    /**
     * Authenticates user with PHP API
     * @param {string} usernameOrEmail - Username or email
     * @param {string} password - User password
     * @returns {Promise<boolean>} Promise resolving to authentication success
     */
    function loginWithPHPAPI(usernameOrEmail, password) {
        return fetch('http://localhost/LEAVE_RMS/database/api.php?endpoint=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                username: usernameOrEmail, 
                password 
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Login failed');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Store user information and platform data
                localStorage.setItem('user', JSON.stringify({
                    username: usernameOrEmail,
                    ...(data.user || {}),
                    platforms: data.platforms || []
                }));
                
                // Store LMS subplatform notifications if available
                if (data.lms_subplatform_notifications && Array.isArray(data.lms_subplatform_notifications)) {
                    localStorage.setItem('lms_subplatform_notifications', JSON.stringify(data.lms_subplatform_notifications));
                }
                
                return true;
            } else {
                throw new Error(data.message || 'Login failed');
            }
        });
    }
    
    // =============================================================================
    // UI UTILITIES
    // =============================================================================
    
    /**
     * Displays error message to user
     * @param {string} message - Error message to display
     */
    function showError(message) {
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('hidden');
        }
    }
    
    /**
     * Clears error message display
     */
    function clearError() {
        if (errorMessage) {
            errorMessage.textContent = '';
            errorMessage.classList.add('hidden');
        }
    }
    

});