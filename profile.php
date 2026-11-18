<?php
session_name("employee");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "employee") {
    $em = "First login";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/user.php";

$user = get_user_by_id($conn, $_SESSION['id']);

if ($user == 0) {
    $em = "User not found.";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

$initial = strtoupper(substr((string)$user['full_name'], 0, 1) ?: '?');

$joinedDisplay = '—';
if (!empty($user['created_at']) && $user['created_at'] !== '0000-00-00 00:00:00') {
    $ts = strtotime($user['created_at']);
    if ($ts !== false) {
        $joinedDisplay = date('M j, Y', $ts);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile · <?= htmlspecialchars($user['full_name']) ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:            #f4f6fb;
            --surface:       #ffffff;
            --surface-alt:   #f8fafc;
            --border:        #e5e9f0;
            --text:          #1e293b;
            --text-muted:    #64748b;
            --text-subtle:   #94a3b8;

            --primary:       #4f46e5;
            --primary-hover: #4338ca;
            --primary-soft:  #eef2ff;

            --success:       #10b981;
            --success-soft:  #ecfdf5;
            --warning:       #f59e0b;
            --warning-soft:  #fffbeb;
            --danger:        #ef4444;
            --danger-soft:   #fef2f2;
            --info:          #0ea5e9;
            --info-soft:     #f0f9ff;
            --neutral-soft:  #f1f5f9;

            --radius:        14px;
            --radius-sm:     10px;
            --shadow-sm:     0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-md:     0 4px 16px rgba(15, 23, 42, 0.06);
            --transition:    0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }

        /* ============ LAYOUT ============ */
        .app-shell { min-height: 100vh; }

        .app-main {
            margin-left: 260px;
            padding: 28px 32px 48px;
            max-width: 1400px;
            width: calc(100% - 260px);
        }

        /* ============ PAGE HEADER ============ */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--text-subtle);
            margin-bottom: 8px;
        }
        .breadcrumb a { color: var(--text-muted); font-weight: 500; transition: color var(--transition); }
        .breadcrumb a:hover { color: var(--primary); }
        .breadcrumb svg { width: 12px; height: 12px; }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 4px;
        }

        .page-actions { display: flex; gap: 8px; flex-wrap: wrap; }

        /* ============ BUTTONS ============ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
        }
        .btn svg { width: 14px; height: 14px; }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-ghost {
            background: var(--surface);
            color: var(--text);
            border-color: var(--border);
        }
        .btn-ghost:hover { background: var(--surface-alt); }

        /* ============ ALERTS ============ */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
            border: 1px solid;
        }
        .alert svg { flex-shrink: 0; width: 16px; height: 16px; }
        .alert-success {
            background: var(--success-soft);
            border-color: #a7f3d0;
            color: #047857;
        }
        .alert-danger {
            background: var(--danger-soft);
            border-color: #fecaca;
            color: #b91c1c;
        }

        /* ============ PROFILE CARD ============ */
        .profile-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        /* Header band with avatar */
        .profile-header {
            position: relative;
            padding: 32px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            background:
                radial-gradient(circle at top right, rgba(79, 70, 229, 0.10), transparent 60%),
                linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--border);
        }

        .profile-avatar {
            width: 76px;
            height: 76px;
            border-radius: 22px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 30px;
            font-weight: 800;
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.32);
            flex-shrink: 0;
            overflow: hidden;
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }

        .profile-identity { flex: 1 1 240px; min-width: 0; }

        .profile-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
            word-break: break-word;
        }

        .profile-handle {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .profile-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .profile-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .profile-chip svg { width: 11px; height: 11px; }

        .profile-chip.tone-success { background: var(--success-soft); color: #047857; border-color: #a7f3d0; }
        .profile-chip.tone-primary { background: var(--primary-soft); color: var(--primary); border-color: #c7d2fe; }

        .profile-chip::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .profile-chip.no-dot::before { display: none; }

        /* Details body */
        .profile-body { padding: 8px 32px 32px; }

        .profile-section-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 24px 0 12px;
            margin: 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 4px;
        }

        .detail-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0;
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px 0;
            border-bottom: 1px solid var(--border);
        }

        /* Remove divider for last row on each column side */
        .detail-row:nth-last-child(-n+2) { border-bottom: none; }

        .detail-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .detail-icon svg { width: 17px; height: 17px; }

        .detail-content { min-width: 0; flex: 1; }

        .detail-label {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--text);
            word-break: break-word;
            line-height: 1.45;
        }

        .detail-value.is-muted {
            color: var(--text-subtle);
            font-weight: 500;
            font-style: italic;
        }

        /* ============ META STRIP ============ */
        .meta-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 20px 32px;
            background: var(--surface-alt);
            border-top: 1px solid var(--border);
        }
        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .meta-pill svg { width: 12px; height: 12px; }

        /* ============ FOOTER ============ */
        .page-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-subtle);
            font-size: 12px;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .detail-list { grid-template-columns: 1fr; }
            .detail-row:nth-last-child(-n+2) { border-bottom: 1px solid var(--border); }
            .detail-row:last-child { border-bottom: none; }
        }

        @media (max-width: 960px) {
            .app-main {
                margin-left: 0;
                width: 100%;
                padding: 76px 16px 40px;
            }
            .page-title { font-size: 22px; }
            .profile-header { padding: 24px 20px; }
            .profile-body { padding: 8px 20px 24px; }
            .meta-strip { padding: 16px 20px; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php include "inc/nav-user.php"; ?>

    <main class="app-main">

        <!-- ============ PAGE HEADER ============ -->
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <a href="index-user.php">Dashboard</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span>Profile</span>
                </div>
                <h1 class="page-title">My Profile</h1>
                <p class="page-subtitle">Your account details and contact information.</p>
            </div>
            <div class="page-actions">
                <a href="index-user.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Dashboard
                </a>
                <a href="edit_profile.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit Profile
                </a>
            </div>
        </div>

        <!-- ============ ALERTS ============ -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= htmlspecialchars(stripcslashes($_GET['error'])) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <?= htmlspecialchars(stripcslashes($_GET['success'])) ?>
            </div>
        <?php endif; ?>

        <!-- ============ PROFILE CARD ============ -->
        <div class="profile-card">

            <!-- Header band -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])): ?>
                        <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="">
                    <?php else: ?>
                        <?= htmlspecialchars($initial) ?>
                    <?php endif; ?>
                </div>

                <div class="profile-identity">
                    <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
                    <div class="profile-handle">@<?= htmlspecialchars($user['username']) ?></div>
                    <div class="profile-chips">
                        <span class="profile-chip tone-success">Employee</span>
                        <?php if (!empty($user['contact'])): ?>
                            <span class="profile-chip no-dot">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <?= htmlspecialchars($user['contact']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="profile-body">
                <p class="profile-section-title">Account details</p>

                <div class="detail-list">

                    <div class="detail-row">
                        <div class="detail-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?= htmlspecialchars($user['full_name']) ?></div>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="5"/>
                                <path d="M20 21a8 8 0 0 0-16 0"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Username</div>
                            <div class="detail-value"><?= htmlspecialchars($user['username']) ?></div>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Contact</div>
                            <?php if (!empty($user['contact'])): ?>
                                <div class="detail-value"><?= htmlspecialchars($user['contact']) ?></div>
                            <?php else: ?>
                                <div class="detail-value is-muted">Not provided</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-row">
                        <div class="detail-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                        <div class="detail-content">
                            <div class="detail-label">Joined</div>
                            <?php if ($joinedDisplay !== '—'): ?>
                                <div class="detail-value"><?= htmlspecialchars($joinedDisplay) ?></div>
                            <?php else: ?>
                                <div class="detail-value is-muted">Unknown</div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Meta strip -->
            <div class="meta-strip">
                <span class="meta-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    User #<?= (int)$user['id'] ?>
                </span>
                <?php if (!empty($user['department'])): ?>
                    <span class="meta-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l8-4v18"/>
                            <path d="M19 21V11l-6-4"/>
                        </svg>
                        Department #<?= (int)$user['department'] ?>
                    </span>
                <?php endif; ?>
            </div>

        </div>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>
</body>
</html>