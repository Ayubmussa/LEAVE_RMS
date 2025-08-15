// Admin Panel JavaScript Functions
console.log('admin.js script loaded');

class AdminPanel {
    constructor() {
        console.log('AdminPanel constructor called');
        this.currentAdmin = null;
        this.admins = [];
        this.announcements = [];
        this.diningMenus = [];
        this.holidays = [];
        this.editingAnnouncementId = null;
        this.editingDiningMenuId = null;
        this.editingHolidayId = null;
        this.init();
    }

    init() {
        console.log('AdminPanel init called');
        this.checkAuth();
        this.setupEventListeners();
        this.setupBackButtonHandler();
        this.loadDashboardStats();
        this.populateYearOptions();
        this.loadAdmins();
        this.loadAnnouncements();
        this.loadDiningMenus();
        this.loadHolidays();
        this.loadUsers();
        this.loadPlatforms();
    }

    checkAuth() {
        console.log('checkAuth called');
        const adminSession = localStorage.getItem('adminSession');
        console.log('adminSession from localStorage:', adminSession);
        if (!adminSession) {
            console.log('No admin session found, redirecting to login');
            window.location.href = 'login.html';
            return;
        }

        try {
            this.currentAdmin = JSON.parse(adminSession);
            console.log('Current admin parsed:', this.currentAdmin);
            this.updateAdminInfo();
        } catch (error) {
            console.error('Error parsing admin session:', error);
            window.location.href = 'login.html';
        }
    }

    updateAdminInfo() {
        if (this.currentAdmin) {
            const adminName = document.getElementById('admin-name');
            const adminRole = document.getElementById('admin-role');
            const adminAvatar = document.getElementById('admin-avatar');
            const adminManagementNav = document.getElementById('admin-management-nav');
            
            if (adminName) adminName.textContent = this.currentAdmin.username;
            if (adminRole) adminRole.textContent = this.currentAdmin.role.replace('_', ' ').toUpperCase();
            if (adminAvatar) adminAvatar.textContent = this.currentAdmin.username.charAt(0).toUpperCase();
            
            // Show admin management nav only for super_admin role
            if (adminManagementNav) {
                if (this.currentAdmin.role === 'super_admin') {
                    adminManagementNav.style.display = 'block';
                } else {
                    adminManagementNav.style.display = 'none';
                }
            }

            // Check if this is an admin-only account and show notification
            const userSession = localStorage.getItem('user');
            const adminSession = localStorage.getItem('adminSession');
            
            // Show/hide "Go to User Panel" button based on user session
            const userPanelBtn = document.getElementById('user-panel-btn');
            if (userPanelBtn) {
                if (adminSession && userSession) {
                    // User-admin account - show the button
                    userPanelBtn.style.display = 'block';
                } else {
                    // Admin-only account - hide the button
                    userPanelBtn.style.display = 'none';
                }
            }
            
            if (adminSession && !userSession) {
                // Admin-only account - show notification about back button behavior
                setTimeout(() => {
                    this.showNotification('⚠️ Admin-only account: Pressing the back button will log you out. Use the logout button to safely exit.', 'warning');
                }, 1000);
            }
        }
    }

