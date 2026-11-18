<?php
// Standalone employee sidebar – safe to include from any employee page
$currentPage = basename($_SERVER['PHP_SELF']);
$navUser     = $_SESSION['username'] ?? 'employee';

// Helper: mark link active
function nav_user_active($pages, $current) {
    foreach ((array)$pages as $p) {
        if ($p === $current) return ' is-active';
    }
    return '';
}
?>
<style>
/* ============================================================
   SCOPED SIDEBAR STYLES  –  prefix: tmu-  (user)
   ============================================================ */
.tmu-sidebar {
    --tmu-primary:      #4f46e5;
    --tmu-primary-soft: #eef2ff;
    --tmu-surface:      #ffffff;
    --tmu-surface-alt:  #f8fafc;
    --tmu-border:       #e5e9f0;
    --tmu-text:         #1e293b;
    --tmu-text-muted:   #64748b;
    --tmu-text-subtle:  #94a3b8;
    --tmu-danger:       #ef4444;
    --tmu-danger-soft:  #fef2f2;
    --tmu-success:      #10b981;
    --tmu-success-soft: #ecfdf5;

    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 260px;
    height: 100vh;
    background: var(--tmu-surface);
    border-right: 1px solid var(--tmu-border);
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    overflow-y: auto;
    overscroll-behavior: contain;
    z-index: 900;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    color: var(--tmu-text);
    box-sizing: border-box;
}

.tmu-sidebar *,
.tmu-sidebar *::before,
.tmu-sidebar *::after { box-sizing: border-box; }

/* ---------- User card ---------- */
.tmu-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--tmu-border);
    border-radius: 14px;
    margin-bottom: 16px;
}

.tmu-avatar {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--tmu-primary) 0%, #7c3aed 100%);
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 700;
    font-size: 15px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
    overflow: hidden;
}

.tmu-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
}

.tmu-user-info { min-width: 0; }

.tmu-user-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--tmu-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -0.01em;
}

.tmu-user-role {
    font-size: 11px;
    color: var(--tmu-text-muted);
    font-weight: 500;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tmu-user-role .role-dot {
    display: inline-block;
    width: 5px;
    height: 5px;
    background: var(--tmu-success);
    border-radius: 50%;
    margin-right: 5px;
    vertical-align: middle;
}

/* ---------- Nav list ---------- */
.tmu-nav {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.tmu-nav li { margin: 0; }

.tmu-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    color: var(--tmu-text-muted);
    text-decoration: none;
    font-weight: 500;
    font-size: 13.5px;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.tmu-nav a:hover {
    background: var(--tmu-surface-alt);
    color: var(--tmu-text);
}

.tmu-nav a.is-active {
    background: var(--tmu-primary-soft);
    color: var(--tmu-primary);
    font-weight: 600;
}

.tmu-nav a.is-active::before {
    content: "";
    position: absolute;
    left: -16px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 22px;
    background: var(--tmu-primary);
    border-radius: 0 3px 3px 0;
}

.tmu-nav a.tmu-logout:hover {
    background: var(--tmu-danger-soft);
    color: var(--tmu-danger);
}

/* ---------- Icons ---------- */
.tmu-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    stroke-width: 2;
}

/* ---------- Section label ---------- */
.tmu-section-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--tmu-text-subtle);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 16px 14px 6px;
    margin: 0;
}

/* ---------- Mobile toggle ---------- */
.tmu-mobile-toggle {
    display: none;
    position: fixed;
    top: 16px;
    left: 16px;
    z-index: 1001;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--tmu-surface);
    border: 1px solid var(--tmu-border);
    color: var(--tmu-text);
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    align-items: center;
    justify-content: center;
    padding: 0;
}

.tmu-mobile-toggle svg { width: 20px; height: 20px; }

.tmu-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 899;
    backdrop-filter: blur(2px);
}

/* ---------- Responsive ---------- */
@media (max-width: 960px) {
    .tmu-sidebar {
        transform: translateX(-100%);
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 4px 0 24px rgba(15, 23, 42, 0.12);
    }
    .tmu-sidebar.is-open { transform: translateX(0); }
    .tmu-mobile-toggle { display: flex; }
    .tmu-backdrop.is-open { display: block; }
}

/* ---------- Reduced motion ---------- */
@media (prefers-reduced-motion: reduce) {
    .tmu-sidebar, .tmu-nav a { transition: none; }
}
</style>

<!-- Mobile hamburger -->
<button class="tmu-mobile-toggle" id="tmuMenuToggle" aria-label="Open navigation" aria-controls="tmuSidebar" aria-expanded="false">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<div class="tmu-backdrop" id="tmuBackdrop"></div>

<nav class="tmu-sidebar" id="tmuSidebar" aria-label="Employee navigation">

    <!-- User card -->
    <div class="tmu-user">
        <div class="tmu-avatar">
            <?php if (!empty($_SESSION['profile_pic']) && file_exists($_SESSION['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['profile_pic']) ?>" alt="">
            <?php else: ?>
                <?= htmlspecialchars(strtoupper(substr($navUser, 0, 1))) ?>
            <?php endif; ?>
        </div>
        <div class="tmu-user-info">
            <div class="tmu-user-name">@<?= htmlspecialchars($navUser) ?></div>
            <div class="tmu-user-role">
                <span class="role-dot"></span>Employee
            </div>
        </div>
    </div>

    <!-- ================= WORKSPACE ================= -->
    <p class="tmu-section-label">Workspace</p>
    <ul class="tmu-nav">
        <li>
            <a href="index-user.php" class="<?= trim(nav_user_active('index-user.php', $currentPage)) ?>">
                <svg class="tmu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                    <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                    <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                </svg>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="my-task.php" class="<?= trim(nav_user_active('my-task.php', $currentPage)) ?>">
                <svg class="tmu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <span>My Tasks</span>
            </a>
        </li>
    </ul>

    <!-- ================= ACCOUNT ================= -->
    <p class="tmu-section-label">Account</p>
    <ul class="tmu-nav">
        <li>
            <a href="profile.php" class="<?= trim(nav_user_active('profile.php', $currentPage)) ?>">
                <svg class="tmu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="notifications.php" class="<?= trim(nav_user_active('notifications.php', $currentPage)) ?>">
                <svg class="tmu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span>Notifications</span>
            </a>
        </li>
    </ul>

    <!-- ================= LOGOUT ================= -->
    <ul class="tmu-nav">
        <li>
            <a href="logout.php" class="tmu-logout">
                <svg class="tmu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>

<script>
(function () {
    const toggle   = document.getElementById('tmuMenuToggle');
    const sidebar  = document.getElementById('tmuSidebar');
    const backdrop = document.getElementById('tmuBackdrop');
    if (!toggle || !sidebar) return;

    function openMenu() {
        sidebar.classList.add('is-open');
        backdrop.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeMenu() {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('is-open') ? closeMenu() : openMenu();
    });
    backdrop.addEventListener('click', closeMenu);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeMenu();
    });

    sidebar.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            if (window.innerWidth <= 960) closeMenu();
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) closeMenu();
    });
})();
</script>