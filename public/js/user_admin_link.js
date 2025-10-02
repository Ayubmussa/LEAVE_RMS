(function() {
  const ORIGIN = window.location.origin;
  // Avoid global collisions; reuse global ADMIN_API_BASE_URL if defined
  const ADMIN_API = (typeof window !== 'undefined' && window.ADMIN_API_BASE_URL)
    ? window.ADMIN_API_BASE_URL
    : `${ORIGIN}/LEAVE_RMS/database/admin_api.php`;

document.addEventListener('DOMContentLoaded', function() {
  const adminBtn = document.getElementById('admin-panel-btn');
  if (!adminBtn) return;

  function hide() { adminBtn.style.display = 'none'; }
  function show() { adminBtn.style.display = 'block'; }

  // Prefer existing adminSession; if not present, verify by username
  const adminSessionRaw = localStorage.getItem('adminSession');
  let adminFound = false;
  try {
    const admin = adminSessionRaw ? JSON.parse(adminSessionRaw) : null;
    if (admin && admin.role) {
      adminFound = true;
      show();
    }
  } catch (_) {}

  if (!adminFound) {
    // Verify against backend by username
    const userRaw = localStorage.getItem('user');
    try {
      const user = userRaw ? JSON.parse(userRaw) : null;
      if (user && user.username) {
        fetch(`${ADMIN_API}?endpoint=admin-by-username&username=${encodeURIComponent(user.username)}`)
          .then(r => r.json())
          .then(data => {
            if (data && data.success && data.admin) {
              // Cache adminSession for later and show button
              localStorage.setItem('adminSession', JSON.stringify(data.admin));
              show();
            } else {
              hide();
            }
          })
          .catch(() => hide());
      } else {
        hide();
      }
    } catch (_) {
      hide();
    }
  }

  // Navigate to admin panel
  adminBtn.addEventListener('click', function(e) {
    e.preventDefault();
    window.location.href = 'admin-panel.html';
  });
});

})();


