<?php
session_name("admin");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/model/Task.php";
include "app/model/user.php";

$currentdate = date('Y-m-d');
$text        = "All Tasks";
$filterKey   = $_GET['due_date'] ?? '';

switch ($filterKey) {
    case "Due Today":
        $text = "Due Today";       $tasks = get_all_tasks_due_today($conn);       $num_task = count_tasks_due_today($conn);       break;
    case "Overdue":
        $text = "Overdue";         $tasks = get_all_tasks_overdue($conn);         $num_task = count_tasks_overdue($conn);         break;
    case "No Deadline":
        $text = "No Deadline";     $tasks = get_all_tasks_noDeadline($conn);      $num_task = count_tasks_noDeadline($conn);      break;
    case "Due In 7 Days":
        $text = "Due In 7 Days";   $tasks = get_all_tasks_due_in_7_days($conn);   $num_task = count_tasks_due_in_7_days($conn);   break;
    case "Due In 30 Days":
        $text = "Due In 30 Days";  $tasks = get_all_tasks_due_in_30_days($conn);  $num_task = count_tasks_due_in_30_days($conn);  break;
    case "Pending":
        $text = "Pending";         $tasks = get_all_tasks_pending($conn);         $num_task = count_tasks_pending($conn);         break;
    case "In Progress":
        $text = "In Progress";     $tasks = get_all_tasks_in_progress($conn);     $num_task = count_tasks_in_progress($conn);     break;
    case "Completed":
        $text = "Completed";       $tasks = get_all_tasks_completed($conn);       $num_task = count_completed($conn);             break;
    default:
        $tasks = get_all_tasks($conn);
        $num_task = count_tasks($conn);
}

$users = get_all_users($conn);

/* Build a quick lookup: user_id => full_name */
$userMap = [];
foreach ($users as $u) {
    $userMap[$u['id']] = $u['full_name'];
}

