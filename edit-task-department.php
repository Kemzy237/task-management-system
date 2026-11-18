<?php
session_name("admin-department");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    $em = "First login";
    header("Location: login-admin.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/Task.php";
include "app/model/user.php";

$departmentId = (int)($_SESSION['department'] ?? 0);

if (!isset($_GET['id'])) {
    header("Location: task-department.php");
    exit();
}

$id = (int)$_GET['id'];
$task = get_task_by_id($conn, $id);

if ($task == 0) {
    header("Location: task-department.php");
    exit();
}

/* ---------- Department members (for assignee dropdown) ---------- */
$users = get_users_by_department($conn, ["employee", $departmentId]);
if ($users == 0 || empty($users)) $users = [];

/* ---------- Ownership guard: task must belong to this department ---------- */
$memberIds = array_map(fn($u) => (int)$u['id'], $users);

if (!in_array((int)$task['assigned_to'], $memberIds, true)) {
    $em = "You don't have permission to edit this task.";
    header("Location: task-department.php?error=" . urlencode($em));
    exit();
}

/* ---------- Current admin user ---------- */
$user = get_user_by_id($conn, $_SESSION['id']);

/* ---------- Normalize due_date for the input ---------- */
$dueDateValue = in_array($task['due_date'], ['', '0000-00-00', null], true) ? '' : $task['due_date'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task · Task Management</title>

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

        /* ============ FORM CARD ============ */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 320px;
        }

        .form-main {
            padding: 32px;
            border-right: 1px solid var(--border);
        }

        .form-aside {
            background: var(--surface-alt);
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-section-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 6px;
        }

        .form-section-sub {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* ============ FIELDS ============ */
        .field { margin-bottom: 20px; }
        .field:last-child { margin-bottom: 0; }

        .field-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .field-label svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
            color: var(--text-subtle);
        }

        .input,
        .textarea,
        .select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--text);
            font-family: inherit;
            font-size: 13.5px;
            line-height: 1.5;
            transition: var(--transition);
        }

        .textarea {
            resize: vertical;
            min-height: 120px;
        }

        .input:focus,
        .textarea:focus,
        .select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .input::placeholder,
        .textarea::placeholder { color: var(--text-subtle); }

        .select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 40px;
            cursor: pointer;
        }

        .field-hint {
            font-size: 12px;
            color: var(--text-subtle);
            margin-top: 6px;
        }

        /* ============ STICKY FOOTER ============ */
        .form-footer {
            grid-column: 1 / -1;
            padding: 20px 32px;
            background: var(--surface);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .form-footer-info {
            font-size: 12.5px;
            color: var(--text-subtle);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-footer-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ============ META PILLS ============ */
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
            .form-card { grid-template-columns: 1fr; }
            .form-main { border-right: none; border-bottom: 1px solid var(--border); }
        }

        @media (max-width: 960px) {
            .app-main {
                margin-left: 0;
                width: 100%;
                padding: 76px 16px 40px;
            }
            .page-title { font-size: 22px; }
            .form-main, .form-aside { padding: 24px 20px; }
            .form-footer { padding: 16px 20px; }
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
                    <a href="task-department.php">Department Tasks</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span>Edit Task</span>
                </div>
                <h1 class="page-title">Edit Task</h1>
                <p class="page-subtitle">
                    Update the details, deadline, status, and assignee for this department task.
                </p>
            </div>
            <div class="page-actions">
                <a href="task-department.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back to Tasks
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

        <!-- ============ FORM ============ -->
        <form class="form-card" method="POST" action="app/update-task-department.php">
            <!-- Left column: primary fields -->
            <div class="form-main">
                <div class="field">
                    <label class="field-label" for="title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        Task Title
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="input"
                        placeholder="Enter task title"
                        value="<?= htmlspecialchars($task['title']) ?>"
                        required
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="description">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        class="textarea"
                        placeholder="Describe the task, expected outcome, or any relevant notes..."
                    ><?= htmlspecialchars($task['description']) ?></textarea>
                    <div class="field-hint">Give your team enough context to understand what needs to be done.</div>
                </div>
            </div>

            <!-- Right column: settings -->
            <aside class="form-aside">
                <div>
                    <div class="form-section-title">Task Settings</div>
                    <div class="form-section-sub">Control when and by whom this task should be completed.</div>
                </div>

                <div class="field">
                    <label class="field-label" for="due_date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Deadline
                    </label>
                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        class="input"
                        value="<?= htmlspecialchars($dueDateValue) ?>"
                    >
                    <div class="field-hint">Leave blank if this task has no deadline.</div>
                </div>

                <div class="field">
                    <label class="field-label" for="status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        </svg>
                        Status
                    </label>
                    <select id="status" name="status" class="select">
                        <option value="pending"     <?= $task['status'] === 'pending'     ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed"   <?= $task['status'] === 'completed'   ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="assigned_to">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Assigned To
                    </label>
                    <select id="assigned_to" name="assigned_to" class="select">
                        <option value="0">Unassigned</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int)$u['id'] ?>" <?= (int)$task['assigned_to'] === (int)$u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-hint">Only employees in your department can be assigned.</div>
                </div>

                <div style="display:flex; flex-wrap:wrap; gap:8px; padding-top:8px;">
                    <span class="meta-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Task #<?= (int)$task['id'] ?>
                    </span>
                    <?php if (!empty($task['created_at'])): ?>
                        <span class="meta-pill">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Created <?= date('M j, Y', strtotime($task['created_at'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Shared footer -->
            <div class="form-footer">
                <div class="form-footer-info">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Changes take effect immediately after saving.
                </div>
                <div class="form-footer-actions">
                    <a href="task-department.php" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>

            <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
        </form>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>
</body>
</html>