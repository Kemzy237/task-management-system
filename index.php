<?php
session_name("admin");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/model/Task.php";
include "app/model/Department.php";
include "app/model/user.php";

/* ---------- Dashboard metrics ---------- */
$due_today_task  = count_tasks_due_today($conn);
$overdue_task    = count_tasks_overdue($conn);
$noDeadline_task = count_tasks_noDeadline($conn);
$pending_task    = count_tasks_pending($conn);
$in_progress_task= count_tasks_in_progress($conn);
$num_task        = count_tasks($conn);
$num_users       = count_users($conn);
$completed       = count_completed($conn);

/* ---------- Per-department breakdown ---------- */
$departments = get_all_departments($conn);
$tasks       = get_all_tasks($conn);
$departmentStats = [];

foreach ($departments as $department) {
    $data  = ["employee", $department['id']];
    $users = get_users_by_department($conn, $data);
    if (empty($users)) continue;

    $currentdate = date('Y-m-d');
    $finished = $in_progress = $overdue = $pending = 0;
    $userIds = array_column($users, 'id');

    foreach ($tasks as $task) {
        if (!in_array($task['assigned_to'], $userIds)) continue;

        $noDeadline = ($task['due_date'] == "" || $task['due_date'] == "0000-00-00");

        if ($task['status'] === "completed") {
            $finished++;
        } elseif ($task['status'] === "in_progress") {
            if ($noDeadline || $task['due_date'] >= $currentdate) $in_progress++;
            else $overdue++;
        } elseif ($task['status'] === "pending") {
            if ($noDeadline || $task['due_date'] >= $currentdate) $pending++;
            else $overdue++;
        }
    }

    if ($finished + $in_progress + $overdue + $pending === 0) continue;

    $departmentStats[] = [
        'name'        => $department['name'],
        'finished'    => $finished,
        'in_progress' => $in_progress,
        'overdue'     => $overdue,
        'pending'     => $pending,
    ];
}
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
        .app-shell {
    min-height: 100vh;
}

        .app-sidebar {
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 24px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px 24px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            display: grid;
            place-items: center;
            color: #fff;
            font-weight: 800;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .brand-name {
            font-weight: 700;
            font-size: 15px;
            letter-spacing: -0.01em;
        }

        .brand-sub {
            font-size: 11px;
            color: var(--text-subtle);
            font-weight: 500;
        }

        .app-main {
    margin-left: 260px;       /* reserve space for the fixed sidebar */
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

        .page-actions {
            display: flex;
            gap: 10px;
        }

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

        /* ============ DEPARTMENT GRID ============ */
        .dept-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 20px;
        }

        .dept-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            transition: var(--transition);
        }

        .dept-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .dept-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .dept-name {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .dept-total {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--surface-alt);
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        .dept-chart {
            width: 100%;
            height: 280px;
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
        padding: 76px 16px 40px; /* 76px = 16px top + 44px toggle + 16px gap */
    }
}
    </style>