/* Filter tabs (primary + secondary groups) */
$primaryFilters = [
    ''              => 'All Tasks',
    'Due Today'     => 'Due Today',
    'Overdue'       => 'Overdue',
    'Completed'     => 'Completed',
];
$moreFilters = [
    'Due In 7 Days'  => 'Due In 7 Days',
    'Due In 30 Days' => 'Due In 30 Days',
    'Pending'        => 'Pending',
    'In Progress'    => 'In Progress',
    'No Deadline'    => 'No Deadline',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tasks · Task Management</title>

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
            max-width: 1600px;
            width: calc(100% - 260px);
        }

        /* ============ HEADER ============ */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }

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

        .page-actions { display: flex; gap: 10px; flex-wrap: wrap; }

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

        /* ============ FILTER BAR ============ */
        .filter-bar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            padding: 9px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            transition: var(--transition);
            white-space: nowrap;
            cursor: pointer;
        }

        .filter-chip:hover {
            background: var(--surface-alt);
            color: var(--text);
        }

        .filter-chip.is-active {
            background: var(--primary-soft);
            color: var(--primary);
        }

        .filter-divider {
            width: 1px;
            height: 24px;
            background: var(--border);
            margin: 0 4px;
        }

        .filter-more {
            background: transparent;
            border: 1px dashed var(--border);
            color: var(--text-muted);
        }
        .filter-more:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft);
        }

        /* Collapsible "more filters" */
        .more-filters {
            display: none;
            width: 100%;
            padding-top: 8px;
            border-top: 1px solid var(--border);
            margin-top: 4px;
            gap: 6px;
            flex-wrap: wrap;
        }
        .more-filters.is-open { display: flex; }

        /* ============ ALERT ============ */
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
        .alert-success {
            background: var(--success-soft);
            border-color: #a7f3d0;
            color: #047857;
        }

        /* ============ TABLE CARD ============ */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .table-count {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--surface-alt);
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        .table-wrap { overflow-x: auto; }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .data-table thead th {
            text-align: left;
            padding: 14px 18px;
            font-weight: 600;
            font-size: 11.5px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: var(--surface-alt);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .data-table tbody td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .data-table tbody tr { transition: background var(--transition); }
        .data-table tbody tr:hover { background: var(--surface-alt); }
        .data-table tbody tr:last-child td { border-bottom: none; }

        .cell-id {
            font-weight: 600;
            color: var(--text-subtle);
            font-variant-numeric: tabular-nums;
        }

        .cell-title {
            font-weight: 600;
            color: var(--text);
            max-width: 220px;
        }

        .cell-desc {
            color: var(--text-muted);
            max-width: 340px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-clamp: 2;
        }

        .cell-assignee {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .assignee-avatar {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .cell-date {
            color: var(--text-muted);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .no-deadline {
            color: var(--text-subtle);
            font-style: italic;
        }

        /* ============ STATUS BADGE ============ */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .badge-pending    { background: var(--warning-soft); color: #b45309; }
        .badge-progress   { background: var(--info-soft);    color: #0369a1; }
        .badge-completed  { background: var(--success-soft); color: #047857; }
        .badge-overdue    { background: var(--danger-soft);  color: #b91c1c; }

        /* ============ ACTION BUTTONS ============ */
        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 11px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-muted);
            transition: var(--transition);
            cursor: pointer;
        }

        .action-btn svg { width: 13px; height: 13px; }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .action-edit:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
        }

        .action-delete:hover {
            background: var(--danger-soft);
            color: var(--danger);
            border-color: var(--danger);
        }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            padding: 80px 20px;
            text-align: center;
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: var(--radius);
        }

        .empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: var(--surface-alt);
            display: grid;
            place-items: center;
            margin: 0 auto 16px;
            color: var(--text-subtle);
        }

        .empty-icon svg { width: 26px; height: 26px; }

        .empty-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .empty-sub {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 20px;
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
            .filter-bar { padding: 6px; }
            .data-table thead th,
            .data-table tbody td { padding: 12px 14px; }
            .cell-desc { display: none; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php include "inc/nav.php"; ?>

    <main class="app-main">

        <!-- ============ HEADER ============ -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Tasks</h1>
                <p class="page-subtitle">
                    Browse, filter, and manage every task across your workspace.
                </p>
            </div>
            <div class="page-actions">
                <a href="create_task.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Create Task
                </a>
            </div>
        </div>

        <!-- ============ FILTERS ============ -->
        <div class="filter-bar">
            <?php foreach ($primaryFilters as $key => $label):
                $href = $key === '' ? 'task.php' : 'task.php?due_date=' . urlencode($key);
                $isActive = ($filterKey === $key);
            ?>
                <a href="<?= $href ?>" class="filter-chip <?= $isActive ? 'is-active' : '' ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>

            <span class="filter-divider"></span>

            <button type="button" class="filter-chip filter-more" id="moreToggle" aria-expanded="false" aria-controls="moreFilters">
                More
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:6px;">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            <div class="more-filters" id="moreFilters">
                <?php foreach ($moreFilters as $key => $label):
                    $href = 'task.php?due_date=' . urlencode($key);
                    $isActive = ($filterKey === $key);
                ?>
                    <a href="<?= $href ?>" class="filter-chip <?= $isActive ? 'is-active' : '' ?>">
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ============ SUCCESS ALERT ============ -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <?= htmlspecialchars(stripcslashes($_GET['success'])) ?>
            </div>
        <?php endif; ?>

        <!-- ============ TASK TABLE ============ -->
        <?php if ($tasks != 0 && !empty($tasks)): ?>
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title"><?= htmlspecialchars($text) ?></div>
                    <div class="table-count"><?= (int)$num_task ?> task<?= $num_task === 1 ? '' : 's' ?></div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Assigned To</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0; foreach ($tasks as $task):
                            $i++;

                            /* Resolve assignee */
                            $assigneeName = $userMap[$task['assigned_to']] ?? 'Unassigned';
                            $initial = strtoupper(substr($assigneeName, 0, 1));

                            /* Resolve due date display */
                            $noDeadline = ($task['due_date'] == "" || $task['due_date'] == "0000-00-00");

                            /* Resolve status label + badge class */
                            if ($noDeadline) {
                                $statusLabel = ucfirst(str_replace('_', ' ', $task['status']));
                                $statusClass = match($task['status']) {
                                    'completed'   => 'badge-completed',
                                    'in_progress' => 'badge-progress',
                                    default       => 'badge-pending',
                                };
                            } elseif ($task['due_date'] < $currentdate && $task['status'] != "completed") {
                                $statusLabel = 'Overdue';
                                $statusClass = 'badge-overdue';
                            } elseif ($task['status'] == "completed") {
                                $statusLabel = 'Completed';
                                $statusClass = 'badge-completed';
                            } elseif ($task['status'] == "in_progress") {
                                $statusLabel = 'In Progress';
                                $statusClass = 'badge-progress';
                            } else {
                                $statusLabel = 'Pending';
                                $statusClass = 'badge-pending';
                            }
                        ?>
                            <tr>
                                <td class="cell-id"><?= $i ?></td>
                                <td class="cell-title"><?= htmlspecialchars($task['title']) ?></td>
                                <td class="cell-desc"><?= htmlspecialchars($task['description']) ?></td>
                                <td>
                                    <span class="cell-assignee">
                                        <span class="assignee-avatar"><?= htmlspecialchars($initial) ?></span>
                                        <?= htmlspecialchars($assigneeName) ?>
                                    </span>
                                </td>
                                <td class="cell-date">
                                    <?php if ($noDeadline): ?>
                                        <span class="no-deadline">No Deadline</span>
                                    <?php else: ?>
                                        <?= htmlspecialchars($task['due_date']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                                </td>
                                <td>
                                    <div class="actions" style="justify-content:flex-end;">
                                        <a href="edit-task.php?id=<?= (int)$task['id'] ?>" class="action-btn action-edit" title="Edit task">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="delete-task.php?id=<?= (int)$task['id'] ?>" class="action-btn action-delete" title="Delete task" onclick="return confirm('Delete this task?');">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                <path d="M10 11v6"/>
                                                <path d="M14 11v6"/>
                                                <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <div class="empty-title">No tasks found</div>
                <div class="empty-sub">There are no tasks matching this filter yet.</div>
                <a href="create_task.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Create your first task
                </a>
            </div>
        <?php endif; ?>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>

<script>
    /* More-filters toggle */
    (function () {
        const toggle = document.getElementById('moreToggle');
        const panel  = document.getElementById('moreFilters');
        if (!toggle || !panel) return;

        // Auto-open if a "more" filter is currently active
        <?php if (in_array($filterKey, array_keys($moreFilters), true)): ?>
        panel.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        <?php endif; ?>

        toggle.addEventListener('click', function () {
            const open = panel.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    })();
</script>
</body>
</html>