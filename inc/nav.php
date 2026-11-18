<?php
// Standalone sidebar – safe to include from any page
$currentPage = basename($_SERVER['PHP_SELF']);
$navUser     = $_SESSION['username'] ?? 'guest';
$navRole     = $_SESSION['role'] ?? 'employee';

// Helper: mark link active
function nav_active($pages, $current) {
    foreach ((array)$pages as $p) {
        if ($p === $current) return ' is-active';
    }
    return '';
}
?>
<style>
/* ============================================================
   SCOPED SIDEBAR STYLES  –  prefix: tm-
   ============================================================ */
.tm-sidebar {
    --tm-primary:      #4f46e5;
    --tm-primary-soft: #eef2ff;
    --tm-surface:      #ffffff;
    --tm-surface-alt:  #f8fafc;
    --tm-border:       #e5e9f0;
    --tm-text:         #1e293b;
    --tm-text-muted:   #64748b;
    --tm-text-subtle:  #94a3b8;
    --tm-danger:       #ef4444;
    --tm-danger-soft:  #fef2f2;

    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 260px;
    height: 100vh;
    background: var(--tm-surface);
    border-right: 1px solid var(--tm-border);
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    overflow-y: auto;          /* only the sidebar itself scrolls if it's taller than the viewport */
    overscroll-behavior: contain;
    z-index: 900;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    color: var(--tm-text);
    box-sizing: border-box;
}

.tm-sidebar *,
.tm-sidebar *::before,
.tm-sidebar *::after { box-sizing: border-box; }

/* ---------- User card ---------- */
.tm-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--tm-border);
    border-radius: 14px;
    margin-bottom: 16px;
}

.tm-avatar {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--tm-primary) 0%, #7c3aed 100%);
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 700;
    font-size: 15px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);
    overflow: hidden;
}

.tm-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
}

.tm-user-info { min-width: 0; }

.tm-user-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--tm-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -0.01em;
}

.tm-user-role {
    font-size: 11px;
    color: var(--tm-text-muted);
    text-transform: capitalize;
    font-weight: 500;
    margin-top: 2px;
}

/* ---------- Nav list ---------- */
.tm-nav {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.tm-nav li { margin: 0; }

.tm-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    color: var(--tm-text-muted);
    text-decoration: none;
    font-weight: 500;
    font-size: 13.5px;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.tm-nav a:hover {
    background: var(--tm-surface-alt);
    color: var(--tm-text);
}

.tm-nav a.is-active {
    background: var(--tm-primary-soft);
    color: var(--tm-primary);
    font-weight: 600;
}

.tm-nav a.is-active::before {
    content: "";
    position: absolute;
    left: -16px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 22px;
    background: var(--tm-primary);
    border-radius: 0 3px 3px 0;
}

.tm-nav a.tm-logout:hover {
    background: var(--tm-danger-soft);
    color: var(--tm-danger);
}

/* ---------- Icons ---------- */
.tm-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    stroke-width: 2;
}

/* ---------- Section label ---------- */
.tm-section-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--tm-text-subtle);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 16px 14px 6px;
    margin: 0;
}

/* ---------- Mobile toggle ---------- */
.tm-mobile-toggle {
    display: none;
    position: fixed;
    top: 16px;
    left: 16px;
    z-index: 1001;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--tm-surface);
    border: 1px solid var(--tm-border);
    color: var(--tm-text);
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    align-items: center;
    justify-content: center;
    padding: 0;
}

.tm-mobile-toggle svg { width: 20px; height: 20px; }

.tm-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 999;
    backdrop-filter: blur(2px);
}

/* ---------- Responsive ---------- */
@media (max-width: 960px) {
    .tm-sidebar {
        transform: translateX(-100%);
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 4px 0 24px rgba(15, 23, 42, 0.12);
    }
    .tm-sidebar.is-open { transform: translateX(0); }
    .tm-mobile-toggle { display: flex; }
    .tm-backdrop.is-open { display: block; }
}

/* ---------- Reduced motion ---------- */
@media (prefers-reduced-motion: reduce) {
    .tm-sidebar, .tm-nav a { transition: none; }
}
</style>

<!-- Mobile hamburger -->
<button class="tm-mobile-toggle" id="tmMenuToggle" aria-label="Open navigation" aria-controls="tmSidebar" aria-expanded="false">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<div class="tm-backdrop" id="tmBackdrop"></div>

<nav class="tm-sidebar" id="tmSidebar" aria-label="Main navigation">

    <!-- User card -->
    <div class="tm-user">
        <div class="tm-avatar">
            <?php if (!empty($_SESSION['profile_pic']) && file_exists($_SESSION['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['profile_pic']) ?>" alt="">
            <?php else: ?>
                <?= htmlspecialchars(strtoupper(substr($navUser, 0, 1))) ?>
            <?php endif; ?>
        </div>
        <div class="tm-user-info">
            <div class="tm-user-name">@<?= htmlspecialchars($navUser) ?></div>
            <div class="tm-user-role"><?= htmlspecialchars($navRole) ?></div>
        </div>
    </div>

    <?php if ($navRole === 'employee'): ?>
        <!-- ================= EMPLOYEE NAV ================= -->
        <p class="tm-section-label">Workspace</p>
        <ul class="tm-nav">
            <li>
                <a href="index.php" class="<?= trim(nav_active(['index.php', 'dashboard.php'], $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="my-task.php" class="<?= trim(nav_active('my-task.php', $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    <span>My Tasks</span>
                </a>
            </li>
            <li>
                <a href="notifications.php" class="<?= trim(nav_active('notifications.php', $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span>Notifications</span>
                </a>
            </li>
            <li>
                <a href="profile.php" class="<?= trim(nav_active('profile.php', $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Profile</span>
                </a>
            </li>
        </ul>
    <?php else: ?>
        <!-- ================= ADMIN NAV ================= -->
        <p class="tm-section-label">Overview</p>
        <ul class="tm-nav">
            <li>
                <a href="index.php" class="<?= trim(nav_active(['index.php', 'dashboard.php'], $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="task.php" class="<?= trim(nav_active('task.php', $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    <span>All Tasks</span>
                </a>
            </li>
        </ul>

        <p class="tm-section-label">Management</p>
        <ul class="tm-nav">
            <li>
                <a href="user.php" class="<?= trim(nav_active('user.php', $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Manage Users</span>
                </a>
            </li>
            <li>
                <a href="department.php" class="<?= trim(nav_active('department.php', $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="2" width="6" height="5" rx="1"/>
                        <rect x="2" y="17" width="6" height="5" rx="1"/>
                        <rect x="16" y="17" width="6" height="5" rx="1"/>
                        <path d="M12 7v3"/>
                        <path d="M5 17v-3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3"/>
                    </svg>
                    <span>Departments</span>
                </a>
            </li>
            <li>
                <a href="create_task.php" class="<?= trim(nav_active(['create_task.php', 'edit-task.php'], $currentPage)) ?>">
                    <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    <span>Create Task</span>
                </a>
            </li>
        </ul>
    <?php endif; ?>

    <!-- ---------- Logout ---------- -->
    <ul class="tm-nav">
        <li>
            <a href="logout.php" class="tm-logout">
                <svg class="tm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
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
    const toggle   = document.getElementById('tmMenuToggle');
    const sidebar  = document.getElementById('tmSidebar');
    const backdrop = document.getElementById('tmBackdrop');
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

    // Close on ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeMenu();
    });

    // Close when a link is clicked (mobile only)
    sidebar.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
            if (window.innerWidth <= 960) closeMenu();
        });
    });

    // Reset on resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) closeMenu();
    });
})();
</script>