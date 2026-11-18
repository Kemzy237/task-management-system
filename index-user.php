<?php
session_name("employee");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    $em = "First login";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/Task.php";
include "app/model/user.php";

$num_my_task         = count_my_task($conn, $_SESSION['id']);
$task_overdue        = count_overdue_task($conn, $_SESSION['id']);
$my_completed        = count_my_completed($conn, $_SESSION['id']);
$my_due_today_task   = count_my_tasks_due_today($conn, $_SESSION['id']);
$my_noDeadline_task  = count_my_tasks_noDeadline($conn, $_SESSION['id']);
$my_pending_task     = count_my_tasks_pending($conn, $_SESSION['id']);
$my_in_progress_task = count_my_tasks_in_progress($conn, $_SESSION['id']);

$fullName = getFullName($conn, $_SESSION['id']);
if (is_array($fullName)) {
    $fullName = $fullName['full_name'] ?? 'there';
}
$initial = strtoupper(substr((string)$fullName, 0, 1) ?: '?');

$stats = [
    [
        'href'  => 'my-task.php',
        'label' => 'My Tasks',
        'count' => (int)$num_my_task,
        'tone'  => 'primary',
        'icon'  => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
    ],
    [
        'href'  => 'my-task.php?due_date=Overdue',
        'label' => 'Overdue Tasks',
        'count' => (int)$task_overdue,
        'tone'  => 'danger',
        'icon'  => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
    ],
    [
        'href'  => 'my-task.php?due_date=Completed',
        'label' => 'Completed Tasks',
        'count' => (int)$my_completed,
        'tone'  => 'success',
        'icon'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    ],
    [
        'href'  => 'my-task.php?due_date=Due Today',
        'label' => 'Tasks Due Today',
        'count' => (int)$my_due_today_task,
        'tone'  => 'warning',
        'icon'  => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
    ],
    [
        'href'  => 'my-task.php?due_date=Pending',
        'label' => 'Pending Tasks',
        'count' => (int)$my_pending_task,
        'tone'  => 'info',
        'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    ],
    [
        'href'  => 'my-task.php?due_date=In Progress',
        'label' => 'Tasks In Progress',
        'count' => (int)$my_in_progress_task,
        'tone'  => 'primary',
        'icon'  => '<path d="M21 12a9 9 0 1 1-6.22-8.56"/>',
    ],
    [
        'href'  => 'my-task.php?due_date=No Deadline',
        'label' => 'No Deadline',
        'count' => (int)$my_noDeadline_task,
        'tone'  => 'neutral',
        'icon'  => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · Task Management</title>

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

        /* ============ HERO / GREETING ============ */
        .hero {
            position: relative;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0 0 auto auto;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.12), transparent 70%);
            pointer-events: none;
        }

        .hero-avatar {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 26px;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            flex-shrink: 0;
        }

        .hero-text { flex: 1 1 240px; min-width: 0; }
        .hero-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .hero-title strong { color: var(--primary); }
        .hero-sub {
            color: var(--text-muted);
            font-size: 13.5px;
            margin-top: 4px;
        }

        .hero-chips {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .hero-chip svg { width: 12px; height: 12px; }
        .hero-chip.overdue { background: var(--danger-soft);    color: #b91c1c; border-color: #fecaca; }
        .hero-chip.due     { background: var(--warning-soft);   color: #b45309; border-color: #fde68a; }

        /* ============ SECTION HEADERS ============ */
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .section-sub {
            font-size: 12.5px;
            color: var(--text-subtle);
        }

        /* ============ STAT GRID ============ */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 8px;
        }

        .stat-card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            transition: var(--transition);
            overflow: hidden;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            inset: auto -40px -40px auto;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0.06;
            pointer-events: none;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: #d8deea;
        }
        .stat-card:hover::after { transform: scale(1.15); }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            transition: var(--transition);
        }
        .stat-icon svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .stat-arrow {
            width: 22px;
            height: 22px;
            color: var(--text-subtle);
            opacity: 0;
            transform: translateX(-4px);
            transition: var(--transition);
        }
        .stat-arrow svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .stat-card:hover .stat-arrow { opacity: 1; transform: translateX(0); color: var(--primary); }

        .stat-count {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1;
            color: var(--text);
        }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: -6px;
        }

        /* Tone variants */
        .stat-card.tone-primary { color: var(--primary); }
        .stat-card.tone-primary .stat-icon { background: var(--primary-soft); color: var(--primary); }

        .stat-card.tone-success { color: var(--success); }
        .stat-card.tone-success .stat-icon { background: var(--success-soft); color: #047857; }

        .stat-card.tone-warning { color: var(--warning); }
        .stat-card.tone-warning .stat-icon { background: var(--warning-soft); color: #b45309; }

        .stat-card.tone-danger { color: var(--danger); }
        .stat-card.tone-danger .stat-icon { background: var(--danger-soft); color: #b91c1c; }

        .stat-card.tone-info { color: var(--info); }
        .stat-card.tone-info .stat-icon { background: var(--info-soft); color: #0369a1; }

        .stat-card.tone-neutral { color: var(--text-subtle); }
        .stat-card.tone-neutral .stat-icon { background: var(--neutral-soft); color: var(--text-muted); }

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
            .hero { padding: 20px; }
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
                    <span>Employee</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span>Dashboard</span>
                </div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">A snapshot of everything on your plate.</p>
            </div>
            <div class="page-actions">
                <a href="my-task.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    View My Tasks
                </a>
            </div>
        </div>

        <!-- ============ HERO / GREETING ============ -->
        <div class="hero">
            <div class="hero-avatar"><?= htmlspecialchars($initial) ?></div>
            <div class="hero-text">
                <div class="hero-title">
                    Welcome back, <strong><?= htmlspecialchars($fullName) ?></strong>
                </div>
                <div class="hero-sub">
                    You have <strong><?= (int)$num_my_task ?></strong> task<?= (int)$num_my_task === 1 ? '' : 's' ?> in total.
                </div>
                <div class="hero-chips">
                    <?php if ((int)$task_overdue > 0): ?>
                        <span class="hero-chip overdue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <?= (int)$task_overdue ?> overdue
                        </span>
                    <?php endif; ?>
                    <?php if ((int)$my_due_today_task > 0): ?>
                        <span class="hero-chip due">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <?= (int)$my_due_today_task ?> due today
                        </span>
                    <?php endif; ?>
                    <span class="hero-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <?= (int)$my_completed ?> completed
                    </span>
                </div>
            </div>
        </div>

        <!-- ============ STATS ============ -->
        <div class="section-head">
            <div>
                <div class="section-title">Task Overview</div>
                <div class="section-sub">Click any card to open the filtered task list.</div>
            </div>
        </div>

        <div class="stat-grid">
            <?php foreach ($stats as $s): ?>
                <a href="<?= htmlspecialchars($s['href']) ?>" class="stat-card tone-<?= htmlspecialchars($s['tone']) ?>">
                    <div class="stat-top">
                        <span class="stat-icon">
                            <svg viewBox="0 0 24 24"><?= $s['icon'] ?></svg>
                        </span>
                        <span class="stat-arrow">
                            <svg viewBox="0 0 24 24">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </span>
                    </div>
                    <div class="stat-count"><?= (int)$s['count'] ?></div>
                    <div class="stat-label"><?= htmlspecialchars($s['label']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>
</body>
</html>