<?php
session_name("employee");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    $em = "First login";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/Notification.php";
include "app/model/user.php";

$notifications     = get_all_my_notifications($conn, $_SESSION['id']);
$num_notification  = (int)count_my_notifications($conn, $_SESSION['id']);

/* Map notification type -> tone + icon */
function notif_type_tone($type) {
    switch (strtolower((string)$type)) {
        case 'task':      return 'primary';
        case 'deadline':  return 'warning';
        case 'completed': return 'success';
        case 'alert':     return 'danger';
        case 'info':      return 'info';
        default:          return 'neutral';
    }
}
function notif_type_icon($type) {
    switch (strtolower((string)$type)) {
        case 'task':
            return '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>';
        case 'deadline':
            return '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>';
        case 'completed':
            return '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>';
        case 'alert':
            return '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>';
        default:
            return '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications · Task Management</title>

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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 26px;
            padding: 0 10px;
            border-radius: 20px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0;
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

        /* ============ NOTIFICATION CARD ============ */
        .notif-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .notif-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            transition: background var(--transition);
            position: relative;
        }
        .notif-item:last-child { border-bottom: none; }
        .notif-item:hover { background: var(--surface-alt); }

        .notif-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .notif-icon svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .notif-icon.tone-primary { background: var(--primary-soft); color: var(--primary); }
        .notif-icon.tone-success { background: var(--success-soft); color: #047857; }
        .notif-icon.tone-warning { background: var(--warning-soft); color: #b45309; }
        .notif-icon.tone-danger  { background: var(--danger-soft);  color: #b91c1c; }
        .notif-icon.tone-info    { background: var(--info-soft);    color: #0369a1; }
        .notif-icon.tone-neutral { background: var(--neutral-soft); color: var(--text-muted); }

        .notif-body { flex: 1; min-width: 0; }

        .notif-message {
            font-size: 14px;
            color: var(--text);
            line-height: 1.55;
            word-break: break-word;
        }

        .notif-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .notif-type {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
            border: 1px solid;
        }
        .notif-type.tone-primary { background: var(--primary-soft); color: var(--primary); border-color: #c7d2fe; }
        .notif-type.tone-success { background: var(--success-soft); color: #047857; border-color: #a7f3d0; }
        .notif-type.tone-warning { background: var(--warning-soft); color: #b45309; border-color: #fde68a; }
        .notif-type.tone-danger  { background: var(--danger-soft);  color: #b91c1c; border-color: #fecaca; }
        .notif-type.tone-info    { background: var(--info-soft);    color: #0369a1; border-color: #bae6fd; }
        .notif-type.tone-neutral { background: var(--neutral-soft); color: var(--text-muted); border-color: var(--border); }

        .notif-type::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .notif-date {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: var(--text-subtle);
            font-weight: 500;
        }
        .notif-date svg { width: 12px; height: 12px; }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            padding: 72px 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            color: var(--text-subtle);
        }
        .empty-icon svg { width: 28px; height: 28px; }

        .empty-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
        }
        .empty-text {
            font-size: 13px;
            color: var(--text-muted);
            max-width: 340px;
            line-height: 1.55;
        }

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
        @media (max-width: 960px) {
            .app-main {
                margin-left: 0;
                width: 100%;
                padding: 76px 16px 40px;
            }
            .page-title { font-size: 22px; }
            .notif-item { padding: 16px 18px; }
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
                    <span>Notifications</span>
                </div>
                <h1 class="page-title">
                    All Notifications
                    <span class="count-pill"><?= $num_notification ?></span>
                </h1>
                <p class="page-subtitle">Updates about your tasks, deadlines, and account.</p>
            </div>
            <div class="page-actions">
                <a href="index-user.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- ============ ALERTS ============ -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <?= htmlspecialchars(stripcslashes($_GET['success'])) ?>
            </div>
        <?php endif; ?>

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

        <!-- ============ NOTIFICATIONS ============ -->
        <?php if ($notifications != 0): ?>
            <div class="notif-card">
                <ul class="notif-list">
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                            $tone = notif_type_tone($notification['type']);
                            $icon = notif_type_icon($notification['type']);
                        ?>
                        <li class="notif-item">
                            <div class="notif-icon tone-<?= $tone ?>">
                                <svg viewBox="0 0 24 24"><?= $icon ?></svg>
                            </div>
                            <div class="notif-body">
                                <div class="notif-message"><?= htmlspecialchars($notification['message']) ?></div>
                                <div class="notif-meta">
                                    <span class="notif-type tone-<?= $tone ?>">
                                        <?= htmlspecialchars($notification['type']) ?>
                                    </span>
                                    <span class="notif-date">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        <?= htmlspecialchars($notification['date']) ?>
                                    </span>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="notif-card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <div class="empty-title">You're all caught up</div>
                    <div class="empty-text">
                        You have zero notifications right now. New updates about your tasks and deadlines will appear here.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>
</body>
</html>