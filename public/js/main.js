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

const API_BASE_URL = 'http://localhost/LEAVE_RMS/database/api.php';
const SIS_URL = 'https://sis.final.edu.tr/';

// =============================================================================
// INITIALIZATION
// =============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // DOM element references
    const elements = {
        loginSection: document.getElementById('login-section'),
        platformsSection: document.getElementById('platforms-section'),
        usernameElement: document.getElementById('username'),
        logoutBtn: document.getElementById('logout-btn'),
        darkModeBtn: document.getElementById('dark-mode-btn'),
        currentThemeText: document.getElementById('current-theme'),
        userDropdownBtn: document.getElementById('user-dropdown-btn'),
        userDropdown: document.querySelector('.user-dropdown'),
        notificationList: document.getElementById('notification-list'),
        platformsContainer: document.querySelector('.platforms-container')
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
            loadNotifications();
            loadPlatforms();
            updateNotificationHeader();
            console.log('Language change handling complete in main.js');
        });
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
        
        // Always load platforms
        loadPlatforms();
    }

    /**
     * Shows the logged-in state
     * @param {Object} user - User object
     */
    function showLoggedInState(user) {
        elements.loginSection.classList.add('hidden');
        elements.platformsSection.classList.remove('hidden');
        
        if (elements.usernameElement) {
            elements.usernameElement.textContent = `Welcome, ${user.username || 'User'}`;
        }
        
        if (elements.logoutBtn) {
            elements.logoutBtn.classList.remove('hidden');
        }
        
        // Load notifications only if user is logged in
        loadNotifications();
    }

    /**
     * Shows the logged-out state
     */
    function showLoggedOutState() {
        elements.loginSection.classList.remove('hidden');
        elements.platformsSection.classList.add('hidden');
        
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
            alert('Please log in first.');
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
     * Handles RMS platform access
     * @param {HTMLElement} button - The clicked button
     * @param {Object} user - User object
     */
    function handleRMSAccess(button, user) {
        button.textContent = 'Authenticating...';
        const proxyUrl = `${API_BASE_URL}?endpoint=rms_dashboard_proxy&username=${encodeURIComponent(user.username)}`;
        window.open(proxyUrl, '_blank');
        resetButtonState(button);
    }

    /**
     * Handles Leave Portal platform access
     * @param {HTMLElement} button - The clicked button
     * @param {Object} user - User object
     */
    function handleLeavePortalAccess(button, user) {
        button.textContent = 'Opening...';
        const proxyUrl = `${API_BASE_URL}?endpoint=leave_portal_proxy&username=${encodeURIComponent(user.username)}&page=Dashboard/adminDashboard.php`;
        window.open(proxyUrl, '_blank');
        resetButtonState(button);
    }

    /**
     * Handles SIS platform access with CAPTCHA handling
     * @param {HTMLElement} button - The clicked button
     */
    function handleSISAccess(button) {
        const user = getUserFromStorage();
        
        if (!user || !user.username) {
            alert('Please log in first.');
            return;
        }
        
        setButtonLoadingState(button, 'Checking SIS...');
        
        fetch(`${API_BASE_URL}?endpoint=authenticate_platform`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                platform: 'SIS',
                username: user.username
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.captcha_required) {
                alert('SIS requires CAPTCHA verification. You will be redirected to the SIS login page where you can complete the CAPTCHA manually.');
                window.open(SIS_URL, '_blank');
            } else if (data.success) {
                window.open(SIS_URL, '_blank');
            } else {
                console.log('SIS authentication failed, opening SIS directly');
                window.open(SIS_URL, '_blank');
            }
        })
        .catch(error => {
            console.error('Error checking SIS authentication:', error);
            window.open(SIS_URL, '_blank');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`;
        });
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
                    alert('Failed to load LMS sub-platforms.');
                }
            })
            .catch(error => {
                console.error('Error loading LMS sub-platforms:', error);
                alert('Failed to load LMS sub-platforms.');
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
                    Access
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
                handleLmsSubplatformAccess(
                    this.dataset.url, 
                    this.dataset.login, 
                    this.dataset.notif, 
                    this
                );
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
            alert('Please log in first.');
            return;
        }
        
        setButtonLoadingState(btn, 'Opening...');
        
        const subplatformCard = btn.closest('.lms-subplatform-card');
        const subplatformName = subplatformCard ? subplatformCard.querySelector('span').textContent : 'Unknown';
        
        const params = new URLSearchParams({
            endpoint: 'lms_subplatform_direct_link',
            username: user.username,
            subplatform: subplatformName
        });
        
        fetch(`${API_BASE_URL}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.url) {
                    window.open(data.url, '_blank');
                } else {
                    alert('Failed to generate LMS access link.');
                }
            })
            .catch(error => {
                console.error('Error generating LMS access link:', error);
                alert('Failed to generate LMS access link.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Access';
            });
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
                if (data.success && data.platforms && data.platforms.length > 0) {
                    data.platforms.forEach(platform => {
                        createPlatformCard(platform, elements.platformsContainer);
                    });
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
        
        platformCard.innerHTML = `
            <h3>${platform.name}</h3>
            <p>${platform.description}</p>
            <a href="${platformUrl}" 
               class="${buttonClass}" 
               target="_blank" 
               data-platform="${platform.name}" 
               data-translate="access-platform">
                ${buttonText}
            </a>
        `;
        
        container.appendChild(platformCard);
    }

    /**
     * Gets platform button configuration
     * @param {Object} platform - Platform object
     * @returns {Object} Button configuration
     */
    function getPlatformButtonConfig(platform) {
        const specialPlatforms = ['RMS', 'SIS', 'LMS', 'Leave and Absence'];
        const isSpecialPlatform = specialPlatforms.includes(platform.name);
        
        return {
            platformUrl: isSpecialPlatform ? '#' : platform.url,
            buttonClass: isSpecialPlatform ? 'btn btn-primary access-platform-btn' : 'btn btn-primary',
            buttonText: `<span style="margin-right:6px;">\u{1F517}</span>${getTranslation('access-platform')}`
        };
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

        elements.notificationList.innerHTML = `<p>${getTranslation('notifications-loading')}</p>`;
        
        const user = getUserFromStorage();
        if (!user || !user.username) {
            elements.notificationList.innerHTML = `<p>${getTranslation('notifications-none')}</p>`;
            return;
        }

        // Display stored LMS notifications first
        const lmsSubplatformNotifications = getStoredLmsNotifications();
        if (lmsSubplatformNotifications.length > 0) {
            displayStoredNotifications(lmsSubplatformNotifications);
        }

        // Fetch and display all notifications
        fetchAllNotifications(user.username)
            .then(allNotifications => {
                handleFetchedNotifications(allNotifications, lmsSubplatformNotifications);
            })
            .catch((err) => {
                console.error('Error fetching notifications:', err);
                if (lmsSubplatformNotifications.length === 0) {
                    elements.notificationList.innerHTML = `<p>${getTranslation('notifications-error')}</p>`;
                }
            });
    }

    /**
     * Gets stored LMS notifications
     * @returns {Array} Array of stored notifications
     */
    function getStoredLmsNotifications() {
        return JSON.parse(localStorage.getItem('lms_subplatform_notifications') || '[]');
    }

    /**
     * Displays stored notifications
     * @param {Array} notifications - Array of notifications
     */
    function displayStoredNotifications(notifications) {
        elements.notificationList.innerHTML = '';
        notifications.forEach(notification => {
            createNotificationCard(notification, elements.notificationList);
        });
        
        // Clear the stored notifications after displaying them
        localStorage.removeItem('lms_subplatform_notifications');
    }

    /**
     * Handles fetched notifications
     * @param {Array} allNotifications - All fetched notifications
     * @param {Array} storedNotifications - Previously stored notifications
     */
    function handleFetchedNotifications(allNotifications, storedNotifications) {
        if (allNotifications.length === 0 && storedNotifications.length === 0) {
            elements.notificationList.innerHTML = `<p>${getTranslation('notifications-none')}</p>`;
            return;
        }
        
        if (storedNotifications.length > 0) {
            // Append new notifications to existing ones
            allNotifications.forEach(notification => {
                createNotificationCard(notification, elements.notificationList);
            });
        } else {
            // Replace loading message with all notifications
            elements.notificationList.innerHTML = '';
            allNotifications.forEach(notification => {
                createNotificationCard(notification, elements.notificationList);
            });
        }
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
     * Gets notification URL based on platform
     * @param {Object} notification - Notification object
     * @returns {string} Notification URL
     */
    function getNotificationUrl(notification) {
        let notificationUrl = notification.url;
        const user = getUserFromStorage();
        
        if (notification.platform === 'RMS' && user && user.username) {
            notificationUrl = `${API_BASE_URL}?endpoint=rms_notifications_proxy&username=${encodeURIComponent(user.username)}`;
        } else if (notification.platform === 'Leave and Absence' && user && user.username) {
            notificationUrl = `${API_BASE_URL}?endpoint=leave_portal_proxy&username=${encodeURIComponent(user.username)}&page=notifications/all_notifications.php`;
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
});