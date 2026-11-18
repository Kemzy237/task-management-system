<?php
session_name("admin-department");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    $em = "First login";
    header("Location: login-admin.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/Task.php";
include "app/model/user.php";
include "app/model/Department.php";

$departmentId = (int)($_SESSION['department'] ?? 0);

/* ---------- Department context ---------- */
$department = function_exists('get_department_by_id')
    ? get_department_by_id($conn, $departmentId)
    : 0;

$departmentName = ($department && $department != 0)
    ? $department['name']
    : 'Your Department';

/* ---------- Members of this department ---------- */
$role     = "employee";
$data     = [$role, $departmentId];
$users    = get_users_by_department($conn, $data);
$num_users = (!empty($users) && $users != 0) ? count($users) : 0;

/* ---------- All tasks, filtered to department members ---------- */
$tasks = get_all_tasks($conn);
$memberIds = (!empty($users) && $users != 0) ? array_column($users, 'id') : [];

$currentdate = date('Y-m-d');

$num_task        = 0;
$completed       = 0;
$in_progress_task= 0;
$due_today_task  = 0;
$overdue_task    = 0;
$pending_task    = 0;
$noDeadline_task = 0;

foreach ($tasks as $task) {
    if (!in_array($task['assigned_to'], $memberIds, true)) continue;

    $num_task++;
    $noDeadline = ($task['due_date'] == "" || $task['due_date'] == "0000-00-00");

    if ($task['status'] === "completed") {
        $completed++;
        continue;
    }

    if ($noDeadline) {
        $noDeadline_task++;
        if ($task['status'] === "in_progress") $in_progress_task++;
        else                                  $pending_task++;
        continue;
    }

    /* Task has a real due date and is not completed */
    if ($task['due_date'] === $currentdate) {
        $due_today_task++;
    }

    if ($task['due_date'] < $currentdate) {
        $overdue_task++;
    }

    if ($task['status'] === "in_progress") {
        $in_progress_task++;
    } elseif ($task['status'] === "pending") {
        $pending_task++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($departmentName) ?> · Dashboard</title>

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
            --shadow-lg:     0 12px 32px rgba(15, 23, 42, 0.08);
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

        /* ============ PAGE HEADER ============ */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 28px;
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

        /* ============ STAT CARDS ============ */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 3px;
            background: var(--primary);
            opacity: 0;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: transparent;
        }
        .stat-card:hover::before { opacity: 1; }

        .stat-card.tone-success::before { background: var(--success); }
        .stat-card.tone-warning::before { background: var(--warning); }
        .stat-card.tone-danger::before  { background: var(--danger); }
        .stat-card.tone-info::before    { background: var(--info); }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .stat-value {
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-top: 6px;
            color: var(--text);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: var(--primary-soft);
            color: var(--primary);
        }
        .stat-card.tone-success .stat-icon { background: var(--success-soft); color: var(--success); }
        .stat-card.tone-warning .stat-icon { background: var(--warning-soft); color: var(--warning); }
        .stat-card.tone-danger  .stat-icon { background: var(--danger-soft);  color: var(--danger); }
        .stat-card.tone-info    .stat-icon { background: var(--info-soft);    color: var(--info); }

        .stat-icon svg { width: 22px; height: 22px; }

        /* ============ CHART SECTION ============ */
        .chart-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 28px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .section-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        #piechart_3d {
            width: 100% !important;
            height: 460px;
        }

        .chart-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-subtle);
        }

        /* ============ EMPTY DEPARTMENT STATE ============ */
        .empty-state {
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            padding: 60px 24px;
            text-align: center;
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
            .stat-value { font-size: 26px; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php include "inc/nav-admin.php"; ?>

    <main class="app-main">

        <!-- ============ PAGE HEADER ============ -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">
                    Overview for <strong><?= htmlspecialchars($departmentName) ?></strong>.
                </p>
            </div>
            <div class="page-actions">
                <a href="task-department.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    View Tasks
                </a>
                <a href="user-department.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Manage Team
                </a>
            </div>
        </div>

        <!-- ============ STAT CARDS ============ -->
        <div class="stat-grid">
            <a href="user-department.php" class="stat-card tone-info">
                <div>
                    <div class="stat-label">Employees</div>
                    <div class="stat-value"><?= (int)$num_users ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </a>

            <a href="task-department.php" class="stat-card">
                <div>
                    <div class="stat-label">All Tasks</div>
                    <div class="stat-value"><?= (int)$num_task ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
            </a>

            <a href="task-department.php?due_date=Completed" class="stat-card tone-success">
                <div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-value"><?= (int)$completed ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </a>

            <a href="task-department.php?due_date=Due Today" class="stat-card tone-warning">
                <div>
                    <div class="stat-label">Due Today</div>
                    <div class="stat-value"><?= (int)$due_today_task ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
            </a>

            <a href="task-department.php?due_date=Pending" class="stat-card tone-danger">
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?= (int)$pending_task ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
            </a>
        </div>

        <!-- ============ CHART ============ -->
        <?php if ($num_task > 0): ?>
            <div class="chart-section">
                <div class="section-header">
                    <div>
                        <div class="section-title">Tasks Overview</div>
                        <div class="section-subtitle">Distribution of all tasks assigned to <?= htmlspecialchars($departmentName) ?></div>
                    </div>
                </div>
                <div id="piechart_3d"></div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div class="empty-title">No tasks in your department yet</div>
                <div class="empty-sub">
                    <?= $num_users > 0
                        ? "Your team doesn't have any tasks assigned. Create one to get started."
                        : "Add team members to your department to start assigning tasks." ?>
                </div>
                <a href="task-department.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    View Tasks
                </a>
            </div>
        <?php endif; ?>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', { packages: ['corechart'] });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var el = document.getElementById('piechart_3d');
        if (!el) return;

        var data = google.visualization.arrayToDataTable([
            ['Status', 'Number of tasks'],
            ['Pending',     <?= (int)$pending_task ?>],
            ['Completed',   <?= (int)$completed ?>],
            ['In Progress', <?= (int)$in_progress_task ?>],
            ['Overdue',     <?= (int)$overdue_task ?>]
        ]);

        var options = {
            pieHole: 0.45,
            is3D: false,
            backgroundColor: 'transparent',
            chartArea: { width: '90%', height: '85%' },
            legend: {
                position: 'bottom',
                textStyle: { color: '#64748b', fontSize: 12, fontName: 'Inter' }
            },
            colors: ['#f59e0b', '#10b981', '#0ea5e9', '#ef4444'],
            pieSliceTextStyle: { color: '#fff', fontName: 'Inter', fontSize: 12 }
        };

        new google.visualization.PieChart(el).draw(data, options);
    }

    window.addEventListener('resize', function () {
        clearTimeout(window._chartResize);
        window._chartResize = setTimeout(drawChart, 200);
    });
</script>
</body>
</html>