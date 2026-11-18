<?php
session_name("admin");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
}

include "DB_connection.php";
include "app/model/user.php";
include "app/model/Task.php";
include "app/model/Department.php";

if (!isset($_GET['id'])) {
    header("Location: department.php");
    exit();
}

$id = (int)$_GET['id'];
$department = get_department_by_id($conn, $id);

if ($department == 0) {
    header("Location: department.php");
    exit();
}

$data  = ["employee", $department['id']];
$users = get_users_by_department($conn, $data);
$tasks = get_all_tasks($conn);
$admins = get_all_admins($conn);
$currentdate = date('Y-m-d');

/* ---- Resolve the department admin (if any) ---- */
$adminName = $adminContact = $adminUsername = null;
foreach ($admins as $admin) {
    if ($admin["department"] == $id) {
        $adminName     = $admin['full_name'];
        $adminContact  = $admin['contact'];
        $adminUsername = $admin['username'];
        break;
    }
}

/* ---- Count members & their tasks ---- */
$num_users = 0;
$num_task  = 0;
$memberTasks = [];

if (!empty($users) && $users != 0) {
    $num_users = count($users);
    $memberIds = array_column($users, 'id');

    foreach ($tasks as $task) {
        if (in_array($task['assigned_to'], $memberIds, true)) {
            $num_task++;
            $memberTasks[] = $task;
        }
    }
}