</head>
<body>
<div class="app-shell">

    <!-- ============ SIDEBAR ============ -->
    
        <?php include "inc/nav.php"; ?>
    

    <!-- ============ MAIN ============ -->
    <main class="app-main">

        <!-- Page header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">
                    Welcome back — here's what's happening across your workspace today.
                </p>
            </div>
            <div class="page-actions">
                <a href="task.php" class="btn btn-ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    View Tasks
                </a>
                <a href="user.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    Manage Users
                </a>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="stat-grid">
            <a href="user.php" class="stat-card tone-info">
                <div>
                    <div class="stat-label">Employees</div>
                    <div class="stat-value"><?= (int)$num_users ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </a>

            <a href="task.php" class="stat-card">
                <div>
                    <div class="stat-label">All Tasks</div>
                    <div class="stat-value"><?= (int)$num_task ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
            </a>

            <a href="task.php?due_date=Completed" class="stat-card tone-success">
                <div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-value"><?= (int)$completed ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </a>

            <a href="task.php?due_date=Due Today" class="stat-card tone-warning">
                <div>
                    <div class="stat-label">Due Today</div>
                    <div class="stat-value"><?= (int)$due_today_task ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </a>

            <a href="task.php?due_date=Pending" class="stat-card tone-danger">
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?= (int)$pending_task ?></div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </a>
        </div>

        <!-- Overall chart -->
        <div class="chart-section">
            <div class="section-header">
                <div>
                    <div class="section-title">Tasks Overview</div>
                    <div class="section-subtitle">Distribution of all tasks by current status</div>
                </div>
            </div>
            <?php if ($num_task != 0): ?>
                <div id="piechart_3d"></div>
            <?php else: ?>
                <div class="chart-empty">
                    <p>No tasks yet. Create your first task to see analytics here.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Per-department charts -->
        <?php if (!empty($departmentStats)): ?>
        <div class="chart-section">
            <div class="section-header">
                <div>
                    <div class="section-title">Department Breakdown</div>
                    <div class="section-subtitle">Task status per department</div>
                </div>
            </div>
            <div class="dept-grid">
                <?php foreach ($departmentStats as $i => $dept): ?>
                    <div class="dept-card">
                        <div class="dept-card-header">
                            <div class="dept-name"><?= htmlspecialchars($dept['name']) ?></div>
                            <div class="dept-total">
                                <?= (int)($dept['finished'] + $dept['in_progress'] + $dept['overdue'] + $dept['pending']) ?> tasks
                            </div>
                        </div>
                        <div class="dept-chart" id="dept-chart-<?= $i ?>"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>

<!-- ============ CHARTS ============ -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load("current", { packages: ["corechart"] });
    google.charts.setOnLoadCallback(drawAllCharts);

    const deptData = <?= json_encode($departmentStats) ?>;

    function chartOptions(title) {
        return {
            title: title,
            pieHole: 0.45,
            is3D: false,
            backgroundColor: 'transparent',
            chartArea: { width: '90%', height: '80%' },
            legend: {
                position: 'bottom',
                textStyle: { color: '#64748b', fontSize: 12, fontName: 'Inter' }
            },
            titleTextStyle: {
                color: '#1e293b',
                fontSize: 15,
                bold: true,
                fontName: 'Inter'
            },
            colors: ['#f59e0b', '#10b981', '#0ea5e9', '#ef4444'],
            pieSliceTextStyle: { color: '#fff', fontName: 'Inter', fontSize: 12 }
        };
    }

    function drawAllCharts() {
        /* ---- Overall chart ---- */
        <?php if ($num_task != 0): ?>
        var overall = google.visualization.arrayToDataTable([
            ['Status', 'Number of tasks'],
            ['Pending',    <?= (int)$pending_task ?>],
            ['Completed',  <?= (int)$completed ?>],
            ['In Progress',<?= (int)$in_progress_task ?>],
            ['Overdue',    <?= (int)$overdue_task ?>]
        ]);
        new google.visualization.PieChart(document.getElementById('piechart_3d'))
            .draw(overall, chartOptions(''));
        <?php endif; ?>

        /* ---- Department charts ---- */
        deptData.forEach(function (d, i) {
            var el = document.getElementById('dept-chart-' + i);
            if (!el) return;
            var data = google.visualization.arrayToDataTable([
                ['Status', 'Number of tasks'],
                ['Pending',     d.pending],
                ['Completed',   d.finished],
                ['In Progress', d.in_progress],
                ['Overdue',     d.overdue]
            ]);
            new google.visualization.PieChart(el).draw(data, chartOptions(d.name));
        });
    }

    window.addEventListener('resize', function () {
        clearTimeout(window._chartResize);
        window._chartResize = setTimeout(drawAllCharts, 200);
    });
</script>
</body>
</html>