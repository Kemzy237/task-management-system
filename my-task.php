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

$text     = "My Tasks";
$filter   = $_GET['due_date'] ?? '';

switch ($filter) {
    case 'Due Today':
        $text     = "Due Today";
        $tasks    = get_all_my_tasks_due_today($conn, $_SESSION['id']);
        $num_task = count_my_tasks_due_today($conn, $_SESSION['id']);
        break;
    case 'Overdue':
        $text     = "Overdue";
        $tasks    = get_all_my_tasks_overdue($conn, $_SESSION['id']);
        $num_task = count_overdue_task($conn, $_SESSION['id']);
        break;
    case 'No Deadline':
        $text     = "No Deadline";
        $tasks    = get_all_my_tasks_noDeadline($conn, $_SESSION['id']);
        $num_task = count_my_tasks_noDeadline($conn, $_SESSION['id']);
        break;
    case 'Pending':
        $text     = "Pending";
        $tasks    = get_all_my_tasks_pending($conn, $_SESSION['id']);
        $num_task = count_my_tasks_pending($conn, $_SESSION['id']);
        break;
    case 'In Progress':
        $text     = "In Progress";
        $tasks    = get_all_my_tasks_in_progress($conn, $_SESSION['id']);
        $num_task = count_my_tasks_in_progress($conn, $_SESSION['id']);
        break;
    case 'Completed':
        $text     = "Completed";
        $tasks    = get_all_my_tasks_completed($conn, $_SESSION['id']);
        $num_task = count_my_completed($conn, $_SESSION['id']);
        break;
    default:
        $filter   = '';
        $tasks    = get_all_tasks_by_id($conn, $_SESSION['id']);
        $num_task = count_my_task($conn, $_SESSION['id']);
}

/* Filter chip definitions */
$chips = [
    ['label' => 'All',         'href' => 'my-task.php',                        'key' => ''],
    ['label' => 'Due Today',   'href' => 'my-task.php?due_date=Due Today',     'key' => 'Due Today'],
    ['label' => 'Overdue',     'href' => 'my-task.php?due_date=Overdue',       'key' => 'Overdue'],
    ['label' => 'Completed',   'href' => 'my-task.php?due_date=Completed',     'key' => 'Completed'],
    ['label' => 'Pending',     'href' => 'my-task.php?due_date=Pending',       'key' => 'Pending'],
    ['label' => 'In Progress', 'href' => 'my-task.php?due_date=In Progress',   'key' => 'In Progress'],
    ['label' => 'No Deadline', 'href' => 'my-task.php?due_date=No Deadline',   'key' => 'No Deadline'],
];