/* ---- Build user name lookup ---- */
$userMap = [];
foreach ($users as $u) {
    if (!empty($u)) {
        $userMap[$u['id']] = $u['full_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($department['name']) ?> · Task Management</title>

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
            gap: 12px;
        }

        .title-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--primary-soft);
            color: var(--primary);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .title-icon svg { width: 20px; height: 20px; }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            margin-top: 6px;
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

        .btn-danger {
            background: var(--danger-soft);
            color: var(--danger);
            border-color: #fecaca;
        }
        .btn-danger:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

        .btn-success {
            background: var(--success-soft);
            color: #047857;
            border-color: #a7f3d0;
        }
        .btn-success:hover { background: var(--success); color: #fff; border-color: var(--success); }

        /* ============ STAT STRIP ============ */
        .stat-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-mini {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-mini-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: var(--primary-soft);
            color: var(--primary);
        }

        .stat-mini-icon.success { background: var(--success-soft); color: var(--success); }
        .stat-mini-icon.info    { background: var(--info-soft);    color: var(--info); }
        .stat-mini-icon.warning { background: var(--warning-soft); color: var(--warning); }

        .stat-mini-icon svg { width: 20px; height: 20px; }

        .stat-mini-label {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-mini-value {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
            line-height: 1.2;
        }

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
        .alert svg { flex-shrink: 0; }
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

        /* ============ SECTION ============ */
        .section { margin-bottom: 28px; }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title svg { width: 16px; height: 16px; color: var(--text-subtle); }

        .section-count {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--surface-alt);
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        /* ============ ADMIN CARD ============ */
        .admin-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .admin-avatar {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 22px;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .admin-info { flex: 1; min-width: 220px; }

        .admin-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.01em;
        }

        .admin-role {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--primary);
            margin-top: 2px;
        }

        .admin-details {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .admin-detail {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .admin-detail svg { width: 13px; height: 13px; color: var(--text-subtle); }
        .admin-detail strong { color: var(--text); font-weight: 600; }

        .admin-empty {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            width: 100%;
        }

        .admin-empty-text {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 13.5px;
        }

        .admin-empty-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--warning-soft);
            color: var(--warning);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .admin-empty-icon svg { width: 20px; height: 20px; }

        /* ============ TABLE CARD ============ */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
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
            padding: 14px 18px;
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
            width: 50px;
        }

        .cell-name {
            font-weight: 600;
            color: var(--text);
        }

        .cell-desc {
            color: var(--text-muted);
            max-width: 320px;
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

        .action-detail:hover {
            background: var(--info-soft);
            color: var(--info);
            border-color: var(--info);
        }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--surface-alt);
            display: grid;
            place-items: center;
            margin: 0 auto 12px;
            color: var(--text-subtle);
        }

        .empty-icon svg { width: 22px; height: 22px; }

        .empty-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 2px;
        }

        .empty-sub {
            font-size: 12.5px;
            color: var(--text-subtle);
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
            .page-title .title-icon { width: 36px; height: 36px; }
            .data-table thead th,
            .data-table tbody td { padding: 12px 14px; }
            .cell-desc { display: none; }
            .admin-card { padding: 18px; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php include "inc/nav.php"; ?>

    <main class="app-main">

        <!-- ============ PAGE HEADER ============ -->
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <a href="department.php">Departments</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span><?= htmlspecialchars($department['name']) ?></span>
                </div>
                <h1 class="page-title">
                    <span class="title-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l8-4v18"/>
                            <path d="M19 21V11l-6-4"/>
                            <line x1="9" y1="9" x2="9" y2="9.01"/>
                            <line x1="9" y1="12" x2="9" y2="12.01"/>
                            <line x1="9" y1="15" x2="9" y2="15.01"/>
                            <line x1="9" y1="18" x2="9" y2="18.01"/>
                        </svg>
                    </span>
                    <?= htmlspecialchars($department['name']) ?>
                </h1>
                <p class="page-subtitle">Manage team members and review tasks assigned across this department.</p>
            </div>
            <div class="page-actions">
                <a href="edit-department.php?id=<?= $id ?>" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit
                </a>
                <a href="delete-department.php?id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Delete this department? This cannot be undone.');">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                        <path d="M10 11v6"/>
                        <path d="M14 11v6"/>
                        <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
                    </svg>
                    Delete
                </a>
                <a href="department.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    All Departments
                </a>
            </div>
        </div>

        <!-- ============ STAT STRIP ============ -->
        <div class="stat-strip">
            <div class="stat-mini">
                <div class="stat-mini-icon info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">Members</div>
                    <div class="stat-mini-value"><?= (int)$num_users ?></div>
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-icon success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">Tasks</div>
                    <div class="stat-mini-value"><?= (int)$num_task ?></div>
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-icon warning">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="7"/>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">Department Admin</div>
                    <div class="stat-mini-value" style="font-size:15px;">
                        <?= $adminName ? htmlspecialchars($adminName) : 'Not assigned' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ ALERTS ============ -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= htmlspecialchars(stripcslashes($_GET['error'])) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <?= htmlspecialchars(stripcslashes($_GET['success'])) ?>
            </div>
        <?php endif; ?>

        <!-- ============ ADMIN INFO ============ -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Department Admin
                </div>
            </div>

            <div class="admin-card">
                <?php if (!empty($adminName) && !empty($adminUsername) && !empty($adminContact)): ?>
                    <div class="admin-avatar"><?= htmlspecialchars(strtoupper(substr($adminName, 0, 1))) ?></div>
                    <div class="admin-info">
                        <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
                        <div class="admin-role">Administrator</div>
                        <div class="admin-details">
                            <span class="admin-detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <strong><?= htmlspecialchars($adminUsername) ?></strong>
                            </span>
                            <span class="admin-detail">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <strong><?= htmlspecialchars($adminContact) ?></strong>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="admin-empty">
                        <div class="admin-empty-text">
                            <div class="admin-empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--text);">No admin assigned</div>
                                <div style="font-size:12.5px; color:var(--text-subtle); margin-top:2px;">Assign an administrator to manage this department.</div>
                            </div>
                        </div>
                        <a href="add-user.php" class="btn btn-success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Add Admin
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============ MEMBERS TABLE ============ -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Members
                    <span class="section-count"><?= (int)$num_users ?></span>
                </div>
            </div>

            <?php if (!empty($users) && $users != 0): ?>
                <div class="table-card">
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Full Name</th>
                                    <th>Username</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; foreach ($users as $u): $i++;
                                $initial = strtoupper(substr($u['full_name'], 0, 1));
                            ?>
                                <tr>
                                    <td class="cell-id"><?= $i ?></td>
                                    <td>
                                        <span class="cell-assignee">
                                            <span class="assignee-avatar"><?= htmlspecialchars($initial) ?></span>
                                            <span class="cell-name"><?= htmlspecialchars($u['full_name']) ?></span>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td>
                                        <div class="actions" style="justify-content:flex-end;">
                                            <a href="task-details.php?id=<?= (int)$u['id'] ?>" class="action-btn action-detail" title="View details">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="11" cy="11" r="8"/>
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                                </svg>
                                                Details
                                            </a>
                                            <a href="edit-user.php?id=<?= (int)$u['id'] ?>" class="action-btn action-edit" title="Edit user">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <a href="delete-user.php?id=<?= (int)$u['id'] ?>" class="action-btn action-delete" title="Delete user" onclick="return confirm('Remove this member from the department?');">
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
                <div class="table-card">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="empty-title">No members yet</div>
                        <div class="empty-sub">This department doesn't have any employees assigned.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============ TASKS TABLE ============ -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Tasks
                    <span class="section-count"><?= (int)$num_task ?></span>
                </div>
            </div>

            <?php if (!empty($memberTasks)): ?>
                <div class="table-card">
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
                            <?php $i = 0; foreach ($memberTasks as $task):
                                $i++;
                                $assigneeName = $userMap[$task['assigned_to']] ?? 'Unassigned';
                                $initial = strtoupper(substr($assigneeName, 0, 1));
                                $noDeadline = ($task['due_date'] == "" || $task['due_date'] == "0000-00-00");

                                if ($noDeadline) {
                                    $statusLabel = ucfirst(str_replace('_', ' ', $task['status']));
                                    $statusClass = match($task['status']) {
                                        'completed'   => 'badge-completed',
                                        'in_progress' => 'badge-progress',
                                        default       => 'badge-pending',
                                    };
                                } elseif ($task['status'] === "completed") {
                                    $statusLabel = 'Completed';
                                    $statusClass = 'badge-completed';
                                } elseif ($task['due_date'] < $currentdate) {
                                    $statusLabel = 'Overdue';
                                    $statusClass = 'badge-overdue';
                                } elseif ($task['status'] === "in_progress") {
                                    $statusLabel = 'In Progress';
                                    $statusClass = 'badge-progress';
                                } else {
                                    $statusLabel = 'Pending';
                                    $statusClass = 'badge-pending';
                                }
                            ?>
                                <tr>
                                    <td class="cell-id"><?= $i ?></td>
                                    <td class="cell-name"><?= htmlspecialchars($task['title']) ?></td>
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
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                    <td>
                                        <div class="actions" style="justify-content:flex-end;">
                                            <a href="edit-task.php?id=<?= (int)$task['id'] ?>" class="action-btn action-edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <a href="delete-task.php?id=<?= (int)$task['id'] ?>" class="action-btn action-delete" onclick="return confirm('Delete this task?');">
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
                <div class="table-card">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                        </div>
                        <div class="empty-title">No tasks assigned</div>
                        <div class="empty-sub">
                            <?= $num_users > 0 ? "Members of this department don't have any tasks yet." : "Add members to this department to start assigning tasks." ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>
</body>
</html>