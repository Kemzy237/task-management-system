<?php
session_name("admin-department");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    $em = "First login";
    header("Location: login-admin.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/user.php";
include "app/model/Task.php";
include "app/model/Department.php";

if (!isset($_GET['id'])) {
    header("Location: user-department.php");
    exit();
}

$id = (int)$_GET['id'];
$user = get_user_by_id($conn, $id);

if ($user == 0) {
    header("Location: user-department.php?error=" . urlencode("User not found."));
    exit();
}

/* ---------- Ownership guard: user must be an employee of this department ---------- */
$departmentId = (int)($_SESSION['department'] ?? 0);

if (
    $user['role'] !== 'employee'
    || (int)$user['department'] !== $departmentId
) {
    $em = "You don't have permission to view this user.";
    header("Location: user-department.php?error=" . urlencode($em));
    exit();
}

$departments = get_all_departments($conn);

/* ---------- Resolve department name ---------- */
$departmentName = 'No department';
foreach ($departments as $d) {
    if ((int)$d['id'] === (int)$user['department']) {
        $departmentName = $d['name'];
        break;
    }
}

/* ---------- Task metrics ---------- */
$num        = count_my_task($conn, $user['id']);
$overdue    = count_overdue_task($conn, $user['id']);
$noDeadline = count_my_tasks_noDeadline($conn, $user['id']);
$pending    = count_my_tasks_pending($conn, $user['id']);
$inProgress = count_my_tasks_in_progress($conn, $user['id']);
$completed  = count_my_completed($conn, $user['id']);

$tasks       = get_all_tasks_by_id($conn, $user['id']);
$num_task    = count_my_task($conn, $user['id']);
$currentdate = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['full_name']) ?> · Task Management</title>

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

        /* ============ PROFILE CARD ============ */
        .profile-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .profile-avatar {
            width: 84px;
            height: 84px;
            border-radius: 22px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 32px;
            font-weight: 800;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .profile-info { flex: 1; min-width: 240px; }

        .profile-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .profile-username {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .profile-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .profile-meta-item svg { width: 13px; height: 13px; color: var(--text-subtle); }
        .profile-meta-item strong { color: var(--text); font-weight: 600; }

        /* ============ STAT STRIP ============ */
        .stat-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .stat-mini {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
        }
        .stat-mini:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }

        .stat-mini-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: var(--primary-soft);
            color: var(--primary);
        }
        .stat-mini-icon.success { background: var(--success-soft); color: var(--success); }
        .stat-mini-icon.warning { background: var(--warning-soft); color: var(--warning); }
        .stat-mini-icon.danger  { background: var(--danger-soft);  color: var(--danger); }
        .stat-mini-icon.info    { background: var(--info-soft);    color: var(--info); }
        .stat-mini-icon svg { width: 18px; height: 18px; }

        .stat-mini-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-mini-value {
            font-size: 20px;
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

        /* ============ CHART CARD ============ */
        .chart-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 24px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }

        .chart-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .chart-subtitle {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        #piechart { width: 100%; height: 440px; }

        /* ============ SECTION ============ */
        .section { margin-bottom: 24px; }

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
        .cell-title { font-weight: 600; color: var(--text); max-width: 220px; }
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
        .cell-date {
            color: var(--text-muted);
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .no-deadline { color: var(--text-subtle); font-style: italic; }

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
        .badge-pending   { background: var(--warning-soft); color: #b45309; }
        .badge-progress  { background: var(--info-soft);    color: #0369a1; }
        .badge-completed { background: var(--success-soft); color: #047857; }
        .badge-overdue   { background: var(--danger-soft);  color: #b91c1c; }

        /* ============ ACTION BUTTONS ============ */
        .actions { display: flex; gap: 6px; flex-wrap: nowrap; }

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
        .action-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
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
        .empty-state { padding: 60px 20px; text-align: center; }
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
        .empty-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
        .empty-sub { font-size: 12.5px; color: var(--text-subtle); }

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
            .profile-card { padding: 20px; gap: 16px; }
            .profile-avatar { width: 64px; height: 64px; font-size: 26px; }
            .profile-name { font-size: 18px; }
            .data-table thead th,
            .data-table tbody td { padding: 12px 14px; }
            .cell-desc { display: none; }
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
                <div class="breadcrumb">
                    <a href="user-department.php">Manage Users</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span><?= htmlspecialchars($user['full_name']) ?></span>
                </div>
                <h1 class="page-title">User Details</h1>
                <p class="page-subtitle">Profile, task statistics, and assigned work for this team member.</p>
            </div>
            <div class="page-actions">
                <a href="edit-user-department.php?id=<?= $id ?>" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit Info
                </a>
                <a href="user-department.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>

        <!-- ============ PROFILE CARD ============ -->
        <div class="profile-card">
            <div class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($user['full_name'], 0, 1))) ?></div>
            <div class="profile-info">
                <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>
                <div class="profile-meta">
                    <span class="profile-meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <strong><?= htmlspecialchars($user['contact']) ?></strong>
                    </span>
                    <span class="profile-meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l8-4v18"/>
                            <path d="M19 21V11l-6-4"/>
                        </svg>
                        <strong><?= htmlspecialchars($departmentName) ?></strong>
                    </span>
                    <span class="profile-meta-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Joined <strong><?= !empty($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : '—' ?></strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- ============ STAT STRIP ============ -->
        <div class="stat-strip">
            <div class="stat-mini">
                <div class="stat-mini-icon info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">Total</div>
                    <div class="stat-mini-value"><?= (int)$num ?></div>
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-icon warning">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">Pending</div>
                    <div class="stat-mini-value"><?= (int)$pending ?></div>
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-icon info">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">In Progress</div>
                    <div class="stat-mini-value"><?= (int)$inProgress ?></div>
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-icon success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">Completed</div>
                    <div class="stat-mini-value"><?= (int)$completed ?></div>
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-icon danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">Overdue</div>
                    <div class="stat-mini-value"><?= (int)$overdue ?></div>
                </div>
            </div>

            <div class="stat-mini">
                <div class="stat-mini-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>
                    <div class="stat-mini-label">No Deadline</div>
                    <div class="stat-mini-value"><?= (int)$noDeadline ?></div>
                </div>
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

        <!-- ============ CHART ============ -->
        <?php if ($num > 0): ?>
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <div class="chart-title">Task Distribution</div>
                        <div class="chart-subtitle">Breakdown of this user's assigned tasks by status</div>
                    </div>
                </div>
                <div id="piechart"></div>
            </div>
        <?php endif; ?>

        <!-- ============ TASKS TABLE ============ -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Assigned Tasks
                    <span class="section-count"><?= (int)$num_task ?></span>
                </div>
            </div>

            <?php if (!empty($tasks) && $tasks != 0): ?>
                <div class="table-card">
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; foreach ($tasks as $task):
                                $i++;
                                $noDeadline = ($task['due_date'] == "" || $task['due_date'] == "0000-00-00");

                                if ($task['status'] === "completed") {
                                    $statusLabel = 'Completed';
                                    $statusClass = 'badge-completed';
                                } elseif ($noDeadline) {
                                    $statusLabel = ucfirst(str_replace('_', ' ', $task['status']));
                                    $statusClass = match($task['status']) {
                                        'in_progress' => 'badge-progress',
                                        default       => 'badge-pending',
                                    };
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
                                    <td class="cell-title"><?= htmlspecialchars($task['title']) ?></td>
                                    <td class="cell-desc"><?= htmlspecialchars($task['description']) ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                    <td class="cell-date">
                                        <?php if ($noDeadline): ?>
                                            <span class="no-deadline">No Deadline</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($task['due_date']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="actions" style="justify-content:flex-end;">
                                            <a href="edit-task-department.php?id=<?= (int)$task['id'] ?>" class="action-btn action-edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <a href="delete-task-department.php?id=<?= (int)$task['id'] ?>" class="action-btn action-delete" onclick="return confirm('Delete this task?');">
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
                        <div class="empty-sub">This user doesn't have any tasks yet.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

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
        var el = document.getElementById('piechart');
        if (!el) return;

        var data = google.visualization.arrayToDataTable([
            ['Status', 'Number of tasks'],
            ['Pending',     <?= (int)$pending ?>],
            ['Completed',   <?= (int)$completed ?>],
            ['Overdue',     <?= (int)$overdue ?>],
            ['No Deadline', <?= (int)$noDeadline ?>],
            ['In Progress', <?= (int)$inProgress ?>]
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
            colors: ['#f59e0b', '#10b981', '#ef4444', '#94a3b8', '#0ea5e9'],
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