/* Status → tone map for badges */
function task_status_tone($status) {
    switch (strtolower((string)$status)) {
        case 'completed':   return 'success';
        case 'in progress': return 'primary';
        case 'pending':     return 'warning';
        default:            return 'neutral';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($text) ?> · My Tasks</title>

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

        .page-title .count-pill {
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
        .btn-sm { padding: 7px 12px; font-size: 12px; }
        .btn-sm svg { width: 12px; height: 12px; }

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

        /* ============ FILTER CHIPS ============ */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 600;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
            transition: var(--transition);
            white-space: nowrap;
        }

        .filter-chip:hover {
            background: var(--surface-alt);
            color: var(--text);
            border-color: #d8deea;
        }

        .filter-chip.is-active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        /* ============ TABLE CARD ============ */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-scroll { overflow-x: auto; }

        .task-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .task-table thead th {
            text-align: left;
            padding: 14px 18px;
            background: var(--surface-alt);
            border-bottom: 1px solid var(--border);
            font-size: 11px;
            font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
        }

        .task-table tbody td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .task-table tbody tr:last-child td { border-bottom: none; }

        .task-table tbody tr {
            transition: background var(--transition);
        }
        .task-table tbody tr:hover { background: var(--surface-alt); }

        .cell-index {
            color: var(--text-subtle);
            font-weight: 600;
            font-size: 12.5px;
            width: 48px;
        }

        .cell-title {
            font-weight: 600;
            color: var(--text);
            max-width: 240px;
        }

        .cell-desc {
            color: var(--text-muted);
            max-width: 360px;
        }
        .cell-desc .truncate {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cell-date {
            white-space: nowrap;
            color: var(--text-muted);
            font-variant-numeric: tabular-nums;
        }
        .cell-date .no-deadline {
            color: var(--text-subtle);
            font-style: italic;
        }

        /* ============ STATUS BADGES ============ */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid;
        }
        .status-badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .status-badge.tone-success { background: var(--success-soft); color: #047857; border-color: #a7f3d0; }
        .status-badge.tone-primary { background: var(--primary-soft); color: var(--primary); border-color: #c7d2fe; }
        .status-badge.tone-warning { background: var(--warning-soft); color: #b45309; border-color: #fde68a; }
        .status-badge.tone-neutral { background: var(--neutral-soft); color: var(--text-muted); border-color: var(--border); }

        /* ============ ROW ACTION ============ */
        .row-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid #c7d2fe;
            transition: var(--transition);
        }
        .row-action:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .row-action svg { width: 12px; height: 12px; }

        .row-done {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            color: var(--success);
        }
        .row-done svg { width: 13px; height: 13px; }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            padding: 64px 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            color: var(--text-subtle);
        }
        .empty-icon svg { width: 24px; height: 24px; }

        .empty-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
        }
        .empty-text {
            font-size: 13px;
            color: var(--text-muted);
            max-width: 320px;
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
            .cell-title, .cell-desc { max-width: none; }
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
                    <span>My Tasks</span>
                    <?php if ($filter !== ''): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                        <span><?= htmlspecialchars($text) ?></span>
                    <?php endif; ?>
                </div>
                <h1 class="page-title">
                    <?= htmlspecialchars($text) ?>
                    <span class="count-pill"><?= (int)$num_task ?></span>
                </h1>
                <p class="page-subtitle">
                    <?php if ($filter !== ''): ?>
                        Showing tasks filtered by <strong><?= htmlspecialchars($text) ?></strong>.
                    <?php else: ?>
                        All tasks assigned to you, in one place.
                    <?php endif; ?>
                </p>
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

        <!-- ============ FILTER CHIPS ============ -->
        <div class="filter-bar" role="tablist" aria-label="Task filters">
            <?php foreach ($chips as $chip): ?>
                <a
                    href="<?= htmlspecialchars($chip['href']) ?>"
                    class="filter-chip<?= $filter === $chip['key'] ? ' is-active' : '' ?>"
                    role="tab"
                    aria-selected="<?= $filter === $chip['key'] ? 'true' : 'false' ?>"
                >
                    <?= htmlspecialchars($chip['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ============ TABLE / EMPTY STATE ============ -->
        <?php if ($tasks != 0): ?>
            <div class="table-card">
                <div class="table-scroll">
                    <table class="task-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; foreach ($tasks as $task): $i++; ?>
                                <tr>
                                    <td class="cell-index"><?= $i ?></td>
                                    <td class="cell-title"><?= htmlspecialchars($task['title']) ?></td>
                                    <td class="cell-desc">
                                        <span class="truncate"><?= htmlspecialchars($task['description']) ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge tone-<?= task_status_tone($task['status']) ?>">
                                            <?= htmlspecialchars(ucfirst((string)$task['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="cell-date">
                                        <?php if ($task['due_date'] == "" || $task['due_date'] == "0000-00-00"): ?>
                                            <span class="no-deadline">No Deadline</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($task['due_date']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($task['status'] == "completed"): ?>
                                            <span class="row-done">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                Completed
                                            </span>
                                        <?php else: ?>
                                            <a href="edit-task-employee.php?id=<?= (int)$task['id'] ?>" class="row-action">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="table-card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </div>
                    <div class="empty-title">No tasks<?= $filter !== '' ? ' in this filter' : '' ?></div>
                    <div class="empty-text">
                        <?php if ($filter !== ''): ?>
                            You don't have any <strong><?= htmlspecialchars(strtolower($text)) ?></strong> tasks right now.
                            Try switching filters or view all tasks.
                        <?php else: ?>
                            You're all caught up — no tasks are assigned to you yet.
                        <?php endif; ?>
                    </div>
                    <?php if ($filter !== ''): ?>
                        <a href="my-task.php" class="btn btn-ghost btn-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="19" y1="12" x2="5" y2="12"/>
                                <polyline points="12 19 5 12 12 5"/>
                            </svg>
                            View all tasks
                        </a>
                    <?php endif; ?>
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