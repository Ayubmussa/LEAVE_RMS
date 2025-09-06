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
            .then(result => {
                if (result === 'admin') {
                    // Admin-only account (no user account). Go to admin panel.
                    window.location.href = 'admin-panel.html';
                } else if (result === true) {
                    // User account (may also be admin). Always go to user panel first.
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
     * Authenticates user with PHP API - checks both user and admin credentials
     * @param {string} usernameOrEmail - Username or email
     * @param {string} password - User password
     * @returns {Promise<boolean>} Promise resolving to authentication success
     */
    async function loginWithPHPAPI(usernameOrEmail, password) {
        try {
            // 1) Try regular user login first
            const userResponse = await fetch('http://localhost/LEAVE_RMS/database/api.php?endpoint=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    username: usernameOrEmail, 
                    password 
                })
            });

            if (userResponse.ok) {
                const userData = await userResponse.json();
                if (userData.success) {
                    // Store user session with role support (default to 'instructor' if not provided)
                    const resolvedRole = (userData.user && userData.user.role) ? userData.user.role : 'instructor';
                    localStorage.setItem('user', JSON.stringify({
                        username: usernameOrEmail,
                        ...(userData.user || {}),
                        role: resolvedRole,
                        platforms: userData.platforms || []
                    }));

                    // 2) In background, check if also admin to enable Admin Panel option
                    try {
                        const adminResponse = await fetch('http://localhost/LEAVE_RMS/database/admin_api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ username: usernameOrEmail, password })
                        });
                        if (adminResponse.ok) {
                            const adminData = await adminResponse.json();
                            if (adminData.success) {
                                localStorage.setItem('adminSession', JSON.stringify(adminData.admin));
                            } else {
                                localStorage.removeItem('adminSession');
                            }
                        }
                    } catch (_) {}

                    // Always go to user panel
                    return true;
                }
            }

            // 3) If user login failed, try admin-only login
            const adminOnlyResponse = await fetch('http://localhost/LEAVE_RMS/database/admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username: usernameOrEmail, password })
            });

            if (adminOnlyResponse.ok) {
                const adminData = await adminOnlyResponse.json();
                if (adminData.success) {
                    localStorage.setItem('adminSession', JSON.stringify(adminData.admin));
                    return 'admin'; // No user account; go to admin panel directly
                }
            }

            throw new Error('Login failed');
        } catch (error) {
            console.error('Authentication error:', error);
            throw new Error('Invalid credentials. Please check your username and password.');
        }
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