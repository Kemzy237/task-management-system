<?php
session_name("admin-department");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "admin") {
    $em = "First login";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/user.php";
include "app/model/Department.php";

$user        = get_user_by_id($conn, $_SESSION['id']);
$departments = get_all_departments($conn);

$departmentId   = (int)($_SESSION['department'] ?? 0);
$currentDeptName = 'No department';
foreach ($departments as $d) {
    if ((int)$d['id'] === $departmentId) {
        $currentDeptName = $d['name'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User · Task Management</title>

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

        /* ============ FIELDS ============ */
        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .field { margin-bottom: 0; }
        .field.full { grid-column: 1 / -1; }

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

        .input {
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
        }

        .input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .input::placeholder { color: var(--text-subtle); }

        .field-hint {
            font-size: 12px;
            color: var(--text-subtle);
            margin-top: 6px;
        }
        .field-hint strong { color: var(--text-muted); font-weight: 600; }

        /* Input with trailing toggle */
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap .input { padding-right: 44px; }
        .input-toggle {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border: none;
            background: transparent;
            color: var(--text-subtle);
            cursor: pointer;
            border-radius: 8px;
            transition: var(--transition);
        }
        .input-toggle:hover { background: var(--surface-alt); color: var(--text); }
        .input-toggle svg { width: 16px; height: 16px; }

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
            align-items: center;
            gap: 12px;
            text-align: center;
        }

        .preview-avatar {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 28px;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            transition: var(--transition);
        }

        .preview-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.01em;
            min-height: 22px;
        }

        .preview-name.is-empty {
            color: var(--text-subtle);
            font-weight: 500;
            font-style: italic;
        }

        .preview-username {
            font-size: 13px;
            color: var(--text-muted);
            min-height: 18px;
        }

        .preview-chips {
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
        .preview-chip.role-employee { background: var(--info-soft);    color: #0369a1; border-color: #bae6fd; }
        .preview-chip.dept          { background: var(--primary-soft); color: var(--primary); border-color: #c7d2fe; }

        .preview-hint {
            font-size: 12px;
            color: var(--text-subtle);
            line-height: 1.55;
        }

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
            .field-grid { grid-template-columns: 1fr; }
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
                    <a href="user-department.php">Manage Users</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span>Add</span>
                </div>
                <h1 class="page-title">Add User</h1>
                <p class="page-subtitle">
                    Create a new employee account in <strong><?= htmlspecialchars($currentDeptName) ?></strong>.
                </p>
            </div>
            <div class="page-actions">
                <a href="user-department.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"/>
                        <polyline points="12 19 5 12 12 5"/>
                    </svg>
                    Back to Users
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
        <form class="form-card" method="POST" action="app/add-user-department.php" autocomplete="off">
            <!-- Left column: fields -->
            <div class="form-main">
                <div class="field-grid">

                    <div class="field full">
                        <label class="field-label" for="fullname">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Full Name
                        </label>
                        <input
                            type="text"
                            id="fullname"
                            name="full_name"
                            class="input"
                            placeholder="e.g. Jane Doe"
                            required
                            autofocus
                            maxlength="50"
                        >
                    </div>

                    <div class="field">
                        <label class="field-label" for="username">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="5"/>
                                <path d="M20 21a8 8 0 0 0-16 0"/>
                            </svg>
                            Username
                        </label>
                        <input
                            type="text"
                            id="username"
                            name="user_name"
                            class="input"
                            placeholder="janedoe"
                            required
                            maxlength="50"
                            pattern="[A-Za-z0-9_\.\-]+"
                            title="Letters, numbers, dots, dashes and underscores only"
                        >
                        <div class="field-hint">This will be the user's login name.</div>
                    </div>

                    <div class="field">
                        <label class="field-label" for="contact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            Contact
                        </label>
                        <input
                            type="text"
                            id="contact"
                            name="contact"
                            class="input"
                            placeholder="712345678"
                            required
                            maxlength="10"
                            inputmode="numeric"
                        >
                        <div class="field-hint">Up to 10 digits.</div>
                    </div>

                    <div class="field full">
                        <label class="field-label" for="password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Password
                        </label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="input"
                                placeholder="Minimum 6 characters"
                                required
                                minlength="6"
                            >
                            <button type="button" class="input-toggle" id="togglePassword" aria-label="Show password">
                                <svg id="iconEye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg id="iconEyeOff" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <div class="field-hint">
                            At least <strong>6 characters</strong>. Share it with the employee so they can sign in.
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right column: live preview -->
            <aside class="form-aside">
                <div>
                    <div class="form-section-title">Preview</div>
                    <div class="form-section-sub">This is how the user will appear after saving.</div>
                </div>

                <div>
                    <div class="preview-label">Profile Card</div>
                    <div class="preview-card">
                        <div class="preview-avatar" id="previewAvatar">?</div>
                        <div class="preview-name is-empty" id="previewName">Full name</div>
                        <div class="preview-username" id="previewUsername">@username</div>
                        <div class="preview-chips">
                            <span class="preview-chip role-employee">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Employee
                            </span>
                            <span class="preview-chip dept">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 21h18"/>
                                    <path d="M5 21V7l8-4v18"/>
                                    <path d="M19 21V11l-6-4"/>
                                </svg>
                                <?= htmlspecialchars($currentDeptName) ?>
                            </span>
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
                        New account
                    </span>
                    <span class="meta-pill">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Created today
                    </span>
                </div>

                <div class="preview-hint">
                    The account is created in your own department (<strong><?= htmlspecialchars($currentDeptName) ?></strong>). To place the employee in another department, ask your system administrator.
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
                    The account will be active immediately after saving.
                </div>
                <div class="form-footer-actions">
                    <a href="user-department.php" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Create User
                    </button>
                </div>
            </div>

            <!-- Hidden fields kept from original (department + role) -->
            <input type="hidden" name="department" value="<?= (int)$_SESSION['department'] ?>">
            <input type="hidden" name="role" value="employee">
        </form>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>

<script>
    /* ---------- Password visibility toggle ---------- */
    (function () {
        const toggle = document.getElementById('togglePassword');
        const input  = document.getElementById('password');
        const eye    = document.getElementById('iconEye');
        const eyeOff = document.getElementById('iconEyeOff');
        if (!toggle || !input) return;

        toggle.addEventListener('click', function () {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            eye.style.display    = showing ? '' : 'none';
            eyeOff.style.display = showing ? 'none' : '';
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        });
    })();

    /* ---------- Live preview ---------- */
    (function () {
        const fullName = document.getElementById('fullname');
        const username = document.getElementById('username');

        const avatar      = document.getElementById('previewAvatar');
        const previewName = document.getElementById('previewName');
        const previewUser = document.getElementById('previewUsername');

        if (!fullName) return;

        function update() {
            const name = fullName.value.trim();
            const user = username.value.trim();

            if (name) {
                avatar.textContent = name.charAt(0).toUpperCase();
                previewName.textContent = name;
                previewName.classList.remove('is-empty');
            } else if (user) {
                avatar.textContent = user.charAt(0).toUpperCase();
                previewName.textContent = 'Full name';
                previewName.classList.add('is-empty');
            } else {
                avatar.textContent = '?';
                previewName.textContent = 'Full name';
                previewName.classList.add('is-empty');
            }

            previewUser.textContent = user ? '@' + user : '@username';
        }

        [fullName, username].forEach(el => {
            el.addEventListener('input', update);
        });
        update();
    })();
</script>
</body>
</html>