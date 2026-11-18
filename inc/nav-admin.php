<?php
// Standalone department-admin sidebar – safe to include from any admin page
$currentPage = basename($_SERVER['PHP_SELF']);
$navUser     = $_SESSION['username']   ?? 'admin';
$navRole     = $_SESSION['role']       ?? 'admin';
$navDept     = $_SESSION['department'] ?? null;

// Optional: resolve department name for display
$navDepartmentName = null;
if ($navDept !== null && isset($conn)) {
    // $conn is available on pages that include this after DB_connection.php
    if (function_exists('get_department_by_id')) {
        $__dept = get_department_by_id($conn, (int)$navDept);
        if ($__dept && $__dept != 0) {
            $navDepartmentName = $__dept['name'];
        }
    }
}

// Helper: mark link active
function nav_admin_active($pages, $current) {
    foreach ((array)$pages as $p) {
        if ($p === $current) return ' is-active';
    }
    return '';
}
?>
<style>
/* ============================================================
   SCOPED SIDEBAR STYLES  –  prefix: tma-
   ============================================================ */
.tma-sidebar {
    --tma-primary:      #4f46e5;
    --tma-primary-soft: #eef2ff;
    --tma-surface:      #ffffff;
    --tma-surface-alt:  #f8fafc;
    --tma-border:       #e5e9f0;
    --tma-text:         #1e293b;
    --tma-text-muted:   #64748b;
    --tma-text-subtle:  #94a3b8;
    --tma-danger:       #ef4444;
    --tma-danger-soft:  #fef2f2;

    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 260px;
    height: 100vh;
    background: var(--tma-surface);
    border-right: 1px solid var(--tma-border);
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    overflow-y: auto;
    overscroll-behavior: contain;
    z-index: 900;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    color: var(--tma-text);
    box-sizing: border-box;
}

.tma-sidebar *,
.tma-sidebar *::before,
.tma-sidebar *::after { box-sizing: border-box; }

/* ---------- User card ---------- */
.tma-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--tma-border);
    border-radius: 14px;
    margin-bottom: 16px;
}

.tma-avatar {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%);
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 700;
    font-size: 15px;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);
    overflow: hidden;
}

.tma-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
}

.tma-user-info { min-width: 0; }

.tma-user-name {
    font-weight: 700;
    font-size: 14px;
    color: var(--tma-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    letter-spacing: -0.01em;
}

.tma-user-role {
    font-size: 11px;
    color: var(--tma-text-muted);
    font-weight: 500;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tma-user-role .dept-dot {
    display: inline-block;
    width: 5px;
    height: 5px;
    background: var(--tma-danger);
    border-radius: 50%;
    margin-right: 5px;
    vertical-align: middle;
}

/* ---------- Nav list ---------- */
.tma-nav {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.tma-nav li { margin: 0; }

.tma-nav a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    color: var(--tma-text-muted);
    text-decoration: none;
    font-weight: 500;
    font-size: 13.5px;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.tma-nav a:hover {
    background: var(--tma-surface-alt);
    color: var(--tma-text);
}

.tma-nav a.is-active {
    background: var(--tma-primary-soft);
    color: var(--tma-primary);
    font-weight: 600;
}

.tma-nav a.is-active::before {
    content: "";
    position: absolute;
    left: -16px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 22px;
    background: var(--tma-primary);
    border-radius: 0 3px 3px 0;
}

.tma-nav a.tma-logout:hover {
    background: var(--tma-danger-soft);
    color: var(--tma-danger);
}

/* ---------- Icons ---------- */
.tma-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    stroke-width: 2;
}

/* ---------- Section label ---------- */
.tma-section-label {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--tma-text-subtle);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 16px 14px 6px;
    margin: 0;
}

/* ---------- Mobile toggle ---------- */
.tma-mobile-toggle {
    display: none;
    position: fixed;
    top: 16px;
    left: 16px;
    z-index: 1001;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--tma-surface);
    border: 1px solid var(--tma-border);
    color: var(--tma-text);
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    align-items: center;
    justify-content: center;
    padding: 0;
}

.tma-mobile-toggle svg { width: 20px; height: 20px; }

.tma-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 899;
    backdrop-filter: blur(2px);
}

/* ---------- Responsive ---------- */
@media (max-width: 960px) {
    .tma-sidebar {
        transform: translateX(-100%);
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 4px 0 24px rgba(15, 23, 42, 0.12);
    }
    .tma-sidebar.is-open { transform: translateX(0); }
    .tma-mobile-toggle { display: flex; }
    .tma-backdrop.is-open { display: block; }
}

/* ---------- Reduced motion ---------- */
@media (prefers-reduced-motion: reduce) {
    .tma-sidebar, .tma-nav a { transition: none; }
}
</style>

<!-- Mobile hamburger -->
<button class="tma-mobile-toggle" id="tmaMenuToggle" aria-label="Open navigation" aria-controls="tmaSidebar" aria-expanded="false">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
</button>

<div class="tma-backdrop" id="tmaBackdrop"></div>

<nav class="tma-sidebar" id="tmaSidebar" aria-label="Admin navigation">

    <!-- User card -->
    <div class="tma-user">
        <div class="tma-avatar">
            <?php if (!empty($_SESSION['profile_pic']) && file_exists($_SESSION['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['profile_pic']) ?>" alt="">
            <?php else: ?>
                <?= htmlspecialchars(strtoupper(substr($navUser, 0, 1))) ?>
            <?php endif; ?>
        </div>
        <div class="tma-user-info">
            <div class="tma-user-name">@<?= htmlspecialchars($navUser) ?></div>
            <div class="tma-user-role">
                <?php if ($navDepartmentName): ?>
                    <span class="dept-dot"></span><?= htmlspecialchars($navDepartmentName) ?>
                <?php else: ?>
                    Department Admin
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ================= WORKSPACE ================= -->
    <p class="tma-section-label">Workspace</p>
    <ul class="tma-nav">
        <li>
            <a href="index-admin.php" class="<?= trim(nav_admin_active(['index-admin.php'], $currentPage)) ?>">
                <svg class="tma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                    <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                    <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                </svg>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="task-department.php" class="<?= trim(nav_admin_active('task-department.php', $currentPage)) ?>">
                <svg class="tma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <span>All Tasks</span>
            </a>
        </li>
    </ul>

    <!-- ================= TEAM ================= -->
    <p class="tma-section-label">Team</p>
    <ul class="tma-nav">
        <li>
            <a href="user-department.php" class="<?= trim(nav_admin_active('user-department.php', $currentPage)) ?>">
                <svg class="tma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Manage Users</span>
            </a>
        </li>
        <li>
            <a href="create_task-department.php" class="<?= trim(nav_admin_active(['create_task-department.php', 'edit-task-department.php'], $currentPage)) ?>">
                <svg class="tma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                <span>Create Task</span>
            </a>
        </li>
    </ul>

    <!-- ================= LOGOUT ================= -->
    <ul class="tma-nav">
        <li>
            <a href="logout.php" class="tma-logout">
                <svg class="tma-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
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
    const toggle   = document.getElementById('tmaMenuToggle');
    const sidebar  = document.getElementById('tmaSidebar');
    const backdrop = document.getElementById('tmaBackdrop');
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