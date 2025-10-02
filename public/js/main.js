/**
 * LEAVE RMS - Main Frontend JavaScript
 *
 * Handles user interface, platform integration, authentication, notifications,
 * and theme management for the LEAVE RMS system.
 *
 * @author System Administrator
 * @version 2.0
 */

// =============================================================================
// CONSTANTS & CONFIGURATION
// =============================================================================

const API_BASE_URL = 'https://global.fnlsrv.website/LEAVE_RMS/database/api.php';
const SIS_URL = 'https://sis.final.edu.tr/';

// =============================================================================
// NOTIFICATION SYSTEM
// =============================================================================

/**
 * Shows a custom notification message
 * @param {string} message - The message to display
 * @param {string} type - The type of notification ('success', 'error', 'warning', 'info')
 */
function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existingNotification = document.querySelector('.custom-notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `custom-notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;

    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : type === 'warning' ? '#fff3cd' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : type === 'warning' ? '#856404' : '#0c5460'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : type === 'warning' ? '#ffeaa7' : '#bee5eb'};
        border-radius: 4px;
        padding: 15px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }
        .notification-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            padding: 0;
            line-height: 1;
        }
        .notification-close:hover {
            opacity: 0.7;
        }
    `;
    document.head.appendChild(style);

    // Add close functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.remove();
    });

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);

    // Add to page
    document.body.appendChild(notification);
}

// =============================================================================
// INITIALIZATION
// =============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // DOM element references
    const elements = {
        loginSection: document.getElementById('login-section'),
        sectionsContainer: document.getElementById('sections-container'),
        platformsSection: document.getElementById('platforms-section'),
        announcementsSection: document.getElementById('announcements-section'),
        diningMenuSection: document.getElementById('dining-menu-section'),
        usernameElement: document.getElementById('username'),
        logoutBtn: document.getElementById('logout-btn'),
        darkModeBtn: document.getElementById('dark-mode-btn'),
        currentThemeText: document.getElementById('current-theme'),
        userDropdownBtn: document.getElementById('user-dropdown-btn'),
        userDropdown: document.querySelector('.user-dropdown'),
        notificationList: document.getElementById('notification-list'),
        platformsContainer: document.querySelector('.platforms-container'),
        announcementsContainer: document.querySelector('.announcements-container'),
        announcementsSlider: document.getElementById('announcements-slider'),
        announcementsDots: document.getElementById('announcements-dots'),
        announcementsPrevBtn: document.getElementById('announcements-prev-btn'),
        announcementsNextBtn: document.getElementById('announcements-next-btn'),
        diningMenuContainer: document.querySelector('.dining-menu-container'),
        diningMenuSlider: document.getElementById('dining-menu-slider'),
        diningMenuDots: document.getElementById('dining-menu-dots'),
        diningPrevBtn: document.getElementById('dining-prev-btn'),
        diningNextBtn: document.getElementById('dining-next-btn'),
        notificationsSection: document.getElementById('notification-section'),
        notificationBtn: document.getElementById('notification-btn'),
        notificationDropdown: document.getElementById('notification-dropdown-content'),
        notificationBadge: document.getElementById('notification-badge')
    };

    // Dining menu slider state
    let diningMenuState = {
        currentSlide: 0,
        totalSlides: 0,
        slides: [],
        touchStartX: 0,
        touchEndX: 0
    };

    // Announcements slider state
    let announcementsState = {
        currentSlide: 0,
        totalSlides: 0,
        slides: [],
        touchStartX: 0,
        touchEndX: 0
    };

    // Initialize application state
    initializeApplication();

    // =============================================================================
    // EVENT LISTENERS
    // =============================================================================

    setupEventListeners();

    // =============================================================================
    // INITIALIZATION FUNCTIONS
    // =============================================================================

    /**
     * Initializes the application
     */
    function initializeApplication() {
        checkAuthStatus();
        initTheme();
    }

    /**
     * Sets up all event listeners
     */
    function setupEventListeners() {
        setupLogoutHandler();
        setupDarkModeHandler();
        setupPlatformAccessHandler();
        setupUserDropdownHandler();
        setupLanguageChangeHandler();
        setupNotificationDropdownHandler();
        setupDiningMenuSlider();
        setupAnnouncementsSlider();
    }

    /**
     * Sets up logout button handler
     */
    function setupLogoutHandler() {
        if (elements.logoutBtn) {
            elements.logoutBtn.onclick = function(e) {
                e.preventDefault();
                handleLogout();
            };
        }
    }

    /**
     * Sets up dark mode toggle handler
     */
    function setupDarkModeHandler() {
        if (elements.darkModeBtn) {
            elements.darkModeBtn.onclick = function(e) {
                e.preventDefault();
                toggleDarkMode();
            };
        }
    }

    /**
     * Sets up platform access button handler (event delegation for dynamic content)
     */
    function setupPlatformAccessHandler() {
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('access-platform-btn')) {
                handlePlatformAccess(e);
            }
        });
    }

    /**
     * Sets up user dropdown functionality
     */
    function setupUserDropdownHandler() {
        if (elements.userDropdownBtn && elements.userDropdown) {
            elements.userDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                elements.userDropdown.classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!elements.userDropdown.contains(e.target)) {
                    elements.userDropdown.classList.remove('show');
                }
            });
        }
    }

    /**
     * Sets up language change event listener
     */
    function setupLanguageChangeHandler() {
        window.addEventListener('languageChanged', function() {
            console.log('Language change event detected in main.js');
            updateGreeting();
            
            // Load notifications only for non-student roles and toggle UI
            const user = getUserFromStorage();
            const userRole = (user && user.role) ? String(user.role).toLowerCase().trim() : 'instructor';
            const isStudent = userRole === 'student';
            if (!isStudent) {
            loadNotifications();
                updateNotificationHeader();
            }
            const notifEls = [
                elements.notificationsSection,
                elements.notificationBtn,
                elements.notificationDropdown,
                elements.notificationBadge
            ];
            notifEls.forEach(el => {
                if (!el) return;
                if (isStudent) {
                    el.classList.add('hidden');
                    if (el.id === 'notification-dropdown-content') {
                        el.classList.remove('show');
                    }
                } else {
                    el.classList.remove('hidden');
                }
            });
            
            loadPlatforms();
            loadAnnouncements();
            loadDiningMenu();
            console.log('Language change handling complete in main.js');
        });
    }

    /**
     * Sets up notification dropdown handler
     */
    function setupNotificationDropdownHandler() {
        console.log('Setting up notification dropdown handler');
        console.log('Notification button:', elements.notificationBtn);
        console.log('Notification dropdown:', elements.notificationDropdown);
        
        if (elements.notificationBtn && elements.notificationDropdown) {
            elements.notificationBtn.addEventListener('click', function(e) {
                console.log('Notification button clicked');
                e.stopPropagation();
                elements.notificationDropdown.classList.toggle('show');
                console.log('Dropdown show class:', elements.notificationDropdown.classList.contains('show'));
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!elements.notificationDropdown.contains(e.target) && !elements.notificationBtn.contains(e.target)) {
                    elements.notificationDropdown.classList.remove('show');
                }
            });
        } else {
            console.error('Notification elements not found');
        }
    }

    /**
     * Updates notification header text
     */
    function updateNotificationHeader() {
        const notifSection = document.getElementById('notification-section');
        if (notifSection) {
            const notifHeader = notifSection.querySelector('h2[data-translate="notifications"]');
            if (notifHeader) {
                console.log('Updating notification header text');
                notifHeader.textContent = getTranslation('notifications');
            }
        }
    }

    // =============================================================================
    // AUTHENTICATION & USER MANAGEMENT
    // =============================================================================

    /**
     * Handles user logout
     */
    function handleLogout() {
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = 'login.html';
    }

    /**
     * Checks authentication status and updates UI accordingly
     */
    function checkAuthStatus() {
        const user = getUserFromStorage();

        if (user) {
            showLoggedInState(user);
        } else {
            showLoggedOutState();
        }
        
        // Load platforms for all roles (students will see filtered platforms)
        loadPlatforms();
    }

    /**
     * Shows the logged-in state
     * @param {Object} user - User object
     */
    function showLoggedInState(user) {
        // Role-based gating for students (platforms to be applied later)
        const userRole = (user && user.role) ? user.role : 'instructor';
        const isStudent = userRole === 'student';
        
        elements.loginSection.classList.add('hidden');
        elements.sectionsContainer.classList.remove('hidden');
        
        // Add student-view class for styling adjustments
        if (isStudent) {
            elements.sectionsContainer.classList.add('student-view');
        } else {
            elements.sectionsContainer.classList.remove('student-view');
        }
        
        if (elements.usernameElement) {
            elements.usernameElement.textContent = `Welcome, ${user.username || 'User'}`;
        }
        
        if (elements.logoutBtn) {
            elements.logoutBtn.classList.remove('hidden');
        }

        // Load notifications only for non-student roles, announcements and dining menu for all
        if (!isStudent) {
        loadNotifications();
        }
        loadAnnouncements();
        // Only load dining menu for non-students
        if (!isStudent) {
            loadDiningMenu();
        }

        // Show platforms section for all roles (students will see filtered platforms)
        if (elements.platformsSection) {
            elements.platformsSection.classList.remove('hidden');
        }

        // Hide all notification UI for student role
        const notifEls = [
            elements.notificationsSection,
            elements.notificationBtn,
            elements.notificationDropdown,
            elements.notificationBadge
        ];
        notifEls.forEach(el => {
            if (!el) return;
            if (isStudent) {
                el.classList.add('hidden');
                if (el.id === 'notification-dropdown-content') {
                    el.classList.remove('show');
                }
            } else {
                el.classList.remove('hidden');
            }
        });
    }

    /**
     * Shows the logged-out state
     */
    function showLoggedOutState() {
        elements.loginSection.classList.remove('hidden');
        elements.sectionsContainer.classList.add('hidden');
        
        if (elements.logoutBtn) {
            elements.logoutBtn.classList.add('hidden');
        }
        
        if (elements.usernameElement) {
            elements.usernameElement.textContent = '';
        }
    }

    /**
     * Updates the greeting message
     */
    function updateGreeting() {
        const user = getUserFromStorage();
        if (elements.usernameElement && user) {
            elements.usernameElement.textContent = `${getTranslation('welcome-user')}${user.username || 'User'}`;
        }
    }

    /**
     * Gets user from localStorage
     * @returns {Object|null} User object or null
     */
    function getUserFromStorage() {
        return JSON.parse(localStorage.getItem('user') || 'null');
    }

    // =============================================================================
    // PLATFORM ACCESS HANDLERS
    // =============================================================================

    /**
     * Handles platform access button clicks
     * @param {Event} e - Click event
     */
    function handlePlatformAccess(e) {
        const platform = e.target.getAttribute('data-platform');
        const user = getUserFromStorage();
        
        if (!user || !user.username) {
            showNotification('Please log in first.', 'warning');
            return;
        }

        e.preventDefault();
        setButtonLoadingState(e.target, 'Opening...');

        const platformHandlers = {
            'RMS': () => handleRMSAccess(e.target, user),
            'Leave and Absence': () => handleLeavePortalAccess(e.target, user),
            'SIS': () => handleSISAccess(e.target),
            'LMS': () => handleLMSAccess(e.target)
        };

        const handler = platformHandlers[platform];
        if (handler) {
            handler();
        } else {
            console.warn('Unknown platform:', platform);
            resetButtonState(e.target);
        }
    }

    /**
     * Sets button to loading state
     * @param {HTMLElement} button - Button element
     * @param {string} text - Loading text
     */
    function setButtonLoadingState(button, text) {
        button.disabled = true;
        button.textContent = text;
    }

    /**
     * Resets button to normal state
     * @param {HTMLElement} button - Button element
     */
    function resetButtonState(button) {
        button.disabled = false;
        button.textContent = getTranslation('access-platform');
    }

    /**
     * Handles RMS platform access with bridge authentication
     * @param {HTMLElement} button - The clicked button
     * @param {Object} user - User object
     */
    function handleRMSAccess(button, user) {
        button.textContent = 'Authenticating...';
        const bridgeUrl = `rms_auth_bridge.php?username=${encodeURIComponent(user.username)}&type=dashboard`;
        window.open(bridgeUrl, '_blank');
        resetButtonState(button);
    }

    /**
     * Handles Leave Portal platform access with bridge authentication
     * @param {HTMLElement} button - The clicked button
     * @param {Object} user - User object
     */
    function handleLeavePortalAccess(button, user) {
        button.textContent = 'Opening...';
        // Redirect to login page as requested
        window.open('https://leave.final.digital/index.php', '_blank');
        resetButtonState(button);
    }

    /**
     * Handles SIS platform access - direct access only
     * @param {HTMLElement} button - The clicked button
     */
    function handleSISAccess(button) {
        // Direct access to SIS without authentication
        window.open(SIS_URL, '_blank');
        resetButtonState(button);
    }



    /**
     * Handles LMS platform access
     * @param {HTMLElement} button - The clicked button
     */
    function handleLMSAccess(button) {
        setButtonLoadingState(button, 'Loading...');
        
        fetch(`${API_BASE_URL}?endpoint=lms_subplatforms`)
            .then(res => res.json())
            .then(data => {
                if (data.success && Array.isArray(data.subplatforms)) {
                    showLmsModal(data.subplatforms);
                } else {
                    showNotification('Failed to load LMS sub-platforms.', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading LMS sub-platforms:', error);
                showNotification('Failed to load LMS sub-platforms.', 'error');
            })
            .finally(() => {
                resetButtonState(button);
            });
    }

    /**
     * Shows LMS modal with sub-platforms
     * @param {Array} subplatforms - Array of sub-platform objects
     */
    function showLmsModal(subplatforms) {
        const modal = document.getElementById('lms-modal');
        const list = document.getElementById('lms-subplatforms-list');
        
        if (!modal || !list) return;
        
        list.innerHTML = '';
        
        subplatforms.forEach(sp => {
            const div = document.createElement('div');
            div.className = 'lms-subplatform-card';
            div.innerHTML = `
                <span>${sp.name}</span> 
                <button class='btn btn-primary lms-access-btn' 
                        data-url='${sp.url}' 
                        data-login='${sp.login_endpoint}' 
                        data-notif='${sp.notifications_endpoint}'>
                    ${getTranslation('access-platform')}
                </button>
            `;
            list.appendChild(div);
        });
        
        // Show modal as flex for overlay centering
        modal.style.display = 'flex';
        
        // Setup close button
        setupModalCloseButton(modal);
        
        // Setup access buttons
        setupLmsAccessButtons(list);
    }

    /**
     * Sets up modal close button
     * @param {HTMLElement} modal - Modal element
     */
    function setupModalCloseButton(modal) {
        const closeBtn = document.getElementById('close-lms-modal');
        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            };
        }
    }

    /**
     * Sets up LMS access buttons
     * @param {HTMLElement} list - List container
     */
    function setupLmsAccessButtons(list) {
        list.querySelectorAll('.lms-access-btn').forEach(btn => {
            btn.onclick = function() {
                // Open the subplatform's real login page directly
                const baseUrl = this.dataset.url || '';
                const loginPath = this.dataset.login || '';
                try {
                    const absoluteUrl = loginPath
                        ? new URL(loginPath, baseUrl).toString()
                        : (baseUrl || null);
                    if (absoluteUrl) {
                        window.open(absoluteUrl, '_blank');
                    } else {
                        showNotification('Login URL not available for this sub-platform.', 'warning');
                    }
                } catch (e) {
                    console.error('Failed to build sub-platform URL', e);
                    showNotification('Invalid sub-platform URL.', 'error');
                }
            };
        });
    }

    /**
     * Handles LMS sub-platform authentication and notification fetching
     * @param {string} url - Base URL of the sub-platform
     * @param {string} loginEndpoint - Login endpoint path
     * @param {string} notifEndpoint - Notifications endpoint path
     * @param {HTMLElement} btn - The clicked button
     */
    function handleLmsSubplatformAccess(url, loginEndpoint, notifEndpoint, btn) {
        const user = getUserFromStorage();
        
        if (!user || !user.username) {
            showNotification('Please log in first.', 'warning');
            return;
        }
        
        setButtonLoadingState(btn, 'Opening...');
        
        const subplatformCard = btn.closest('.lms-subplatform-card');
        const subplatformName = subplatformCard ? subplatformCard.querySelector('span').textContent : 'Unknown';
        
        // Use server-side direct link for LMS (similar to RMS)
        const directLinkUrl = `${API_BASE_URL}?endpoint=lms_subplatform_direct_link&username=${encodeURIComponent(user.username)}&subplatform=${encodeURIComponent(subplatformName)}`;
        
        fetch(directLinkUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.url) {
                    window.open(data.url, '_blank');
                } else {
                    showNotification('Failed to access LMS: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error accessing LMS:', error);
                showNotification('Failed to access LMS. Please try again.', 'error');
            });
        
        // Reset button state
        setTimeout(() => {
            btn.disabled = false;
            btn.textContent = getTranslation('access-platform');
        }, 1000);
    }

    // =============================================================================
    // PLATFORM MANAGEMENT
    // =============================================================================

    /**
     * Loads platforms from the PHP API
     */
    function loadPlatforms() {
        if (!elements.platformsContainer) {
            console.error('Platforms container not found');
            return;
        }

        elements.platformsContainer.innerHTML = '';

        fetch(`${API_BASE_URL}?endpoint=platforms`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch platforms');
                return response.json();
            })
            .then(data => {
                console.log('Platforms data received:', data);
                if (data.success && data.platforms && data.platforms.length > 0) {
                    console.log('Creating platform cards for:', data.platforms.length, 'platforms');
                    
                    // Filter platforms based on user role
                    const user = getUserFromStorage();
                    const userRole = (user && user.role) ? user.role : 'instructor';
                    const isStudent = userRole === 'student';
                    
                    let filteredPlatforms = data.platforms;
                    if (isStudent) {
                        // Students only see Document Application System, Summer School Application, Accommodation Booking Portal, Support Center, Student Exam Registration, Exemption exam form, Resit Exams Application, SIS, and LMS
                        filteredPlatforms = data.platforms.filter(platform => 
                            platform.name === 'Document Application System' || 
                            platform.name === 'Summer School Application' ||
                            platform.name === 'Accommodation Booking Portal' ||
                            platform.name === 'Support Center' ||
                            platform.name === 'Student Exam Registration' ||
                            platform.name === 'Exemption exam form' ||
                            platform.name === 'Resit Exams Application' ||
                            platform.name === 'SIS' ||
                            platform.name === 'LMS'
                        );
                        console.log('Filtered platforms for student:', filteredPlatforms.length, 'platforms');
                    } else {
                        // Instructors and admins see all platforms EXCEPT student-specific ones
                        filteredPlatforms = data.platforms.filter(platform => 
                            platform.name !== 'Document Application System' && 
                            platform.name !== 'Summer School Application' &&
                            platform.name !== 'Accommodation Booking Portal' &&
                            platform.name !== 'Support Center' &&
                            platform.name !== 'Student Exam Registration' &&
                            platform.name !== 'Exemption exam form' &&
                            platform.name !== 'Resit Exams Application'
                        );
                        console.log('Filtered platforms for instructor/admin:', filteredPlatforms.length, 'platforms');
                    }
                    
                    if (filteredPlatforms.length > 0) {
                        filteredPlatforms.forEach(platform => {
                        createPlatformCard(platform, elements.platformsContainer);
                    });
                    } else {
                        elements.platformsContainer.innerHTML = '<p>No platforms available for your role.</p>';
                    }
                } else {
                    elements.platformsContainer.innerHTML = '<p>No platforms found.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading platforms:', error);
                elements.platformsContainer.innerHTML = '<p>Error loading platforms.</p>';
            });
    }

    /**
     * Creates a platform card element
     * @param {Object} platform - Platform data
     * @param {HTMLElement} container - Container to append the card to
     */
    function createPlatformCard(platform, container) {
        const platformCard = document.createElement('div');
        platformCard.className = 'platform-card';
        
        const { platformUrl, buttonClass, buttonText } = getPlatformButtonConfig(platform);
        
        // Check if user is a student
        const user = getUserFromStorage();
        const userRole = (user && user.role) ? user.role : 'instructor';
        const isStudent = userRole === 'student';
        
        // No direct access buttons (LMS and Leave both use main Access action)
        const directAccessButton = '';
        
        console.log('Creating platform card for:', platform.name);
        console.log('Is LMS platform?', platform.name === 'LMS');
        console.log('Is Leave and Absence platform?', platform.name === 'Leave and Absence');
        console.log('Direct access button HTML:', directAccessButton);
        console.log('Full platform object:', platform);
        
        platformCard.innerHTML = `
            <h3>${platform.name}</h3>
            <p>${platform.description}</p>
            <div class="btn-container">
                <a href="${platformUrl}" 
                   class="${buttonClass}" 
                   target="_blank" 
                   data-platform="${platform.name}" 
                   data-translate="access-platform">
                    ${buttonText}
                </a>
                ${directAccessButton}
            </div>
        `;
        
        container.appendChild(platformCard);
        
        // Debug: Check if the direct access button is in the DOM
        if (platform.name === 'LMS') {
            const directAccessBtn = platformCard.querySelector('a[href="https://lms0.final.edu.tr/"]');
            console.log('Direct access button found in DOM:', directAccessBtn);
            if (directAccessBtn) {
                console.log('Button text:', directAccessBtn.textContent);
                console.log('Button styles:', window.getComputedStyle(directAccessBtn));
            }
        }
    }

    /**
     * Gets platform button configuration
     * @param {Object} platform - Platform object
     * @returns {Object} Button configuration
     */
    function getPlatformButtonConfig(platform) {
        const specialPlatforms = ['RMS', 'SIS', 'LMS', 'LMS0', 'Leave and Absence', 'Document Application System', 'Summer School Application', 'Accommodation Booking Portal', 'Support Center', 'Student Exam Registration', 'Exemption exam form', 'Resit Exams Application'];
        const isSpecialPlatform = specialPlatforms.includes(platform.name);
        // Special case: SIS should be a direct link
        if (platform.name === 'SIS') {
            return {
                platformUrl: SIS_URL,
                buttonClass: 'btn btn-primary',
                buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
            };
        }
        // Special case: LMS should open sub-platform modal (both roles)
        if (platform.name === 'LMS') {
            return {
                platformUrl: '#',
                buttonClass: 'btn btn-primary access-platform-btn',
                buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
            };
        }
        // Special case: LMS0 should be a direct link
        if (platform.name === 'LMS0') {
            return {
                platformUrl: 'https://lms0.final.edu.tr',
                buttonClass: 'btn btn-primary',
                buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
            };
        }
        // Special case: Document Application System should be direct access to login
        if (platform.name === 'Document Application System') {
            return {
                platformUrl: 'https://docs.final.edu.tr/pages/login',
                buttonClass: 'btn btn-primary',
                buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
            };
        }
        // Special case: Leave and Absence should redirect to login page
        if (platform.name === 'Leave and Absence') {
            return {
                platformUrl: 'https://leave.final.digital/index.php',
                buttonClass: 'btn btn-primary',
                buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
            };
        }
        // Special case: Summer School Application should be direct access
        if (platform.name === 'Summer School Application') {
            return {
                platformUrl: 'https://online.final.edu.tr/yazokulu/login.php',
                buttonClass: 'btn btn-primary',
                buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
            };
        }
        // Special case: Accommodation Booking Portal should be direct access
        if (platform.name === 'Accommodation Booking Portal') {
            return {
                platformUrl: 'https://dorms.final.edu.tr/',
                buttonClass: 'btn btn-primary',
                buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
            };
        }
                    // Special case: Support Center should be direct access to login
            if (platform.name === 'Support Center') {
                return {
                    platformUrl: 'https://destek.final.edu.tr/index.php',
                    buttonClass: 'btn btn-primary',
                    buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
                };
            }
            // Special case: Student Exam Registration should be direct access
            if (platform.name === 'Student Exam Registration') {
                return {
                    platformUrl: 'https://online.final.edu.tr/exam/',
                    buttonClass: 'btn btn-primary',
                    buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
                };
            }
            // Special case: Exemption exam form should be direct access
            if (platform.name === 'Exemption exam form') {
                return {
                    platformUrl: 'https://online.final.edu.tr/muafiyet',
                    buttonClass: 'btn btn-primary',
                    buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
                };
            }
            // Special case: Resit Exams Application should be direct access
            if (platform.name === 'Resit Exams Application') {
                return {
                    platformUrl: 'https://online.final.edu.tr/resit/login.php',
                    buttonClass: 'btn btn-primary',
                    buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
                };
            }
        return {
            platformUrl: isSpecialPlatform ? '#' : platform.url,
            buttonClass: isSpecialPlatform ? 'btn btn-primary access-platform-btn' : 'btn btn-primary',
            buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
        };
    }

    // =============================================================================
    // ANNOUNCEMENTS MANAGEMENT
    // =============================================================================

    /**
     * Loads announcements from the PHP API
     */
    function loadAnnouncements() {
        if (!elements.announcementsSlider) {
            console.error('Announcements slider not found');
            return;
        }

        elements.announcementsSlider.innerHTML = '';

        fetch(`${API_BASE_URL}?endpoint=announcements`)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch announcements');
                return response.json();
            })
            .then(data => {
                console.log('Announcements data received:', data);
                if (data.success && data.announcements && data.announcements.length > 0) {
                    console.log('Creating announcement slides for:', data.announcements.length, 'announcements');
                    // Filter announcements by target audience based on user role
                    const user = getUserFromStorage();
                    const role = (user && user.role) ? String(user.role).toLowerCase().trim() : 'instructor';
                    const filtered = data.announcements.filter(a => {
                        const target = (a.target_audience || 'all').toLowerCase();
                        if (target === 'all') return true;
                        if (target === 'students') return role === 'student';
                        if (target === 'instructors') return role !== 'student';
                        return true;
                    });

                    announcementsState.slides = filtered;
                    announcementsState.totalSlides = filtered.length;
                    announcementsState.currentSlide = 0;

                    filtered.forEach((announcement, index) => {
                        const slideDiv = document.createElement('div');
                        slideDiv.className = 'announcements-slide';
                        createAnnouncementCard(announcement, slideDiv);
                        elements.announcementsSlider.appendChild(slideDiv);
                    });

                    updateAnnouncementsNavigation();
                    createAnnouncementsDots();
                } else {
                    elements.announcementsSlider.innerHTML = '<div class="announcements-slide"><p>No announcements found.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading announcements:', error);
                elements.announcementsSlider.innerHTML = '<div class="announcements-slide"><p>Error loading announcements.</p></div>';
            });
    }

    /**
     * Creates an announcement card element
     * @param {Object} announcement - Announcement data
     * @param {HTMLElement} container - Container to append the card to
     */
    function createAnnouncementCard(announcement, container) {
        const announcementCard = document.createElement('div');
        announcementCard.className = 'announcement-card';
        
        // Format the date with day name
        const date = new Date(announcement.created_at);
        const formattedDate = date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        announcementCard.innerHTML = `
            <h3>${announcement.title}</h3>
            <div class="announcement-date">${formattedDate}</div>
        `;
        
        // Add click event to show full announcement
        announcementCard.addEventListener('click', () => {
            showAnnouncementModal(announcement);
        });
        
        container.appendChild(announcementCard);
    }

    /**
     * Shows announcement modal with full details
     * @param {Object} announcement - Announcement data
     */
    function showAnnouncementModal(announcement) {
        // Create modal HTML
        const modalHTML = `
            <div id="announcement-modal" class="modal" style="display:flex; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; border-radius:8px; max-width:600px; width:90%; max-height:80vh; margin:40px auto; padding:32px; position:relative; display:flex; flex-direction:column;">
                    <button id="close-announcement-modal" style="position:absolute; top:12px; right:16px; font-size:24px; background:none; border:none; cursor:pointer;">&times;</button>
                    <h2 style="margin-bottom:16px; flex-shrink:0;">${announcement.title}</h2>
                    <div style="flex:1; overflow-y:auto; padding-right:8px;">
                        <div style="font-size:12px; color:#888; margin-bottom:16px; font-style:italic;">
                            ${new Date(announcement.created_at).toLocaleDateString() + ' ' + new Date(announcement.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </div>
                        <div style="line-height:1.6; margin-bottom:16px;">
                            ${announcement.content}
                        </div>
                        <div style="font-size:12px; color:#666; font-weight:500;">
                            By: ${announcement.created_by || 'Administrator'}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Setup close button
        const closeBtn = document.getElementById('close-announcement-modal');
        const modal = document.getElementById('announcement-modal');
        
        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.remove();
            };
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // =============================================================================
    // DINING MENU MANAGEMENT
    // =============================================================================

    /**
     * Loads dining menu from the PHP API
     */
    function loadDiningMenu() {
        if (!elements.diningMenuSlider) {
            console.error('Dining menu slider not found');
            return;
        }

        elements.diningMenuSlider.innerHTML = '';

		// Fetch dining menus for today and tomorrow only
        const promises = [];
		for (let i = 0; i < 2; i++) {
            const date = new Date();
            date.setDate(date.getDate() + i);
            const dateStr = date.toISOString().split('T')[0];
            
            promises.push(
                fetch(`${API_BASE_URL}?endpoint=dining-menu-today&date=${dateStr}`)
                    .then(response => response.json())
                    .then(data => ({ date: dateStr, data }))
                    .catch(error => ({ date: dateStr, data: { success: false, error } }))
            );
        }

        Promise.all(promises)
            .then(results => {
                const validMenus = results.filter(result => result.data.success && result.data.dining_menu);
                
                if (validMenus.length === 0) {
                    elements.diningMenuSlider.innerHTML = '<div class="dining-menu-slide"><p>No dining menu available.</p></div>';
                    return;
                }

                diningMenuState.slides = validMenus;
                diningMenuState.totalSlides = validMenus.length;
                diningMenuState.currentSlide = 0;

                validMenus.forEach((menuData, index) => {
                    const slideDiv = document.createElement('div');
                    slideDiv.className = 'dining-menu-slide';
                    createDiningMenuCard(menuData.data.dining_menu, slideDiv);
                    elements.diningMenuSlider.appendChild(slideDiv);
                });

                updateDiningMenuNavigation();
                createDiningMenuDots();
            })
            .catch(error => {
                console.error('Error loading dining menus:', error);
                elements.diningMenuSlider.innerHTML = '<div class="dining-menu-slide"><p>Error loading dining menu.</p></div>';
            });
    }

    /**
     * Creates a dining menu card element
     * @param {Object} diningMenu - Dining menu data
     * @param {HTMLElement} container - Container to append the card to
     */
    function createDiningMenuCard(diningMenu, container) {
        const diningMenuCard = document.createElement('div');
        diningMenuCard.className = 'dining-menu-card';
        
        // Format the date with day name
        const date = new Date(diningMenu.date);
        const formattedDate = date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Format times to show only hours and minutes
        const formatTime = (timeString) => {
            if (!timeString || timeString === 'N/A') return 'N/A';
            return timeString.substring(0, 5); // Take only HH:MM part
        };

        diningMenuCard.innerHTML = `
            <h3>${formattedDate}</h3>
            <div class="meal-section">
                <h4><i class="fas fa-sun" style="color: #c0392b; margin-right: 8px;"></i>Breakfast</h4>
                <p>${diningMenu.breakfast_menu || 'No breakfast menu available'}</p>
                <small>Time: ${formatTime(diningMenu.breakfast_start_time)} - ${formatTime(diningMenu.breakfast_end_time)}</small>
            </div>
            <div class="meal-section">
                <h4><i class="fas fa-utensils" style="color: #c0392b; margin-right: 8px;"></i>Lunch</h4>
                <p>${diningMenu.lunch_menu || 'No lunch menu available'}</p>
                <small>Time: ${formatTime(diningMenu.lunch_start_time)} - ${formatTime(diningMenu.lunch_end_time)}</small>
            </div>
        `;
        
        // Add click event to show full dining menu details
        diningMenuCard.addEventListener('click', () => {
            showDiningMenuModal(diningMenu);
        });
        
        container.appendChild(diningMenuCard);
    }

    /**
     * Shows dining menu modal with full details
     * @param {Object} diningMenu - Dining menu data
     */
    function showDiningMenuModal(diningMenu) {
        // Create modal HTML
        const modalHTML = `
            <div id="dining-menu-modal" class="modal" style="display:flex; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:#fff; border-radius:8px; max-width:600px; width:90%; max-height:80vh; margin:40px auto; padding:32px; position:relative; display:flex; flex-direction:column;">
                    <button id="close-dining-menu-modal" style="position:absolute; top:12px; right:16px; font-size:24px; background:none; border:none; cursor:pointer;">&times;</button>
                    <h2 style="margin-bottom:16px; flex-shrink:0;">Dining Menu - ${new Date(diningMenu.date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            })}</h2>
                                         <div style="flex:1; overflow-y:auto; padding-right:8px;">
                         <div class="meal-section" style="margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid #eee;">
                             <h3 style="color:#c0392b; margin-bottom:12px; font-size:18px;"><i class="fas fa-sun" style="color: #c0392b; margin-right: 8px;"></i>Breakfast</h3>
                             <p style="line-height:1.6; margin-bottom:8px;">${diningMenu.breakfast_menu || 'No breakfast menu available'}</p>
                             <small style="color:#888; font-style:italic;">Time: ${diningMenu.breakfast_start_time ? diningMenu.breakfast_start_time.substring(0, 5) : 'N/A'} - ${diningMenu.breakfast_end_time ? diningMenu.breakfast_end_time.substring(0, 5) : 'N/A'}</small>
                         </div>
                         <div class="meal-section">
                             <h3 style="color:#c0392b; margin-bottom:12px; font-size:18px;"><i class="fas fa-utensils" style="color: #c0392b; margin-right: 8px;"></i>Lunch</h3>
                             <p style="line-height:1.6; margin-bottom:8px;">${diningMenu.lunch_menu || 'No lunch menu available'}</p>
                             <small style="color:#888; font-style:italic;">Time: ${diningMenu.lunch_start_time ? diningMenu.lunch_start_time.substring(0, 5) : 'N/A'} - ${diningMenu.lunch_end_time ? diningMenu.lunch_end_time.substring(0, 5) : 'N/A'}</small>
                         </div>
                     </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Setup close button
        const closeBtn = document.getElementById('close-dining-menu-modal');
        const modal = document.getElementById('dining-menu-modal');
        
        if (closeBtn) {
            closeBtn.onclick = function() {
                modal.remove();
            };
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // =============================================================================
    // NOTIFICATION MANAGEMENT
    // =============================================================================

    /**
     * Loads notifications from backend and renders them
     */
    function loadNotifications() {
        if (!elements.notificationList) {
            console.error('Notification list container not found');
            return;
        }

        elements.notificationList.innerHTML = `<p class="no-notifications">${getTranslation('notifications-loading')}</p>`;
        
        const user = getUserFromStorage();
        if (!user || !user.username) {
            elements.notificationList.innerHTML = `<p class="no-notifications">${getTranslation('notifications-none')}</p>`;
            updateNotificationBadge(0);
            return;
        }

        // Fetch and display all notifications
        fetchAllNotifications(user.username)
            .then(allNotifications => {
                handleFetchedNotifications(allNotifications, []);
            })
            .catch((err) => {
                console.error('Error fetching notifications:', err);
                elements.notificationList.innerHTML = `<p class="no-notifications">${getTranslation('notifications-error')}</p>`;
                updateNotificationBadge(0);
            });
    }



    /**
     * Handles fetched notifications
     * @param {Array} allNotifications - All fetched notifications
     * @param {Array} storedNotifications - Previously stored notifications (unused)
     */
    function handleFetchedNotifications(allNotifications, storedNotifications) {
        if (allNotifications.length === 0) {
            elements.notificationList.innerHTML = `<p class="no-notifications">${getTranslation('notifications-none')}</p>`;
            updateNotificationBadge(0);
            return;
        }
        
        // Replace loading message with all notifications
        elements.notificationList.innerHTML = '';
        allNotifications.forEach(notification => {
            createNotificationItem(notification, elements.notificationList);
        });
        
        // Update notification badge
        updateNotificationBadge(allNotifications.length);
    }

    /**
     * Fetches all notifications from the API
     * @param {string} username - Username to fetch notifications for
     * @returns {Promise<Array>} Promise resolving to array of notifications
     */
    function fetchAllNotifications(username) {
        return fetch(`${API_BASE_URL}?endpoint=notifications&username=${encodeURIComponent(username)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && Array.isArray(data.notifications)) {
                    return data.notifications;
                }
                return [];
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
                return [];
            });
    }

    /**
     * Creates a notification card element
     * @param {Object} notification - Notification data
     * @param {HTMLElement} container - Container to append the card to
     */
    function createNotificationCard(notification, container) {
        const notifDiv = document.createElement('div');
        notifDiv.className = 'notification-card';
        
        const notificationUrl = getNotificationUrl(notification);
        const dateDisplay = getNotificationDateDisplay(notification);
        
        const cardHTML = `
            <h3>${notification.platform}</h3>
            ${dateDisplay}
            <p>${notification.message}</p>
            <a href="${notificationUrl}" 
               target="_blank" 
               class="btn btn-primary" 
               data-translate="open">
                ${getTranslation('Open')}
            </a>
        `;
        
        notifDiv.innerHTML = cardHTML;
        container.appendChild(notifDiv);
    }

    /**
     * Creates a notification item for the dropdown
     * @param {Object} notification - Notification data
     * @param {HTMLElement} container - Container to append the item to
     */
    function createNotificationItem(notification, container) {
        const notifDiv = document.createElement('div');
        notifDiv.className = 'notification-item';
        
        const notificationUrl = getNotificationUrl(notification);
        const dateDisplay = getNotificationDateDisplay(notification);
        
        const itemHTML = `
            <div class="notification-title">${notification.platform}</div>
            <div class="notification-content">${notification.message}</div>
            ${dateDisplay ? `<div class="notification-time">${notification.date}</div>` : ''}
            <a href="${notificationUrl}" target="_blank" class="btn btn-primary" style="margin-top: 8px; font-size: 12px; padding: 4px 8px;">
                ${getTranslation('Open')}
            </a>
        `;
        
        notifDiv.innerHTML = itemHTML;
        container.appendChild(notifDiv);
    }

    /**
     * Updates the notification badge count
     * @param {number} count - Number of notifications
     */
    function updateNotificationBadge(count) {
        if (elements.notificationBadge) {
            if (count > 0) {
                elements.notificationBadge.textContent = count > 99 ? '99+' : count.toString();
                elements.notificationBadge.style.display = 'block';
            } else {
                elements.notificationBadge.style.display = 'none';
            }
        }
    }

    /**
     * Gets notification URL based on platform
     * @param {Object} notification - Notification object
     * @returns {string} Notification URL
     */
    function getNotificationUrl(notification) {
        let notificationUrl = notification.url;
        const user = getUserFromStorage();
        
        if (notification.platform === 'RMS' && user && user.username) {
            notificationUrl = `rms_auth_bridge.php?username=${encodeURIComponent(user.username)}&type=notifications&page=Dashboard/notifications.php`;
        } else if (notification.platform === 'Leave and Absence') {
            // Open Leave Portal notifications directly; portal will prompt login if needed
            notificationUrl = 'https://leave.final.digital/notifications/all_notifications.php';
        } else if (notification.platform === 'LMS' && user && user.username) {
            // Use server-side direct link for LMS notifications
            const subplatformName = notification.subplatform || 'Unknown';
            notificationUrl = `${API_BASE_URL}?endpoint=lms_subplatform_direct_link&username=${encodeURIComponent(user.username)}&subplatform=${encodeURIComponent(subplatformName)}`;
        }
        
        return notificationUrl;
    }

    /**
     * Gets notification date display HTML
     * @param {Object} notification - Notification object
     * @returns {string} Date display HTML
     */
    function getNotificationDateDisplay(notification) {
        if (notification.date) {
            return `<small style="color: #888; display: block; margin-bottom: 10px;">${notification.date}</small>`;
        }
        return '';
    }

    // =============================================================================
    // TRANSLATION & INTERNATIONALIZATION
    // =============================================================================

    /**
     * Gets the current language setting
     * @returns {string} Current language code
     */
    function getCurrentLanguage() {
        return localStorage.getItem('language') || 'en';
    }

    /**
     * Gets translation for a given key
     * @param {string} key - Translation key
     * @returns {string} Translated text or key if translation not found
     */
    function getTranslation(key) {
        const currentLang = getCurrentLanguage();
        
        if (window.translations && window.translations[currentLang]) {
            const translation = window.translations[currentLang][key];
            
            if (translation) {
                return translation;
            } else {
                console.log('No translation found for key:', key, 'in language:', currentLang);
                return key;
            }
        } else {
            console.log('No translations available for language:', currentLang);
            return key;
        }
    }

    // =============================================================================
    // THEME MANAGEMENT
    // =============================================================================

    /**
     * Initializes theme based on saved preference
     */
    function initTheme() {
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark') {
            enableDarkMode();
        } else {
            disableDarkMode();
        }
    }

    /**
     * Enables dark mode
     */
    function enableDarkMode() {
        document.body.classList.add('dark-mode');
        if (elements.currentThemeText) {
            elements.currentThemeText.textContent = getTranslation('dark-mode') || 'Dark Mode';
        }
    }

    /**
     * Disables dark mode
     */
    function disableDarkMode() {
        document.body.classList.remove('dark-mode');
        if (elements.currentThemeText) {
            elements.currentThemeText.textContent = getTranslation('light-mode') || 'Light Mode';
        }
    }

    /**
     * Toggles dark mode
     */
    function toggleDarkMode() {
        const isDarkMode = document.body.classList.toggle('dark-mode');
        
        if (elements.currentThemeText) {
            if (isDarkMode) {
                elements.currentThemeText.textContent = getTranslation('dark-mode') || 'Dark Mode';
                localStorage.setItem('theme', 'dark');
            } else {
                elements.currentThemeText.textContent = getTranslation('light-mode') || 'Light Mode';
                localStorage.setItem('theme', 'light');
            }
        }
    }

    // =============================================================================
    // UTILITY FUNCTIONS
    // =============================================================================

    /**
     * Proxy AJAX/API requests to LMS subplatforms via backend to avoid CORS
     * @param {string} subplatformName - The name of the LMS subplatform
     * @param {string} username - The current user's username
     * @param {string} apiPath - The API path on the LMS server (relative)
     * @param {Object} [options] - fetch options (method, headers, body, etc.)
     * @returns {Promise<Response>}
     */
    function proxyLmsAjax(subplatformName, username, apiPath, options = {}) {
        const url = `${API_BASE_URL}?endpoint=lms_ajax_proxy&subplatform=${encodeURIComponent(subplatformName)}&username=${encodeURIComponent(username)}&apipath=${encodeURIComponent(apiPath)}`;
        return fetch(url, options);
    }

    // Example usage:
    // proxyLmsAjax('Üniversite Ortak/University Common', 'student1', 'webservice/rest/server.php?wsfunction=core_user_get_users_by_field&field=username&values[0]=student1')
    //     .then(response => response.json())
    //     .then(data => console.log('LMS API data:', data));
    
    // Make loadNotifications available globally for tour system
    window.loadNotifications = loadNotifications;

    // =============================================================================
    // DINING MENU SLIDER FUNCTIONS
    // =============================================================================

    /**
     * Sets up dining menu slider functionality
     */
    function setupDiningMenuSlider() {
        if (elements.diningPrevBtn) {
            elements.diningPrevBtn.addEventListener('click', () => {
                navigateDiningMenu('prev');
            });
        }

        if (elements.diningNextBtn) {
            elements.diningNextBtn.addEventListener('click', () => {
                navigateDiningMenu('next');
            });
        }

        // Touch events for swipe
        if (elements.diningMenuSlider) {
            elements.diningMenuSlider.addEventListener('touchstart', handleTouchStart);
            elements.diningMenuSlider.addEventListener('touchend', handleTouchEnd);
        }
    }

    /**
     * Handles touch start event
     */
    function handleTouchStart(e) {
        diningMenuState.touchStartX = e.changedTouches[0].screenX;
    }

    /**
     * Handles touch end event
     */
    function handleTouchEnd(e) {
        diningMenuState.touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }

    /**
     * Handles swipe gesture
     */
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = diningMenuState.touchStartX - diningMenuState.touchEndX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // Swipe left - next slide
                navigateDiningMenu('next');
            } else {
                // Swipe right - previous slide
                navigateDiningMenu('prev');
            }
        }
    }

    /**
     * Navigates to previous or next dining menu slide
     */
    function navigateDiningMenu(direction) {
        if (diningMenuState.totalSlides === 0) return;

        if (direction === 'prev' && diningMenuState.currentSlide > 0) {
            diningMenuState.currentSlide--;
        } else if (direction === 'next' && diningMenuState.currentSlide < diningMenuState.totalSlides - 1) {
            diningMenuState.currentSlide++;
        }

        updateDiningMenuSlider();
        updateDiningMenuNavigation();
        updateDiningMenuDots();
    }

    /**
     * Updates the dining menu slider position
     */
    function updateDiningMenuSlider() {
        if (elements.diningMenuSlider) {
            const translateX = -diningMenuState.currentSlide * 100;
            elements.diningMenuSlider.style.transform = `translateX(${translateX}%)`;
        }
    }

    /**
     * Updates dining menu navigation buttons
     */
    function updateDiningMenuNavigation() {
        if (elements.diningPrevBtn) {
            elements.diningPrevBtn.style.display = diningMenuState.currentSlide > 0 ? 'flex' : 'none';
            elements.diningPrevBtn.disabled = diningMenuState.currentSlide === 0;
        }

        if (elements.diningNextBtn) {
            elements.diningNextBtn.style.display = diningMenuState.currentSlide < diningMenuState.totalSlides - 1 ? 'flex' : 'none';
            elements.diningNextBtn.disabled = diningMenuState.currentSlide === diningMenuState.totalSlides - 1;
        }
    }

    /**
     * Creates navigation dots for dining menu
     */
    function createDiningMenuDots() {
        if (!elements.diningMenuDots) return;

        elements.diningMenuDots.innerHTML = '';
        
        for (let i = 0; i < diningMenuState.totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = 'dining-menu-dot';
            if (i === 0) dot.classList.add('active');
            
            dot.addEventListener('click', () => {
                diningMenuState.currentSlide = i;
                updateDiningMenuSlider();
                updateDiningMenuNavigation();
                updateDiningMenuDots();
            });
            
            elements.diningMenuDots.appendChild(dot);
        }
    }

    /**
     * Updates dining menu dots
     */
    function updateDiningMenuDots() {
        const dots = elements.diningMenuDots?.querySelectorAll('.dining-menu-dot');
        if (!dots) return;

        dots.forEach((dot, index) => {
            if (index === diningMenuState.currentSlide) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    // =============================================================================
    // ANNOUNCEMENTS SLIDER FUNCTIONS
    // =============================================================================

    /**
     * Sets up announcements slider functionality
     */
    function setupAnnouncementsSlider() {
        if (elements.announcementsPrevBtn) {
            elements.announcementsPrevBtn.addEventListener('click', () => {
                navigateAnnouncements('prev');
            });
        }

        if (elements.announcementsNextBtn) {
            elements.announcementsNextBtn.addEventListener('click', () => {
                navigateAnnouncements('next');
            });
        }

        // Touch events for swipe
        if (elements.announcementsSlider) {
            elements.announcementsSlider.addEventListener('touchstart', handleAnnouncementsTouchStart);
            elements.announcementsSlider.addEventListener('touchend', handleAnnouncementsTouchEnd);
        }
    }

    /**
     * Handles touch start event for announcements
     */
    function handleAnnouncementsTouchStart(e) {
        announcementsState.touchStartX = e.changedTouches[0].screenX;
    }

    /**
     * Handles touch end event for announcements
     */
    function handleAnnouncementsTouchEnd(e) {
        announcementsState.touchEndX = e.changedTouches[0].screenX;
        handleAnnouncementsSwipe();
    }

    /**
     * Handles swipe gesture for announcements
     */
    function handleAnnouncementsSwipe() {
        const swipeThreshold = 50;
        const diff = announcementsState.touchStartX - announcementsState.touchEndX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // Swipe left - next slide
                navigateAnnouncements('next');
            } else {
                // Swipe right - previous slide
                navigateAnnouncements('prev');
            }
        }
    }

    /**
     * Navigates to previous or next announcements slide
     */
    function navigateAnnouncements(direction) {
        if (announcementsState.totalSlides === 0) return;

        if (direction === 'prev' && announcementsState.currentSlide > 0) {
            announcementsState.currentSlide--;
        } else if (direction === 'next' && announcementsState.currentSlide < announcementsState.totalSlides - 1) {
            announcementsState.currentSlide++;
        }

        updateAnnouncementsSlider();
        updateAnnouncementsNavigation();
        updateAnnouncementsDots();
    }

    /**
     * Updates the announcements slider position
     */
    function updateAnnouncementsSlider() {
        if (elements.announcementsSlider) {
            const translateX = -announcementsState.currentSlide * 100;
            elements.announcementsSlider.style.transform = `translateX(${translateX}%)`;
        }
    }

    /**
     * Updates announcements navigation buttons
     */
    function updateAnnouncementsNavigation() {
        if (elements.announcementsPrevBtn) {
            elements.announcementsPrevBtn.style.display = announcementsState.currentSlide > 0 ? 'flex' : 'none';
            elements.announcementsPrevBtn.disabled = announcementsState.currentSlide === 0;
        }

        if (elements.announcementsNextBtn) {
            elements.announcementsNextBtn.style.display = announcementsState.currentSlide < announcementsState.totalSlides - 1 ? 'flex' : 'none';
            elements.announcementsNextBtn.disabled = announcementsState.currentSlide === announcementsState.totalSlides - 1;
        }
    }

    /**
     * Creates navigation dots for announcements
     */
    function createAnnouncementsDots() {
        if (!elements.announcementsDots) return;

        elements.announcementsDots.innerHTML = '';
        
        for (let i = 0; i < announcementsState.totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = 'announcements-dot';
            if (i === 0) dot.classList.add('active');
            
            dot.addEventListener('click', () => {
                announcementsState.currentSlide = i;
                updateAnnouncementsSlider();
                updateAnnouncementsNavigation();
                updateAnnouncementsDots();
            });
            
            elements.announcementsDots.appendChild(dot);
        }
    }

    /**
     * Updates announcements dots
     */
    function updateAnnouncementsDots() {
        const dots = elements.announcementsDots?.querySelectorAll('.announcements-dot');
        if (!dots) return;

        dots.forEach((dot, index) => {
            if (index === announcementsState.currentSlide) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
});