    setupEventListeners() {
        console.log('setupEventListeners called');
        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                
                const section = link.getAttribute('data-section');
                console.log('Navigation clicked, section:', section);
                this.showSection(section);
            });
        });

        // Logout
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }

        // User Panel button (for user-admin accounts)
        const userPanelBtn = document.getElementById('user-panel-btn');
        if (userPanelBtn) {
            userPanelBtn.addEventListener('click', () => {
                window.location.href = 'index.html';
            });
        }

        // Announcement form submission
        const announcementForm = document.getElementById('announcement-form');
        if (announcementForm) {
            announcementForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(announcementForm);
                const data = Object.fromEntries(formData.entries());
                this.saveAnnouncement(data);
            });
        }

        // Admin create/update form submission
        const adminManageForm = document.getElementById('admin-manage-form');
        if (adminManageForm) {
            adminManageForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(adminManageForm);
                const payload = Object.fromEntries(formData.entries());

                if (this.editingAdminId) {
                    // Update username, email and status
                    const updateOk = await this.updateAdmin({
                        action: 'admin-update',
                        id: parseInt(payload.id, 10),
                        username: payload.username,
                        email: payload.email,
                        is_active: payload.is_active === '1'
                    });
                    // If new password provided, call change-password
                    if (updateOk && payload.new_password) {
                        await this.updateAdmin({
                            action: 'admin-change-password',
                            id: parseInt(payload.id, 10),
                            new_password: payload.new_password
                        });
                    }
                    this.closeAdminModal();
                } else {
                    // Create admin (role forced to admin on backend)
                    await this.createAdmin({
                        action: 'admin-create',
                        username: payload.username,
                        password: payload.password,
                        email: payload.email,
                        role: 'admin'
                    });
                    this.closeAdminModal();
                }
            });
        }

        // Dining menu form submission
        const diningMenuForm = document.getElementById('dining-menu-form');
        if (diningMenuForm) {
            diningMenuForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(diningMenuForm);
                const data = Object.fromEntries(formData.entries());
                
                // Handle checkbox value
                data.is_recurring = document.getElementById('dining-menu-recurring').checked;
                
                this.saveDiningMenu(data);
            });
        }
        
        // Show/hide recurring options based on checkbox
        const diningMenuRecurringCheckbox = document.getElementById('dining-menu-recurring');
        if (diningMenuRecurringCheckbox) {
            diningMenuRecurringCheckbox.addEventListener('change', (e) => {
                const recurringOptions = document.getElementById('recurring-options');
                if (recurringOptions) {
                    if (e.target.checked) {
                        recurringOptions.style.display = 'block';
                    } else {
                        recurringOptions.style.display = 'none';
                    }
                }
            });
        }

        // Holiday form submission
        const holidayForm = document.getElementById('holiday-form');
        if (holidayForm) {
            holidayForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(holidayForm);
                const data = Object.fromEntries(formData.entries());
                this.saveHoliday(data);
            });
        }

        // Upload holiday file form submission
        const uploadHolidayForm = document.getElementById('upload-holiday-form');
        if (uploadHolidayForm) {
            uploadHolidayForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(uploadHolidayForm);
                this.uploadHolidayFile(formData);
            });
        }

        // Holiday button event listeners
        console.log('Setting up holiday button event listeners');
        console.log('All elements with id containing "holiday":', document.querySelectorAll('[id*="holiday"]'));
        const addHolidayBtn = document.getElementById('add-holiday-btn');
        if (addHolidayBtn) {
            console.log('Add holiday button found, adding event listener');
            addHolidayBtn.addEventListener('click', () => {
                console.log('Add holiday button clicked');
                this.showAddHolidayModal();
            });
        } else {
            console.log('Add holiday button not found');
        }

        const uploadHolidayBtn = document.getElementById('upload-holiday-btn');
        if (uploadHolidayBtn) {
            console.log('Upload holiday button found, adding event listener');
            uploadHolidayBtn.addEventListener('click', () => {
                console.log('Upload holiday button clicked');
                this.showUploadHolidayModal();
            });
        } else {
            console.log('Upload holiday button not found');
        }

        const exportHolidaysBtn = document.getElementById('export-holidays-btn');
        if (exportHolidaysBtn) {
            exportHolidaysBtn.addEventListener('click', () => this.exportHolidays());
        }

        const downloadTemplateBtn = document.getElementById('download-template-btn');
        if (downloadTemplateBtn) {
            downloadTemplateBtn.addEventListener('click', () => this.downloadTemplate());
        }

        const deleteAllHolidaysBtn = document.getElementById('delete-all-holidays-btn');
        if (deleteAllHolidaysBtn) {
            deleteAllHolidaysBtn.addEventListener('click', () => this.deleteAllHolidays());
        }

        // Year selector is now static (disabled), so no change event needed
        // const holidayYearSelect = document.getElementById('holiday-year');
        // if (holidayYearSelect) {
        //     holidayYearSelect.addEventListener('change', () => this.loadHolidays());
        // }

        // Prevent changes to upload year selector (readonly)
        const uploadYearSelect = document.getElementById('upload-year');
        if (uploadYearSelect) {
            uploadYearSelect.addEventListener('change', (e) => {
                e.preventDefault();
                // Reset to current year if somehow changed
                const currentYear = new Date().getFullYear();
                uploadYearSelect.value = currentYear;
            });
        }

        // Holiday modal close buttons
        const closeHolidayModal = document.getElementById('close-holiday-modal');
        if (closeHolidayModal) {
            closeHolidayModal.addEventListener('click', () => this.closeHolidayModal());
        }

        const cancelHolidayModal = document.getElementById('cancel-holiday-modal');
        if (cancelHolidayModal) {
            cancelHolidayModal.addEventListener('click', () => this.closeHolidayModal());
        }

        const closeUploadHolidayModal = document.getElementById('close-upload-holiday-modal');
        if (closeUploadHolidayModal) {
            closeUploadHolidayModal.addEventListener('click', () => this.closeUploadHolidayModal());
        }

        const cancelUploadHolidayModal = document.getElementById('cancel-upload-holiday-modal');
        if (cancelUploadHolidayModal) {
            cancelUploadHolidayModal.addEventListener('click', () => this.closeUploadHolidayModal());
        }

        // Admin modal event listeners
        console.log('Setting up admin modal event listeners');
        const addAdminBtn = document.getElementById('add-admin-btn');
        if (addAdminBtn) {
            console.log('Add admin button found, adding event listener');
            addAdminBtn.addEventListener('click', () => this.showAddAdminModal());
        } else {
            console.log('Add admin button not found');
        }

        const closeAdminModal = document.getElementById('close-admin-modal');
        if (closeAdminModal) {
            closeAdminModal.addEventListener('click', () => this.closeAdminModal());
        }

        const cancelAdminModal = document.getElementById('cancel-admin-modal');
        if (cancelAdminModal) {
            cancelAdminModal.addEventListener('click', () => this.closeAdminModal());
        }

        // Announcement modal event listeners
        const addAnnouncementBtn = document.getElementById('add-announcement-btn');
        if (addAnnouncementBtn) {
            addAnnouncementBtn.addEventListener('click', () => this.showAddAnnouncementModal());
        }

        const closeAnnouncementModal = document.getElementById('close-announcement-modal');
        if (closeAnnouncementModal) {
            closeAnnouncementModal.addEventListener('click', () => this.closeAnnouncementModal());
        }

        const cancelAnnouncementModal = document.getElementById('cancel-announcement-modal');
        if (cancelAnnouncementModal) {
            cancelAnnouncementModal.addEventListener('click', () => this.closeAnnouncementModal());
        }

        // Dining menu modal event listeners
        const addDiningMenuBtn = document.getElementById('add-dining-menu-btn');
        if (addDiningMenuBtn) {
            addDiningMenuBtn.addEventListener('click', () => this.showAddDiningMenuModal());
        }

        const closeDiningMenuModal = document.getElementById('close-dining-menu-modal');
        if (closeDiningMenuModal) {
            closeDiningMenuModal.addEventListener('click', () => this.closeDiningMenuModal());
        }

        const cancelDiningMenuModal = document.getElementById('cancel-dining-menu-modal');
        if (cancelDiningMenuModal) {
            cancelDiningMenuModal.addEventListener('click', () => this.closeDiningMenuModal());
        }

        // Dashboard card clicks
        const totalUsersCard = document.getElementById('total-users');
        if (totalUsersCard) {
            totalUsersCard.addEventListener('click', () => {
                this.showSection('users');
                this.loadUsers();
            });
            totalUsersCard.style.cursor = 'pointer';
        }

        const activeAdminsCard = document.getElementById('active-admins');
        if (activeAdminsCard) {
            activeAdminsCard.addEventListener('click', () => {
                this.showSection('admins');
                this.loadAdmins();
            });
            activeAdminsCard.style.cursor = 'pointer';
        }

        const totalPlatformsCard = document.getElementById('total-platforms');
        if (totalPlatformsCard) {
            totalPlatformsCard.addEventListener('click', () => {
                this.showSection('platforms');
                this.loadPlatforms();
            });
            totalPlatformsCard.style.cursor = 'pointer';
        }
    }

    showSection(sectionName) {
        console.log('showSection called with:', sectionName);
        // Check if user is trying to access admin management without super_admin role
        if (sectionName === 'admins' && this.currentAdmin && this.currentAdmin.role !== 'super_admin') {
            this.showNotification('Access denied. Admin management is restricted to Super Administrators only.', 'error');
            return;
        }
        
        document.querySelectorAll('.section-content').forEach(section => {
            section.classList.add('hidden');
        });
        
        const targetSection = document.getElementById(sectionName + '-section');
        console.log('Looking for section with id:', sectionName + '-section');
        if (targetSection) {
            console.log('Found target section, removing hidden class');
            targetSection.classList.remove('hidden');
            
            // Load section-specific data
            if (sectionName === 'holidays') {
                console.log('Loading holidays section data');
                this.loadHolidays();
            }
        } else {
            console.log('Target section not found');
        }
        
        const pageTitle = document.getElementById('page-title');
        if (pageTitle) {
            pageTitle.textContent = sectionName.charAt(0).toUpperCase() + sectionName.slice(1);
        }
    }

    logout() {
        localStorage.removeItem('adminSession');
        window.location.href = 'login.html';
    }

    /**
     * Sets up back button handler for admin logout functionality
     * Admin-only accounts will be logged out when back button is pressed
     * User-admin accounts will be redirected to user panel
     */
    setupBackButtonHandler() {
        // Listen for the beforeunload event (back button, refresh, close tab)
        window.addEventListener('beforeunload', (event) => {
            // Check if this is an admin-only account (no user session)
            const userSession = localStorage.getItem('user');
            const adminSession = localStorage.getItem('adminSession');
            
            if (adminSession && !userSession) {
                // Admin-only account - show confirmation dialog
                event.preventDefault();
                event.returnValue = 'You will be logged out. Are you sure you want to leave?';
                return event.returnValue;
            }
        });

        // Listen for popstate event (back/forward button navigation)
        window.addEventListener('popstate', (event) => {
            // Check if this is an admin-only account (no user session)
            const userSession = localStorage.getItem('user');
            const adminSession = localStorage.getItem('adminSession');
            
            if (adminSession && !userSession) {
                // Admin-only account - logout immediately
                this.logout();
            } else if (adminSession && userSession) {
                // User-admin account - redirect to user panel
                window.location.href = 'index.html';
            }
        });

        // Prevent back button by pushing a new state
        window.history.pushState(null, null, window.location.href);
        
        // Add another listener for when user tries to go back
        window.addEventListener('popstate', (event) => {
            // Push the state back to prevent navigation
            window.history.pushState(null, null, window.location.href);
            
            // Check if this is an admin-only account (no user session)
            const userSession = localStorage.getItem('user');
            const adminSession = localStorage.getItem('adminSession');
            
            if (adminSession && !userSession) {
                // Admin-only account - logout immediately
                this.logout();
            } else if (adminSession && userSession) {
                // User-admin account - redirect to user panel
                window.location.href = 'index.html';
            }
        });
    }

    async loadDashboardStats() {
        try {
            const response = await fetch('../database/admin_api.php?endpoint=dashboard-stats');
            const data = await response.json();
            
            if (data.success) {
                const stats = data.stats;
                                    this.updateStat('total-users', stats.total_users);
                    this.updateStat('active-admins', stats.active_admins);
                    this.updateStat('total-platforms', stats.total_platforms);
                
                // Update system health
                this.updateSystemHealth(stats.system_health);
                this.updateRecentLogins(stats.recent_logins);
            } else {
                console.error('Error loading dashboard stats:', data.error);
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    updateStat(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        }
    }

    updateSystemHealth(healthData) {
        const dbStatus = document.getElementById('db-status');
        const tablesStatus = document.getElementById('tables-status');
        
        if (dbStatus) {
            dbStatus.textContent = healthData.database;
            dbStatus.className = `health-indicator ${healthData.database}`;
        }
        
        if (tablesStatus) {
            tablesStatus.textContent = healthData.tables;
            tablesStatus.className = `health-indicator ${healthData.tables}`;
        }
    }

    updateRecentLogins(logins) {
        const container = document.getElementById('recent-logins-list');
        if (!container) return;

        if (logins.length === 0) {
            container.innerHTML = '<p style="color: #666; text-align: center;">No recent logins</p>';
            return;
        }

        container.innerHTML = '';
        logins.forEach(login => {
            const loginItem = document.createElement('div');
            loginItem.className = 'login-item';
            
            const loginTime = new Date(login.last_login).toLocaleString();
            
            loginItem.innerHTML = `
                <span class="login-username">${login.username}</span>
                <span class="login-time">${loginTime}</span>
            `;
            container.appendChild(loginItem);
        });
    }

    async loadAdmins() {
        try {
            const response = await fetch(`../database/admin_api.php?endpoint=admin-list&current_admin_id=${this.currentAdmin.id}`);
            const data = await response.json();
            
            if (data.success) {
                this.admins = data.admins;
                this.renderAdminsTable();
            } else {
                console.error('Error loading admins:', data.error);
            }
        } catch (error) {
            console.error('Error loading admins:', error);
        }
    }

    renderAdminsTable() {
        const tbody = document.getElementById('admins-table-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        this.admins.forEach(admin => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${admin.id}</td>
                <td>${admin.username}</td>
                <td>${admin.email || 'N/A'}</td>
                <td>${admin.role.replace('_', ' ').toUpperCase()}</td>
                <td>${admin.is_active == 1 ? 'Active' : 'Inactive'}</td>
                <td>${new Date(admin.created_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn" onclick="adminPanel.editAdmin(${admin.id})" style="margin-right: 5px; padding: 5px 10px; font-size: 12px;">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn" onclick="adminPanel.deleteAdmin(${admin.id})" style="padding: 5px 10px; font-size: 12px; background: #e74c3c;">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async createAdmin(formData) {
        try {
            // Add current admin ID to the request
            formData.current_admin_id = this.currentAdmin.id;
            
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Admin created successfully', 'success');
                this.loadAdmins();
                this.loadDashboardStats();
                return true;
            } else {
                this.showNotification('Error creating admin: ' + data.error, 'error');
                return false;
            }
        } catch (error) {
            console.error('Error creating admin:', error);
            this.showNotification('Error creating admin', 'error');
            return false;
        }
    }

    async updateAdmin(formData) {
        try {
            // Add current admin ID to the request
            formData.current_admin_id = this.currentAdmin.id;
            
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Admin updated successfully', 'success');
                this.loadAdmins();
                return true;
            } else {
                this.showNotification('Error updating admin: ' + data.error, 'error');
                return false;
            }
        } catch (error) {
            console.error('Error updating admin:', error);
            this.showNotification('Error updating admin', 'error');
            return false;
        }
    }

    async deleteAdmin(adminId) {
        if (confirm('Are you sure you want to delete this admin?')) {
            try {
                const response = await fetch('../database/admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'admin-delete',
                        id: adminId,
                        current_admin_id: this.currentAdmin.id
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Admin deleted successfully', 'success');
                    this.loadAdmins();
                    this.loadDashboardStats();
                } else {
                    this.showNotification('Error deleting admin: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting admin:', error);
                this.showNotification('Error deleting admin', 'error');
            }
        }
    }

    editAdmin(adminId) {
        const admin = this.admins.find(a => a.id == adminId);
        if (!admin) return;
        // Prepare modal for editing
        this.editingAdminId = admin.id;
        document.getElementById('admin-modal-title').textContent = 'Edit Admin';
        document.getElementById('admin-id').value = admin.id;
        document.getElementById('admin-username').value = admin.username;
        document.getElementById('admin-email').value = admin.email || '';
        
        // Show username field for editing, hide password field
        document.getElementById('admin-username-group').style.display = 'block';
        document.getElementById('admin-password-group').style.display = 'none';
        document.getElementById('admin-new-password-group').style.display = 'block';
        document.getElementById('admin-status-group').style.display = 'block';
        
        document.getElementById('admin-manage-modal').classList.remove('hidden');
    }

    showAddAdminModal() {
        this.editingAdminId = null;
        document.getElementById('admin-modal-title').textContent = 'Add Admin';
        document.getElementById('admin-manage-form').reset();
        document.getElementById('admin-id').value = '';
        
        // Show username and password fields for create
        document.getElementById('admin-username-group').style.display = 'block';
        document.getElementById('admin-password-group').style.display = 'block';
        document.getElementById('admin-new-password-group').style.display = 'none';
        document.getElementById('admin-status-group').style.display = 'none';
        
        document.getElementById('admin-manage-modal').classList.remove('hidden');
    }

    closeAdminModal() {
        document.getElementById('admin-manage-modal').classList.add('hidden');
        this.editingAdminId = null;
    }

    // =============================================================================
    // ANNOUNCEMENTS METHODS
    // =============================================================================

    async loadAnnouncements() {
        try {
            const response = await fetch('../database/admin_api.php?endpoint=announcement-list');
            const data = await response.json();
            
            if (data.success) {
                this.announcements = data.announcements;
                this.renderAnnouncements();
            } else {
                console.error('Error loading announcements:', data.error);
            }
        } catch (error) {
            console.error('Error loading announcements:', error);
        }
    }

    // =============================================================================
    // USERS AND PLATFORMS METHODS
    // =============================================================================

    async loadUsers() {
        try {
            const response = await fetch('../database/admin_api.php?endpoint=users-list');
            const data = await response.json();
            if (data.success) {
                this.renderUsers(data.users);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    renderUsers(users) {
        const tbody = document.getElementById('users-table-body');
        if (!tbody) return;
        if (!users || users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#666;">No users found</td></tr>';
            return;
        }
        tbody.innerHTML = users.map(u => {
            const isAdmin = !!u.admin_id;
            const role = u.admin_role || '-';
            const canManage = this.currentAdmin && this.currentAdmin.role === 'super_admin';
            const actionBtn = !canManage
                ? '-'
                : (isAdmin
                    ? `<button class="btn btn-secondary" onclick="adminPanel.demoteToUser('${u.username}')">Demote</button>`
                    : `<button class="btn" onclick="adminPanel.promoteToAdmin('${u.username}')">Promote</button>`);
            const statusBadge = isAdmin
                ? `<span class="announcement-status status-active">${role}</span>`
                : `<span class="announcement-status status-inactive">not admin</span>`;
            return `
            <tr>
                <td>${u.id}</td>
                <td>${u.username}</td>
                <td>${u.email || ''}</td>
                <td>${new Date(u.created_at).toLocaleString()}</td>
                <td>${statusBadge}</td>
                <td>${actionBtn}</td>
            </tr>`;
        }).join('');
    }

    async promoteToAdmin(username) {
        if (!confirm(`Promote ${username} to admin?`)) return;
        try {
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'user-promote-to-admin',
                    username: username,
                    role: 'admin',
                    current_admin_id: this.currentAdmin.id
                })
            });
            const data = await response.json();
            if (data.success) {
                this.showNotification('User promoted to admin', 'success');
                this.loadUsers();
                this.loadDashboardStats();
            } else {
                this.showNotification('Failed: ' + data.error, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showNotification('Error promoting user', 'error');
        }
    }

    async demoteToUser(username) {
        if (!confirm(`Demote ${username} to normal user?`)) return;
        try {
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'admin-demote-to-user',
                    username: username,
                    current_admin_id: this.currentAdmin.id
                })
            });
            const data = await response.json();
            if (data.success) {
                this.showNotification('Admin demoted to user', 'success');
                this.loadUsers();
                this.loadDashboardStats();
            } else {
                this.showNotification('Failed: ' + data.error, 'error');
            }
        } catch (e) {
            console.error(e);
            this.showNotification('Error demoting admin', 'error');
        }
    }

    async loadPlatforms() {
        try {
            const response = await fetch('../database/admin_api.php?endpoint=platforms-list');
            const data = await response.json();
            if (data.success) {
                this.renderPlatforms(data.platforms);
            }
        } catch (error) {
            console.error('Error loading platforms:', error);
        }
    }

    renderPlatforms(platforms) {
        const tbody = document.getElementById('platforms-table-body');
        if (!tbody) return;
        if (!platforms || platforms.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#666;">No platforms found</td></tr>';
            return;
        }
        tbody.innerHTML = platforms.map(p => `
            <tr>
                <td>${p.id}</td>
                <td>${p.name}</td>
                <td>${p.description || ''}</td>
                <td><a href="${p.url}" target="_blank">${p.url}</a></td>
                <td>${new Date(p.created_at).toLocaleString()}</td>
            </tr>
        `).join('');
    }

    renderAnnouncements() {
        const container = document.getElementById('announcements-list');
        if (!container) return;

        if (this.announcements.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #666; padding: 40px;">No announcements found.</p>';
            return;
        }

        container.innerHTML = this.announcements.map(announcement => `
            <div class="announcement-card">
                <div class="announcement-header">
                    <h3 class="announcement-title">${announcement.title}</h3>
                    <div class="announcement-actions">
                        <button class="btn" onclick="adminPanel.editAnnouncement(${announcement.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn" style="background: #e74c3c;" onclick="adminPanel.deleteAnnouncement(${announcement.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="announcement-meta">
                    <span class="announcement-priority priority-${announcement.priority}">${announcement.priority}</span>
                    <span class="announcement-status status-${announcement.is_active == 1 ? 'active' : 'inactive'}">${announcement.is_active == 1 ? 'Active' : 'Inactive'}</span>
                    <span>By: ${announcement.author_name}</span>
                    <span>Created: ${new Date(announcement.created_at).toLocaleDateString()}</span>
                </div>
                <div class="announcement-content">${announcement.content}</div>
            </div>
        `).join('');
    }

    showAddAnnouncementModal() {
        this.editingAnnouncementId = null;
        document.getElementById('modal-title').textContent = 'Add Announcement';
        document.getElementById('announcement-form').reset();
        document.getElementById('announcement-status-group').style.display = 'none';
        document.getElementById('announcement-modal').classList.remove('hidden');
    }

    showEditAnnouncementModal(announcementId) {
        const announcement = this.announcements.find(a => a.id == announcementId);
        if (!announcement) return;

        this.editingAnnouncementId = announcementId;
        document.getElementById('modal-title').textContent = 'Edit Announcement';
        
        // Fill form with announcement data
        document.getElementById('announcement-title').value = announcement.title;
        document.getElementById('announcement-content').value = announcement.content;
        document.getElementById('announcement-priority').value = announcement.priority;
        document.getElementById('announcement-status').value = announcement.is_active;
        
        // Show status field for editing
        document.getElementById('announcement-status-group').style.display = 'block';
        
        document.getElementById('announcement-modal').classList.remove('hidden');
    }

    closeAnnouncementModal() {
        document.getElementById('announcement-modal').classList.add('hidden');
        this.editingAnnouncementId = null;
    }

    editAnnouncement(announcementId) {
        this.showEditAnnouncementModal(announcementId);
    }

    async deleteAnnouncement(announcementId) {
        if (confirm('Are you sure you want to delete this announcement?')) {
            try {
                const response = await fetch('../database/admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'announcement-delete',
                        id: announcementId,
                        current_admin_id: this.currentAdmin.id
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Announcement deleted successfully', 'success');
                    this.loadAnnouncements();
                } else {
                    this.showNotification('Error deleting announcement: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting announcement:', error);
                this.showNotification('Error deleting announcement', 'error');
            }
        }
    }

    async saveAnnouncement(formData) {
        try {
            formData.current_admin_id = this.currentAdmin.id;
            
            const action = this.editingAnnouncementId ? 'announcement-update' : 'announcement-create';
            if (this.editingAnnouncementId) {
                formData.id = this.editingAnnouncementId;
            }
            
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    ...formData
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(this.editingAnnouncementId ? 'Announcement updated successfully' : 'Announcement created successfully', 'success');
                this.closeAnnouncementModal();
                this.loadAnnouncements();
            } else {
                this.showNotification('Error saving announcement: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error saving announcement:', error);
            this.showNotification('Error saving announcement', 'error');
        }
    }

    // =============================================================================
    // DINING MENU FUNCTIONS
    // =============================================================================

    async loadDiningMenus() {
        try {
            const response = await fetch('../database/admin_api.php?endpoint=dining-menu-list');
            const data = await response.json();
            
            if (data.success) {
                this.diningMenus = data.menus;
                this.renderDiningMenus();
            } else {
                console.error('Error loading dining menus:', data.error);
            }
        } catch (error) {
            console.error('Error loading dining menus:', error);
        }
    }

    renderDiningMenus() {
        const container = document.getElementById('dining-menu-list');
        if (!container) return;

        if (!this.diningMenus || this.diningMenus.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #666; padding: 40px;">No dining menus found</div>';
            return;
        }

        container.innerHTML = this.diningMenus.map(menu => {
            const date = new Date(menu.date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            return `
                <div class="dining-menu-card">
                    <div class="dining-menu-header">
                        <h3 class="dining-menu-date">${date}</h3>
                        <div class="dining-menu-actions">
                            <button class="btn" onclick="adminPanel.editDiningMenu(${menu.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn" style="background: #e74c3c;" onclick="adminPanel.deleteDiningMenu(${menu.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <div class="dining-menu-meta">
                        <span>Created by: ${menu.created_by_name}</span>
                        <span>Created: ${new Date(menu.created_at).toLocaleString()}</span>
                        ${menu.is_recurring ? '<span style="color: #28a745; font-weight: bold;"><i class="fas fa-sync-alt"></i> Recurring Menu</span>' : ''}
                    </div>
                    
                    <div class="dining-menu-section">
                        <div class="dining-menu-section-title">
                            <i class="fas fa-sun"></i> Breakfast
                        </div>
                        <div class="dining-menu-content">${menu.breakfast_menu || 'No breakfast menu set'}</div>
                        <div class="dining-menu-timing">
                            <span><i class="fas fa-clock"></i> ${menu.breakfast_start_time} - ${menu.breakfast_end_time}</span>
                        </div>
                    </div>
                    
                    <div class="dining-menu-section">
                        <div class="dining-menu-section-title">
                            <i class="fas fa-cloud-sun"></i> Lunch
                        </div>
                        <div class="dining-menu-content">${menu.lunch_menu || 'No lunch menu set'}</div>
                        <div class="dining-menu-timing">
                            <span><i class="fas fa-clock"></i> ${menu.lunch_start_time} - ${menu.lunch_end_time}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    showAddDiningMenuModal() {
        this.editingDiningMenuId = null;
        document.getElementById('dining-menu-modal-title').textContent = 'Add Dining Menu';
        
        // Reset form
        document.getElementById('dining-menu-form').reset();
        
        // Set default date to today
        document.getElementById('dining-menu-date').value = new Date().toISOString().split('T')[0];
        
        // Hide recurring options by default
        const recurringOptions = document.getElementById('recurring-options');
        if (recurringOptions) {
            recurringOptions.style.display = 'none';
        }
        
        // Add date validation
        this.setupDiningMenuDateValidation();
        
        document.getElementById('dining-menu-modal').classList.remove('hidden');
    }

    setupDiningMenuDateValidation() {
        const dateInput = document.getElementById('dining-menu-date');
        if (dateInput) {
            dateInput.addEventListener('change', async () => {
                const selectedDate = dateInput.value;
                if (selectedDate) {
                    try {
                        const response = await fetch(`../database/admin_api.php?endpoint=check-date-availability&date=${selectedDate}`);
                        const data = await response.json();
                        
                        if (!data.available) {
                            this.showNotification(`⚠️ This date is not available: ${data.message}`, 'error');
                            dateInput.style.borderColor = '#dc3545';
                        } else {
                            dateInput.style.borderColor = '#28a745';
                        }
                    } catch (error) {
                        console.error('Error checking date availability:', error);
                    }
                }
            });
        }
    }

    closeDiningMenuModal() {
        document.getElementById('dining-menu-modal').classList.add('hidden');
        this.editingDiningMenuId = null;
    }

    editDiningMenu(menuId) {
        const menu = this.diningMenus.find(m => m.id == menuId);
        if (!menu) return;

        this.editingDiningMenuId = menuId;
        document.getElementById('dining-menu-modal-title').textContent = 'Edit Dining Menu';
        
        // Fill form with menu data
        document.getElementById('dining-menu-date').value = menu.date;
        document.getElementById('dining-menu-breakfast').value = menu.breakfast_menu || '';
        document.getElementById('dining-menu-breakfast-start').value = menu.breakfast_start_time;
        document.getElementById('dining-menu-breakfast-end').value = menu.breakfast_end_time;
        document.getElementById('dining-menu-lunch').value = menu.lunch_menu || '';
        document.getElementById('dining-menu-lunch-start').value = menu.lunch_start_time;
        document.getElementById('dining-menu-lunch-end').value = menu.lunch_end_time;
        
        document.getElementById('dining-menu-modal').classList.remove('hidden');
    }

    async deleteDiningMenu(menuId) {
        if (confirm('Are you sure you want to delete this dining menu?')) {
            try {
                const response = await fetch('../database/admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'dining-menu-delete',
                        id: menuId,
                        current_admin_id: this.currentAdmin.id
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Dining menu deleted successfully', 'success');
                    this.loadDiningMenus();
                } else {
                    this.showNotification('Error deleting dining menu: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting dining menu:', error);
                this.showNotification('Error deleting dining menu', 'error');
            }
        }
    }

    showNotification(message, type = 'success') {
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
            background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
            color: ${type === 'success' ? '#155724' : '#721c24'};
            border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
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

    async saveDiningMenu(formData) {
        try {
            formData.current_admin_id = this.currentAdmin.id;
            
            const action = this.editingDiningMenuId ? 'dining-menu-update' : 'dining-menu-create';
            if (this.editingDiningMenuId) {
                formData.id = this.editingDiningMenuId;
            }
            
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    ...formData
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                let message = this.editingDiningMenuId ? 'Dining menu updated successfully' : 'Dining menu created successfully';
                
                // Add recurring menu information if available
                if (data.recurring_menus_created && data.recurring_menus_created > 0) {
                    message += ` (${data.recurring_menus_created} recurring menus created)`;
                }
                
                this.showNotification(message, 'success');
                this.closeDiningMenuModal();
                this.loadDiningMenus();
            } else {
                this.showNotification('Error saving dining menu: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error saving dining menu:', error);
            this.showNotification('Error saving dining menu', 'error');
        }
    }

    // =============================================================================
    // HOLIDAYS AND DAYS OFF MANAGEMENT
    // =============================================================================

    populateYearOptions() {
        const yearSelect = document.getElementById('holiday-year');
        const uploadYearSelect = document.getElementById('upload-year');
        
        // Auto-update to current year - static within session but updates yearly
        const currentYear = new Date().getFullYear();
        
        console.log('Populating year options with year:', currentYear);
        console.log('Year select found:', yearSelect);
        console.log('Upload year select found:', uploadYearSelect);

        // Populate main year selector
        if (yearSelect) {
            yearSelect.innerHTML = '';
            const option = document.createElement('option');
            option.value = currentYear;
            option.textContent = currentYear;
            option.selected = true;
            yearSelect.appendChild(option);
            console.log('Main year selector populated');
        }

        // Populate upload year selector
        if (uploadYearSelect) {
            uploadYearSelect.innerHTML = '';
            const option = document.createElement('option');
            option.value = currentYear;
            option.textContent = currentYear;
            option.selected = true;
            uploadYearSelect.appendChild(option);
            console.log('Upload year selector populated');
        } else {
            console.error('Upload year selector not found!');
        }
        
        // Update fallback hidden input
        const yearFallbackInput = document.getElementById('year-fallback-input');
        if (yearFallbackInput) {
            yearFallbackInput.value = currentYear;
            console.log('Fallback year input updated to:', currentYear);
        }
    }

    async loadHolidays() {
        console.log('loadHolidays called');
        try {
            // Auto-update to current year - static within session but updates yearly
            const year = new Date().getFullYear();
            console.log('Loading holidays for year:', year);
            const response = await fetch(`../database/admin_api.php?endpoint=holiday-list&year=${year}`);
            const data = await response.json();
            
            if (data.success) {
                this.holidays = data.holidays;
                console.log('Holidays loaded:', this.holidays.length);
                this.renderHolidays();
            } else {
                console.error('Error loading holidays:', data.error);
            }
        } catch (error) {
            console.error('Error loading holidays:', error);
        }
    }

    renderHolidays() {
        console.log('renderHolidays called');
        const container = document.getElementById('holidays-list');
        if (!container) {
            console.log('Holidays list container not found');
            return;
        }

        if (!this.holidays || this.holidays.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #666; padding: 40px;">No holidays found for this year</div>';
            return;
        }

        container.innerHTML = this.holidays.map(holiday => {
            const date = new Date(holiday.date).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            return `
                <div class="holiday-card">
                    <div class="holiday-header">
                        <h3 class="holiday-date">${date}</h3>
                        <div class="holiday-actions">
                            <button class="btn" onclick="adminPanel.editHoliday(${holiday.id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn" style="background: #e74c3c;" onclick="adminPanel.deleteHoliday(${holiday.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <div class="holiday-meta">
                        <span>Type: ${holiday.type}</span>
                        <span>Day: ${holiday.day_of_week}</span>
                        ${holiday.is_recurring ? '<span style="color: #e67e22;">🔄 Recurring</span>' : ''}
                    </div>
                    <div class="holiday-content">
                        <h4>${holiday.holiday_name}</h4>
                        ${holiday.description ? `<p>${holiday.description}</p>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    showAddHolidayModal() {
        console.log('showAddHolidayModal called');
        this.editingHolidayId = null;
        document.getElementById('holiday-modal-title').textContent = 'Add Holiday';
        
        // Reset form
        document.getElementById('holiday-form').reset();
        
        // Set default date to current year (auto-updates yearly)
        const today = new Date();
        const currentYear = today.getFullYear();
        const defaultDate = `${currentYear}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        document.getElementById('holiday-date').value = defaultDate;
        
        document.getElementById('holiday-modal').classList.remove('hidden');
        console.log('Holiday modal should now be visible');
    }

    closeHolidayModal() {
        document.getElementById('holiday-modal').classList.add('hidden');
        this.editingHolidayId = null;
    }

    editHoliday(holidayId) {
        const holiday = this.holidays.find(h => h.id == holidayId);
        if (!holiday) return;

        this.editingHolidayId = holidayId;
        document.getElementById('holiday-modal-title').textContent = 'Edit Holiday';
        
        // Fill form with holiday data
        document.getElementById('holiday-date').value = holiday.date;
        document.getElementById('holiday-name').value = holiday.holiday_name;
        document.getElementById('holiday-type').value = holiday.type;
        document.getElementById('holiday-description').value = holiday.description || '';
        document.getElementById('holiday-recurring').checked = holiday.is_recurring == 1;
        
        document.getElementById('holiday-modal').classList.remove('hidden');
    }

    async deleteHoliday(holidayId) {
        if (confirm('Are you sure you want to delete this holiday?')) {
            try {
                const response = await fetch('../database/admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'holiday-delete',
                        id: holidayId,
                        current_admin_id: this.currentAdmin.id
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('Holiday deleted successfully', 'success');
                    this.loadHolidays();
                } else {
                    this.showNotification('Error deleting holiday: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting holiday:', error);
                this.showNotification('Error deleting holiday', 'error');
            }
        }
    }

    async saveHoliday(formData) {
        try {
            formData.current_admin_id = this.currentAdmin.id;
            
            const action = this.editingHolidayId ? 'holiday-update' : 'holiday-create';
            if (this.editingHolidayId) {
                formData.id = this.editingHolidayId;
            }
            
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    ...formData
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification(this.editingHolidayId ? 'Holiday updated successfully' : 'Holiday created successfully', 'success');
                this.closeHolidayModal();
                this.loadHolidays();
            } else {
                this.showNotification('Error saving holiday: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error saving holiday:', error);
            this.showNotification('Error saving holiday', 'error');
        }
    }

    showUploadHolidayModal() {
        console.log('showUploadHolidayModal called');
        document.getElementById('upload-holiday-form').reset();
        document.getElementById('upload-holiday-modal').classList.remove('hidden');
        console.log('Upload holiday modal should now be visible');
    }

    closeUploadHolidayModal() {
        document.getElementById('upload-holiday-modal').classList.add('hidden');
    }

    async uploadHolidayFile(formData) {
        try {
            // Debug: Log what's in the formData
            console.log('Upload formData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            // Always ensure year is set - the disabled attribute was preventing form submission
            const currentYear = new Date().getFullYear();
            formData.set('year', currentYear.toString());
            console.log('Set year in formData to:', currentYear);
            
            formData.append('action', 'holiday-upload');
            formData.append('current_admin_id', this.currentAdmin.id);
            
            console.log('Final formData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            const response = await fetch('../database/admin_api.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                let message = `Holidays uploaded successfully! Imported: ${data.imported} holidays`;
                if (data.errors && data.errors.length > 0) {
                    message += ` (${data.errors.length} errors)`;
                }
                this.showNotification(message, 'success');
                this.closeUploadHolidayModal();
                this.loadHolidays();
            } else {
                this.showNotification('Error uploading holidays: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error uploading holidays:', error);
            this.showNotification('Error uploading holidays', 'error');
        }
    }

    async exportHolidays() {
        try {
            // Auto-update to current year - static within session but updates yearly
            const year = new Date().getFullYear();
            window.open(`../database/admin_api.php?endpoint=holiday-export&year=${year}`, '_blank');
        } catch (error) {
            console.error('Error exporting holidays:', error);
            this.showNotification('Error exporting holidays', 'error');
        }
    }

    async downloadTemplate() {
        try {
            // Auto-update to current year - static within session but updates yearly
            const year = new Date().getFullYear();
            window.open(`../database/admin_api.php?endpoint=holiday-template&year=${year}`, '_blank');
        } catch (error) {
            console.error('Error downloading template:', error);
            this.showNotification('Error downloading template', 'error');
        }
    }

    async deleteAllHolidays() {
        // Auto-update to current year - static within session but updates yearly
        const year = new Date().getFullYear();
        if (confirm(`Are you sure you want to delete ALL holidays for ${year}? This action cannot be undone.`)) {
            try {
                const response = await fetch('../database/admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'holiday-delete-by-year',
                        year: year,
                        current_admin_id: this.currentAdmin.id
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('All holidays deleted successfully', 'success');
                    this.loadHolidays();
                } else {
                    this.showNotification('Error deleting holidays: ' + data.error, 'error');
                }
            } catch (error) {
                console.error('Error deleting holidays:', error);
                this.showNotification('Error deleting holidays', 'error');
            }
        }
    }
}

// Initialize admin panel when DOM is loaded
let adminPanel;
console.log('About to add DOMContentLoaded listener');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired');
    adminPanel = new AdminPanel();
    // Make adminPanel available globally for any remaining onclick handlers
    window.adminPanel = adminPanel;
    console.log('AdminPanel initialized and made global');
});
console.log('DOMContentLoaded listener added');
