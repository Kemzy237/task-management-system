<?php
session_name("employee");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "employee") {
    $em = "First login";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/Task.php";
include "app/model/user.php";

if (!isset($_GET['id'])) {
    header("Location: my-task.php");
    exit();
}

$id   = (int)$_GET['id'];
$task = get_task_by_id($conn, $id);

if ($task == 0) {
    header("Location: my-task.php");
    exit();
}

/* Ownership guard: employees may only edit their own tasks */
if ((int)$task['assigned_to'] !== (int)$_SESSION['id']) {
    $em = "You don't have permission to edit this task.";
    header("Location: my-task.php?error=" . urlencode($em));
    exit();
}

$user = get_user_by_id($conn, $_SESSION['id']);

/* Status label helpers */
function task_status_label($status) {
    switch (strtolower((string)$status)) {
        case 'completed':   return 'Completed';
        case 'in_progress': return 'In Progress';
        case 'pending':     return 'Pending';
        default:            return ucfirst((string)$status);
    }
}
function task_status_tone($status) {
    switch (strtolower((string)$status)) {
        case 'completed':   return 'success';
        case 'in_progress': return 'primary';
        case 'pending':     return 'warning';
        default:            return 'neutral';
    }
}

$isCompleted = ($task['status'] === 'completed');
$currentTone = task_status_tone($task['status']);

$hasDeadline = !($task['due_date'] == "" || $task['due_date'] == "0000-00-00");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task · <?= htmlspecialchars($task['title']) ?></title>

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

        /* ============ FORM CARD ============ */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 360px;
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

        /* ============ READ-ONLY DISPLAY BLOCKS ============ */
        .info-block {
            margin-bottom: 20px;
        }

        .info-label {
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
        .info-label svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
            color: var(--text-subtle);
        }

        .info-value {
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface-alt);
            color: var(--text);
            font-size: 14px;
            line-height: 1.55;
        }

        .info-value.multiline {
            white-space: pre-wrap;
            word-break: break-word;
            min-height: 76px;
        }

        .info-value .muted {
            color: var(--text-subtle);
            font-style: italic;
        }

        /* ============ FIELDS ============ */
        .field { margin-bottom: 0; }

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

        .input, .select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
            line-height: 1.5;
            transition: var(--transition);
            appearance: none;
        }

        .select {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
            cursor: pointer;
        }

        .input:focus, .select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .field-hint {
            font-size: 12px;
            color: var(--text-subtle);
            margin-top: 6px;
        }
        .field-hint strong { color: var(--text-muted); font-weight: 600; }

        /* ============ STATUS OPTIONS GRID ============ */
        .status-options {
            display: grid;
            gap: 10px;
        }

        .status-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .status-option:hover {
            border-color: #d8deea;
            background: var(--surface-alt);
        }

        .status-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .status-radio-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid var(--border);
            flex-shrink: 0;
            display: grid;
            place-items: center;
            transition: var(--transition);
        }
        .status-radio-dot::after {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: transparent;
            transition: var(--transition);
        }

        .status-option-body { flex: 1; min-width: 0; }
        .status-option-title {
            font-weight: 600;
            font-size: 13.5px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .status-option-desc {
            font-size: 12px;
            color: var(--text-subtle);
            margin-top: 2px;
        }

        .status-dot-inline {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }
        .status-dot-inline.tone-warning { background: var(--warning); }
        .status-dot-inline.tone-primary { background: var(--primary); }
        .status-dot-inline.tone-success { background: var(--success); }

        .status-option:has(input:checked) {
            border-color: var(--primary);
            background: var(--primary-soft);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.10);
        }
        .status-option:has(input:checked) .status-radio-dot {
            border-color: var(--primary);
        }
        .status-option:has(input:checked) .status-radio-dot::after {
            background: var(--primary);
        }
        .status-option:has(input:checked) .status-option-title { color: var(--primary); }

        .status-option:has(input:disabled) {
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* ============ READ-ONLY STATUS ============ */
        .readonly-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: var(--success-soft);
            border: 1px solid #a7f3d0;
            border-radius: var(--radius-sm);
            color: #047857;
            font-weight: 600;
            font-size: 13.5px;
        }
        .readonly-status svg { width: 18px; height: 18px; flex-shrink: 0; }

        /* ============ PREVIEW ============ */
        .preview-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .preview-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .preview-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            margin: 0 auto;
        }
        .preview-icon svg { width: 26px; height: 26px; }

        .preview-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            text-align: center;
            letter-spacing: -0.01em;
            word-break: break-word;
        }

        .preview-desc {
            font-size: 12.5px;
            color: var(--text-muted);
            text-align: center;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.55;
        }
        .preview-desc.is-empty {
            color: var(--text-subtle);
            font-style: italic;
        }

        .preview-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 4px;
        }

        .preview-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .preview-chip svg { width: 11px; height: 11px; }

        .preview-chip.tone-success { background: var(--success-soft); color: #047857; border-color: #a7f3d0; }
        .preview-chip.tone-primary { background: var(--primary-soft); color: var(--primary); border-color: #c7d2fe; }
        .preview-chip.tone-warning { background: var(--warning-soft); color: #b45309; border-color: #fde68a; }
        .preview-chip.tone-neutral { background: var(--neutral-soft); color: var(--text-muted); border-color: var(--border); }
        .preview-chip.tone-danger  { background: var(--danger-soft);  color: #b91c1c; border-color: #fecaca; }

        .preview-chip::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .preview-chip.no-dot::before { display: none; }

        /* ============ META PILLS ============ */
        .meta-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding-top: 8px;
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
                    <a href="my-task.php">My Tasks</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span>Edit</span>
                </div>
                <h1 class="page-title">Edit Task</h1>
                <p class="page-subtitle">
                    Update the status of <strong><?= htmlspecialchars($task['title']) ?></strong>.
                </p>
            </div>
            <div class="page-actions">
                <a href="my-task.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    All Tasks
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
        <form class="form-card" method="POST" action="app/update-task-employee.php" autocomplete="off">
            <!-- Left column: fields -->
            <div class="form-main">

                <div class="form-section-title">Task Details</div>
                <div class="form-section-sub">Title and description are set by your department admin.</div>

                <div class="info-block">
                    <div class="info-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="6" x2="20" y2="6"/>
                            <line x1="4" y1="12" x2="20" y2="12"/>
                            <line x1="4" y1="18" x2="14" y2="18"/>
                        </svg>
                        Title
                    </div>
                    <div class="info-value">
                        <?= htmlspecialchars($task['title']) ?>
                    </div>
                </div>

                <div class="info-block">
                    <div class="info-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        Description
                    </div>
                    <div class="info-value multiline">
                        <?php if (trim((string)$task['description']) === ''): ?>
                            <span class="muted">No description provided.</span>
                        <?php else: ?>
                            <?= htmlspecialchars($task['description']) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-block" style="margin-bottom: 0;">
                    <div class="field-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Status
                    </div>

                    <?php if ($isCompleted): ?>
                        <div class="readonly-status">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            This task has been completed and can no longer be changed.
                        </div>
                    <?php else: ?>
                        <div class="status-options" role="radiogroup" aria-label="Task status">
                            <label class="status-option">
                                <input
                                    type="radio"
                                    name="status"
                                    value="pending"
                                    <?= $task['status'] === 'pending' ? 'checked' : '' ?>
                                >
                                <span class="status-radio-dot"></span>
                                <span class="status-option-body">
                                    <span class="status-option-title">
                                        <span class="status-dot-inline tone-warning"></span>
                                        Pending
                                    </span>
                                    <span class="status-option-desc">Not started yet.</span>
                                </span>
                            </label>

                            <label class="status-option">
                                <input
                                    type="radio"
                                    name="status"
                                    value="in_progress"
                                    <?= $task['status'] === 'in_progress' ? 'checked' : '' ?>
                                >
                                <span class="status-radio-dot"></span>
                                <span class="status-option-body">
                                    <span class="status-option-title">
                                        <span class="status-dot-inline tone-primary"></span>
                                        In Progress
                                    </span>
                                    <span class="status-option-desc">Currently working on it.</span>
                                </span>
                            </label>

                            <label class="status-option">
                                <input
                                    type="radio"
                                    name="status"
                                    value="completed"
                                    <?= $task['status'] === 'completed' ? 'checked' : '' ?>
                                >
                                <span class="status-radio-dot"></span>
                                <span class="status-option-body">
                                    <span class="status-option-title">
                                        <span class="status-dot-inline tone-success"></span>
                                        Completed
                                    </span>
                                    <span class="status-option-desc">Finished — this cannot be undone.</span>
                                </span>
                            </label>
                        </div>
                        <div class="field-hint">
                            Marking a task <strong>Completed</strong> is final and locks further edits.
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right column: live preview -->
            <aside class="form-aside">
                <div>
                    <div class="form-section-title">Preview</div>
                    <div class="form-section-sub">How the task will appear in your list.</div>
                </div>

                <div>
                    <div class="preview-label">Task Card</div>
                    <div class="preview-card">
                        <div class="preview-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                        </div>
                        <div class="preview-title"><?= htmlspecialchars($task['title']) ?></div>
                        <div class="preview-desc<?= trim((string)$task['description']) === '' ? ' is-empty' : '' ?>" id="previewDesc">
                            <?= trim((string)$task['description']) === '' ? 'No description provided.' : htmlspecialchars($task['description']) ?>
                        </div>
                        <div class="preview-badges">
                            <span class="preview-chip tone-<?= $currentTone ?>" id="previewStatusChip" data-initial-tone="<?= $currentTone ?>">
                                <?= htmlspecialchars(task_status_label($task['status'])) ?>
                            </span>
                            <?php if ($hasDeadline): ?>
                                <span class="preview-chip no-dot">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <?= htmlspecialchars($task['due_date']) ?>
                                </span>
                            <?php else: ?>
                                <span class="preview-chip no-dot">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                    No Deadline
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="meta-stack">
                    <span class="meta-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Task #<?= (int)$task['id'] ?>
                    </span>
                    <?php if (!empty($task['due_date']) && $hasDeadline): ?>
                        <span class="meta-pill">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Due <?= htmlspecialchars($task['due_date']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="field-hint" style="margin-top: 0;">
                    Status changes are visible to your department admin immediately.
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
                    <?php if ($isCompleted): ?>
                        This task is locked because it's completed.
                    <?php else: ?>
                        Changes take effect immediately after saving.
                    <?php endif; ?>
                </div>
                <div class="form-footer-actions">
                    <a href="my-task.php" class="btn btn-ghost">Cancel</a>
                    <?php if (!$isCompleted): ?>
                        <button type="submit" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                <polyline points="17 21 17 13 7 13 7 21"/>
                                <polyline points="7 3 7 8 15 8"/>
                            </svg>
                            Update Status
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
        </form>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>

<script>
    /* ---------- Live status preview ---------- */
    (function () {
        const radios = document.querySelectorAll('input[name="status"]');
        const chip   = document.getElementById('previewStatusChip');
        if (!radios.length || !chip) return;

        const labels = {
            pending:     'Pending',
            in_progress: 'In Progress',
            completed:   'Completed'
        };
        const tones = {
            pending:     'warning',
            in_progress: 'primary',
            completed:   'success'
        };

        function update(value) {
            if (!labels[value]) return;
            chip.textContent = labels[value];
            chip.classList.remove('tone-warning', 'tone-primary', 'tone-success', 'tone-neutral');
            chip.classList.add('tone-' + tones[value]);
        }

        radios.forEach(r => r.addEventListener('change', () => update(r.value)));

        const checked = document.querySelector('input[name="status"]:checked');
        if (checked) update(checked.value);
    })();
</script>
</body>
</html>