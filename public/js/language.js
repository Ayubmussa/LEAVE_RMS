document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const languageBtn = document.getElementById('language-btn');
    const languageDropdown = document.getElementById('language-dropdown');
    const currentLanguageSpan = document.getElementById('current-language');
    const languageOptions = document.querySelectorAll('.language-option');
    
    // Language translations
    const translations = {
        en: {
            // Login page
            'welcome': 'Welcome to Final Global',
            'sign-in-continue': 'Sign in to continue to your account',
            'email': 'Username',
            'enter-username': 'Enter your username',
            'password': 'Password',
            'enter-password': 'Enter your password',
            'sign-in': 'Sign In',
            
            // Main page
            'welcome-to-platform': "Final Global",
            'welcome-user': 'Welcome, ',
            'logout': 'Logout',
            'please-login': 'Please Login',
            'need-login': 'You need to login to access the platforms.',
            'go-to-login': 'Go to Login',
            'select-platform': 'Select a Platform',
            'choose-platform': 'Choose one of the following platforms to access:',
            'access-platform': 'Access Platform',
            'Open': 'Open',
            'copyright': '© 2025 Final Global System',
            'notifications': 'Notifications',
            'notifications-loading': 'Loading...',
            'notifications-none': 'No notifications.',
            'notifications-error': 'Error loading notifications.',
            'notifications-delete-confirm': 'Delete this notification?',
            'notifications-delete-failed': 'Failed to delete notification.',
            'options': 'Options',
            'light-mode': 'Light Mode',
            'dark-mode': 'Dark Mode',
            
            // Tour translations
            'tour-notifications-title': 'Notifications Center',
            'tour-notifications-content': 'Welcome to your notifications center! Here you\'ll see important updates from all your connected platforms including RMS, Leave Portal, SIS, and LMS.',
            'tour-platforms-title': 'Platform Access',
            'tour-platforms-content': 'This is where you can access all your university platforms. Click on any platform card to open it in a new tab.',
            'tour-settings-title': 'User Settings',
            'tour-settings-content': 'Click here to access your account settings, change language, toggle dark mode, or log out.',
            'tour-navigation-title': 'Platform Navigation',
            'tour-navigation-content': 'This is your central hub for accessing all university systems. You can always return here to switch between platforms.',
            'tour-welcome-title': 'Welcome Message',
            'tour-welcome-content': 'You\'re all set! Your username is displayed here. You can now explore all the platforms and features available to you.',
            'tour-previous': 'Previous',
            'tour-next': 'Next',
            'tour-finish': 'Finish',
            'tour-restart': 'Restart Tour',
            'tour-skip': 'Skip'
        },
        tr: {
            // Login page
            'welcome': 'Final Global"a hoş geldiniz',
            'sign-in-continue': 'Hesabınıza devam etmek için giriş yapın',
            'email': 'Kullanıcı Adı',
            'enter-username': 'Kullanıcı adınızı girin',
            'password': 'Şifre',
            'enter-password': 'Şifrenizi girin',
            'sign-in': 'Giriş Yap',
            
            // Main page
            'welcome-to-platform': 'Final Global',
            'welcome-user': 'Hoş geldiniz, ',
            'logout': 'Çıkış Yap',
            'please-login': 'Lütfen Giriş Yapın',
            'need-login': 'Platformlara erişmek için giriş yapmanız gerekiyor.',
            'go-to-login': 'Giriş Sayfasına Git',
            'select-platform': 'Bir Platform Seçin',
            'choose-platform': 'Erişmek için aşağıdaki platformlardan birini seçin:',
            'access-platform': 'Platforma Eriş',
            'Open': 'Aç',
            'copyright': '© 2025 Final küresel Sistemi',
            'notifications': 'Bildirimler',
            'notifications-loading': 'Yükleniyor...',
            'notifications-none': 'Bildirim yok.',
            'notifications-error': 'Bildirimler yüklenirken hata oluştu.',
            'notifications-delete-confirm': 'Bu bildirimi silmek istiyor musunuz?',
            'notifications-delete-failed': 'Bildirim silinemedi.',
            'options': 'Seçenekler',
            'light-mode': 'Aydınlık Mod',
            'dark-mode': 'Karanlık Mod',
            
            // Tour translations
            'tour-notifications-title': 'Bildirim Merkezi',
            'tour-notifications-content': 'Bildirim merkezinize hoş geldiniz! Burada RMS, İzin Portalı, SIS ve LMS dahil tüm bağlı platformlarınızdan önemli güncellemeleri göreceksiniz.',
            'tour-platforms-title': 'Platform Erişimi',
            'tour-platforms-content': 'Bu, tüm üniversite platformlarınıza erişebileceğiniz yerdir. Herhangi bir platform kartına tıklayarak yeni bir sekmede açabilirsiniz.',
            'tour-settings-title': 'Kullanıcı Ayarları',
            'tour-settings-content': 'Hesap ayarlarınıza erişmek, dili değiştirmek, karanlık modu açıp kapatmak veya çıkış yapmak için buraya tıklayın.',
            'tour-navigation-title': 'Platform Navigasyonu',
            'tour-navigation-content': 'Bu, tüm üniversite sistemlerine erişim için merkezi hub\'ınızdır. Platformlar arasında geçiş yapmak için her zaman buraya dönebilirsiniz.',
            'tour-welcome-title': 'Hoş Geldiniz Mesajı',
            'tour-welcome-content': 'Hazırsınız! Kullanıcı adınız burada görüntülenir. Artık size sunulan tüm platformları ve özellikleri keşfedebilirsiniz.',
            'tour-previous': 'Önceki',
            'tour-next': 'Sonraki',
            'tour-finish': 'Bitir',
            'tour-restart': 'Turu Yeniden Başlat',
            'tour-skip': 'Atla'
        },
        fr: {
            // Login page
            'welcome': 'Bienvenue sur Final Global',
            'sign-in-continue': 'Connectez-vous pour continuer vers votre compte',
            'email': 'Nom d\'utilisateur',
            'enter-username': 'Entrez votre nom d\'utilisateur',
            'password': 'Mot de passe',
            'enter-password': 'Entrez votre mot de passe',
            'sign-in': 'Se connecter',
            
            // Main page
            'welcome-to-platform': 'Final Global',
            'welcome-user': 'Bienvenue, ',
            'logout': 'Se déconnecter',
            'please-login': 'Veuillez vous connecter',
            'need-login': 'Vous devez vous connecter pour accéder aux plateformes.',
            'go-to-login': 'Aller à la page de connexion',
            'select-platform': 'Sélectionnez une plateforme',
            'choose-platform': 'Choisissez l\'une des plateformes suivantes pour y accéder :',
            'access-platform': 'Accéder à la plateforme',
            'Open': 'Ouvrir',
            'copyright': '© 2025 Système Final Global',
            'notifications': 'Notifications',
            'notifications-loading': 'Chargement...',
            'notifications-none': 'Aucune notification.',
            'notifications-error': 'Erreur lors du chargement des notifications.',
            'notifications-delete-confirm': 'Supprimer cette notification ?',
            'notifications-delete-failed': 'Échec de la suppression de la notification.',
            'options': 'Options',
            'light-mode': 'Mode clair',
            'dark-mode': 'Mode sombre',
            
            // Tour translations
            'tour-notifications-title': 'Centre de Notifications',
            'tour-notifications-content': 'Bienvenue dans votre centre de notifications ! Vous verrez ici les mises à jour importantes de toutes vos plateformes connectées, y compris RMS, Portail de Congés, SIS et LMS.',
            'tour-platforms-title': 'Accès aux Plateformes',
            'tour-platforms-content': 'C\'est ici que vous pouvez accéder à toutes vos plateformes universitaires. Cliquez sur n\'importe quelle carte de plateforme pour l\'ouvrir dans un nouvel onglet.',
            'tour-settings-title': 'Paramètres Utilisateur',
            'tour-settings-content': 'Cliquez ici pour accéder aux paramètres de votre compte, changer de langue, basculer en mode sombre ou vous déconnecter.',
            'tour-navigation-title': 'Navigation des Plateformes',
            'tour-navigation-content': 'Ceci est votre hub central pour accéder à tous les systèmes universitaires. Vous pouvez toujours revenir ici pour basculer entre les plateformes.',
            'tour-welcome-title': 'Message de Bienvenue',
            'tour-welcome-content': 'Vous êtes prêt ! Votre nom d\'utilisateur s\'affiche ici. Vous pouvez maintenant explorer toutes les plateformes et fonctionnalités disponibles.',
            'tour-previous': 'Précédent',
            'tour-next': 'Suivant',
            'tour-finish': 'Terminer',
            'tour-restart': 'Redémarrer le Tour',
            'tour-skip': 'Passer'
        },
        ru: {
            // Login page
            'welcome': 'Добро пожаловать в Final Global',
            'sign-in-continue': 'Войдите, чтобы продолжить к вашему аккаунту',
            'email': 'Имя пользователя',
            'enter-username': 'Введите ваше имя пользователя',
            'password': 'Пароль',
            'enter-password': 'Введите ваш пароль',
            'sign-in': 'Войти',
            
            // Main page
            'welcome-to-platform': 'Final Global',
            'welcome-user': 'Добро пожаловать, ',
            'logout': 'Выйти',
            'please-login': 'Пожалуйста, войдите',
            'need-login': 'Вам необходимо войти для доступа к платформам.',
            'go-to-login': 'Перейти на страницу входа',
            'select-platform': 'Выберите платформу',
            'choose-platform': 'Выберите одну из следующих платформ для доступа:',
            'access-platform': 'Доступ к платформе',
            'Open': 'Открыть',
            'copyright': '© 2025 Система Final Global',
            'notifications': 'Уведомления',
            'notifications-loading': 'Загрузка...',
            'notifications-none': 'Нет уведомлений.',
            'notifications-error': 'Ошибка загрузки уведомлений.',
            'notifications-delete-confirm': 'Удалить это уведомление?',
            'notifications-delete-failed': 'Не удалось удалить уведомление.',
            'options': 'Опции',
            'light-mode': 'Светлый режим',
            'dark-mode': 'Темный режим',
            
            // Tour translations
            'tour-notifications-title': 'Центр Уведомлений',
            'tour-notifications-content': 'Добро пожаловать в центр уведомлений! Здесь вы увидите важные обновления со всех подключенных платформ, включая RMS, Портал отпусков, SIS и LMS.',
            'tour-platforms-title': 'Доступ к Платформам',
            'tour-platforms-content': 'Здесь вы можете получить доступ ко всем университетским платформам. Нажмите на любую карточку платформы, чтобы открыть её в новой вкладке.',
            'tour-settings-title': 'Настройки Пользователя',
            'tour-settings-content': 'Нажмите здесь, чтобы получить доступ к настройкам аккаунта, изменить язык, переключить темный режим или выйти.',
            'tour-navigation-title': 'Навигация по Платформам',
            'tour-navigation-content': 'Это ваш центральный хаб для доступа ко всем университетским системам. Вы всегда можете вернуться сюда, чтобы переключаться между платформами.',
            'tour-welcome-title': 'Приветственное Сообщение',
            'tour-welcome-content': 'Вы готовы! Ваше имя пользователя отображается здесь. Теперь вы можете исследовать все доступные платформы и функции.',
            'tour-previous': 'Предыдущий',
            'tour-next': 'Следующий',
            'tour-finish': 'Завершить',
            'tour-restart': 'Перезапустить Тур',
            'tour-skip': 'Пропустить'
        },
        ar: {
            // Login page
            'welcome': 'مرحباً بك في Final Global',
            'sign-in-continue': 'سجل دخولك للمتابعة إلى حسابك',
            'email': 'اسم المستخدم',
            'enter-username': 'أدخل اسم المستخدم الخاص بك',
            'password': 'كلمة المرور',
            'enter-password': 'أدخل كلمة المرور الخاصة بك',
            'sign-in': 'تسجيل الدخول',
            
            // Main page
            'welcome-to-platform': 'Final Global',
            'welcome-user': 'مرحباً بك، ',
            'logout': 'تسجيل الخروج',
            'please-login': 'يرجى تسجيل الدخول',
            'need-login': 'تحتاج إلى تسجيل الدخول للوصول إلى المنصات.',
            'go-to-login': 'اذهب إلى صفحة تسجيل الدخول',
            'select-platform': 'اختر منصة',
            'choose-platform': 'اختر إحدى المنصات التالية للوصول:',
            'access-platform': 'الوصول إلى المنصة',
            'Open': 'فتح',
            'copyright': '© 2025 نظام Final Global',
            'notifications': 'الإشعارات',
            'notifications-loading': 'جاري التحميل...',
            'notifications-none': 'لا توجد إشعارات.',
            'notifications-error': 'خطأ في تحميل الإشعارات.',
            'notifications-delete-confirm': 'حذف هذا الإشعار؟',
            'notifications-delete-failed': 'فشل في حذف الإشعار.',
            'options': 'الخيارات',
            'light-mode': 'الوضع الفاتح',
            'dark-mode': 'الوضع الداكن',
            
            // Tour translations
            'tour-notifications-title': 'مركز الإشعارات',
            'tour-notifications-content': 'مرحباً بك في مركز الإشعارات! ستجد هنا التحديثات المهمة من جميع المنصات المتصلة بما في ذلك RMS، بوابة الإجازات، SIS و LMS.',
            'tour-platforms-title': 'الوصول إلى المنصات',
            'tour-platforms-content': 'هذا هو المكان الذي يمكنك من خلاله الوصول إلى جميع منصات الجامعة. انقر على أي بطاقة منصة لفتحها في تبويب جديد.',
            'tour-settings-title': 'إعدادات المستخدم',
            'tour-settings-content': 'انقر هنا للوصول إلى إعدادات حسابك، تغيير اللغة، تبديل الوضع الداكن أو تسجيل الخروج.',
            'tour-navigation-title': 'تنقل المنصات',
            'tour-navigation-content': 'هذا هو المحور المركزي للوصول إلى جميع أنظمة الجامعة. يمكنك دائمًا العودة هنا للتبديل بين المنصات.',
            'tour-welcome-title': 'رسالة الترحيب',
            'tour-welcome-content': 'أنت جاهز! اسم المستخدم الخاص بك معروض هنا. يمكنك الآن استكشاف جميع المنصات والميزات المتاحة لك.',
            'tour-previous': 'السابق',
            'tour-next': 'التالي',
            'tour-finish': 'إنهاء',
            'tour-restart': 'إعادة تشغيل الجولة',
            'tour-skip': 'تخطي'
        }
    };
    
    // Initialize language
    let currentLang = localStorage.getItem('language') || 'en';
    updateLanguageDisplay();
    applyTranslations();
    
    // Add event listener for language button
    if (languageBtn) {
        languageBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('Language button clicked');
            console.log('Language dropdown element:', languageDropdown);
            console.log('Language dropdown classes:', languageDropdown ? languageDropdown.className : 'null');
            // Toggle dropdown visibility
            languageDropdown.classList.toggle('show');
            console.log('Language dropdown classes after toggle:', languageDropdown ? languageDropdown.className : 'null');
        });
    } else {
        console.log('Language button not found');
    }

    // Add event listeners for language options
    if (languageOptions) {
        console.log('Found language options:', languageOptions.length);
        languageOptions.forEach((option, index) => {
            console.log(`Language option ${index}:`, option.textContent, option.getAttribute('data-lang'));
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const selectedLang = this.getAttribute('data-lang');
                console.log('Language option clicked:', selectedLang);
                setLanguage(selectedLang);
                languageDropdown.classList.remove('show');
            });
        });
    } else {
        console.log('No language options found');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.user-dropdown') && !event.target.closest('.language-selector')) {
            languageDropdown.classList.remove('show');
        }
    });
    
    // Expose translations globally for main.js
    window.translations = translations;

    // Dispatch languageChanged event when language changes
    function setLanguage(lang) {
        console.log('setLanguage called with:', lang);
        currentLang = lang;
        localStorage.setItem('language', lang);
        updateLanguageDisplay();
        applyTranslations();
        console.log('Dispatching languageChanged event');
        window.dispatchEvent(new Event('languageChanged'));
        console.log('Language change complete');
    }
    
    // Function to update language display
    function updateLanguageDisplay() {
        if (currentLanguageSpan) {
            if (currentLang === 'en') {
                currentLanguageSpan.textContent = 'English';
            } else if (currentLang === 'tr') {
                currentLanguageSpan.textContent = 'Türkçe';
            } else if (currentLang === 'fr') {
                currentLanguageSpan.textContent = 'Français';
            } else if (currentLang === 'ru') {
                currentLanguageSpan.textContent = 'Русский';
            } else if (currentLang === 'ar') {
                currentLanguageSpan.textContent = 'العربية';
            }
        }
    }
    
    // Function to apply translations
    function applyTranslations() {
        console.log('Applying translations for language:', currentLang);
        
        // Get all elements with data-translate attribute
        const elements = document.querySelectorAll('[data-translate]');
        console.log('Found elements with data-translate:', elements.length);
        
        elements.forEach(element => {
            const key = element.getAttribute('data-translate');
            console.log('Translating element with key:', key);
            if (translations[currentLang][key]) {
                // If it's an input with placeholder
                if (element.hasAttribute('placeholder')) {
                    element.setAttribute('placeholder', translations[currentLang][key]);
                } 
                // If it's a button or input with value
                else if (element.hasAttribute('value')) {
                    element.setAttribute('value', translations[currentLang][key]);
                } 
                // For all other elements
                else {
                    element.textContent = translations[currentLang][key];
                }
                console.log('Applied translation for key:', key, 'Value:', translations[currentLang][key]);
            } else {
                console.log('No translation found for key:', key);
            }
        });
        
        // Special case for username welcome message
        const usernameElement = document.getElementById('username');
        if (usernameElement && usernameElement.textContent) {
            // Check for welcome messages in all languages
            const welcomePatterns = {
                'en': 'Welcome, ',
                'tr': 'Hoş geldiniz, ',
                'fr': 'Bienvenue, ',
                'ru': 'Добро пожаловать, ',
                'ar': 'مرحباً بك، '
            };
            
            let username = usernameElement.textContent;
            let foundWelcome = false;
            
            for (const lang in welcomePatterns) {
                if (username.includes(welcomePatterns[lang])) {
                    username = username.replace(welcomePatterns[lang], '');
                    foundWelcome = true;
                    break;
                }
            }
            
            if (foundWelcome) {
                usernameElement.textContent = translations[currentLang]['welcome-user'] + username;
            }
        }
        
        console.log('Translation application complete');
    }
});