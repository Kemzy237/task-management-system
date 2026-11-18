<?php
session_name("employee");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== "employee") {
    $em = "First login";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

include "DB_connection.php";
include "app/model/user.php";

$user = get_user_by_id($conn, $_SESSION['id']);

if ($user == 0) {
    $em = "User not found.";
    header("Location: login.php?error=" . urlencode($em));
    exit();
}

$initial = strtoupper(substr((string)$user['full_name'], 0, 1) ?: '?');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile · <?= htmlspecialchars($user['full_name']) ?></title>

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

        /* ============ SECTION DIVIDER ============ */
        .section-divider {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 4px 0;
        }
        .section-divider::before,
        .section-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        .section-divider span {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            white-space: nowrap;
        }

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
            overflow: hidden;
        }
        .preview-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }

        .preview-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.01em;
            min-height: 22px;
            word-break: break-word;
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
        .preview-chip.tone-success { background: var(--success-soft); color: #047857; border-color: #a7f3d0; }
        .preview-chip.tone-primary { background: var(--primary-soft); color: var(--primary); border-color: #c7d2fe; }
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

        /* ============ PASSWORD STRENGTH ============ */
        .strength-bar {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }
        .strength-seg {
            flex: 1;
            height: 5px;
            border-radius: 3px;
            background: var(--border);
            transition: background var(--transition);
        }
        .strength-seg.on-weak   { background: var(--danger); }
        .strength-seg.on-fair   { background: var(--warning); }
        .strength-seg.on-good   { background: var(--info); }
        .strength-seg.on-strong { background: var(--success); }

        .strength-text {
            font-size: 11.5px;
            font-weight: 600;
            margin-top: 6px;
            color: var(--text-subtle);
        }
        .strength-text.tone-weak   { color: var(--danger); }
        .strength-text.tone-fair   { color: #b45309; }
        .strength-text.tone-good   { color: #0369a1; }
        .strength-text.tone-strong { color: #047857; }

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
                    <a href="profile.php">Profile</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                    <span>Edit</span>
                </div>
                <h1 class="page-title">Edit Profile</h1>
                <p class="page-subtitle">
                    Update your display name or change your password.
                </p>
            </div>
            <div class="page-actions">
                <a href="profile.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    View Profile
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
        <form class="form-card" method="POST" action="app/update-profile.php" autocomplete="off">
            <!-- Left column: fields -->
            <div class="form-main">
                <div class="form-section-title">Account</div>
                <div class="form-section-sub">Your display name is visible to your department admin and teammates.</div>

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
                            value="<?= htmlspecialchars($user['full_name']) ?>"
                            required
                            autofocus
                            maxlength="50"
                        >
                    </div>

                    <div class="section-divider">
                        <span>Change password (optional)</span>
                    </div>

                    <div class="field full">
                        <label class="field-label" for="password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Current Password
                        </label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="input"
                                placeholder="Enter your current password"
                                autocomplete="current-password"
                            >
                            <button type="button" class="input-toggle" data-target="password" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="field-hint">
                            Only needed if you want to set a new password.
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label" for="new_password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            New Password
                        </label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="input"
                                placeholder="Minimum 6 characters"
                                minlength="6"
                                autocomplete="new-password"
                            >
                            <button type="button" class="input-toggle" data-target="new_password" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="strength-bar" id="strengthBar" aria-hidden="true">
                            <span class="strength-seg"></span>
                            <span class="strength-seg"></span>
                            <span class="strength-seg"></span>
                            <span class="strength-seg"></span>
                        </div>
                        <div class="strength-text" id="strengthText">Enter a new password to see strength.</div>
                    </div>

                    <div class="field">
                        <label class="field-label" for="confirm_password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Confirm New Password
                        </label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="input"
                                placeholder="Re-enter new password"
                                minlength="6"
                                autocomplete="new-password"
                            >
                            <button type="button" class="input-toggle" data-target="confirm_password" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div class="field-hint" id="matchHint">
                            Must match the new password.
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right column: live preview -->
            <aside class="form-aside">
                <div>
                    <div class="form-section-title">Preview</div>
                    <div class="form-section-sub">How your profile will look after saving.</div>
                </div>

                <div>
                    <div class="preview-label">Profile Card</div>
                    <div class="preview-card">
                        <div class="preview-avatar" id="previewAvatar">
                            <?php if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])): ?>
                                <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="">
                            <?php else: ?>
                                <?= htmlspecialchars($initial) ?>
                            <?php endif; ?>
                        </div>
                        <div class="preview-name" id="previewName"><?= htmlspecialchars($user['full_name']) ?></div>
                        <div class="preview-username">@<?= htmlspecialchars($user['username']) ?></div>
                        <div class="preview-chips">
                            <span class="preview-chip tone-success">Employee</span>
                            <span class="preview-chip no-dot">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <?= htmlspecialchars($user['username']) ?>
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
                        User #<?= (int)$user['id'] ?>
                    </span>
                    <?php if (!empty($user['created_at']) && $user['created_at'] !== '0000-00-00 00:00:00'): ?>
                        <?php $ts = strtotime($user['created_at']); ?>
                        <?php if ($ts !== false): ?>
                            <span class="meta-pill">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                Joined <?= htmlspecialchars(date('M j, Y', $ts)) ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="field-hint" style="margin-top: 0;">
                    Your username, contact, and department are managed by your administrator and can't be changed here.
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
                    <a href="profile.php" class="btn btn-ghost">Cancel</a>
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
        </form>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>

<script>
    /* ---------- Password visibility toggle (per field) ---------- */
    (function () {
        document.querySelectorAll('.input-toggle').forEach(function (toggle) {
            const targetId = toggle.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            toggle.addEventListener('click', function () {
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });
    })();

    /* ---------- Live name preview ---------- */
    (function () {
        const fullName = document.getElementById('fullname');
        const avatar   = document.getElementById('previewAvatar');
        const nameEl   = document.getElementById('previewName');
        if (!fullName) return;

        function update() {
            const name = fullName.value.trim();
            if (name) {
                // Only update the letter if there's no profile image
                if (!avatar.querySelector('img')) {
                    avatar.textContent = name.charAt(0).toUpperCase();
                }
                nameEl.textContent = name;
                nameEl.classList.remove('is-empty');
            } else {
                if (!avatar.querySelector('img')) avatar.textContent = '?';
                nameEl.textContent = 'Full name';
                nameEl.classList.add('is-empty');
            }
        }

        fullName.addEventListener('input', update);
        update();
    })();

    /* ---------- Password strength ---------- */
    (function () {
        const input    = document.getElementById('new_password');
        const bar      = document.getElementById('strengthBar');
        const text     = document.getElementById('strengthText');
        if (!input || !bar || !text) return;

        const segs = bar.querySelectorAll('.strength-seg');

        function scorePassword(pw) {
            if (!pw) return 0;
            let score = 0;
            if (pw.length >= 6) score++;
            if (pw.length >= 10) score++;
            if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
            if (/\d/.test(pw) && /[^A-Za-z0-9]/.test(pw)) score++;
            return Math.min(score, 4);
        }

        function apply(score) {
            const labels = {
                0: { text: 'Enter a new password to see strength.', tone: '' },
                1: { text: 'Weak',   tone: 'weak' },
                2: { text: 'Fair',   tone: 'fair' },
                3: { text: 'Good',   tone: 'good' },
                4: { text: 'Strong', tone: 'strong' }
            };
            const conf = labels[score] || labels[0];

            segs.forEach(function (seg, i) {
                seg.classList.remove('on-weak', 'on-fair', 'on-good', 'on-strong');
                if (i < score) seg.classList.add('on-' + conf.tone);
            });

            text.textContent = conf.text;
            text.classList.remove('tone-weak', 'tone-fair', 'tone-good', 'tone-strong');
            if (conf.tone) text.classList.add('tone-' + conf.tone);
        }

        input.addEventListener('input', function () {
            apply(scorePassword(input.value));
        });
    })();

    /* ---------- Password match check ---------- */
    (function () {
        const pw      = document.getElementById('new_password');
        const confirm = document.getElementById('confirm_password');
        const hint    = document.getElementById('matchHint');
        if (!pw || !confirm || !hint) return;

        function check() {
            if (!confirm.value) {
                hint.textContent = 'Must match the new password.';
                hint.style.color = '';
                confirm.setCustomValidity('');
                return;
            }
            if (pw.value === confirm.value) {
                hint.textContent = 'Passwords match.';
                hint.style.color = 'var(--success)';
                confirm.setCustomValidity('');
            } else {
                hint.textContent = 'Passwords do not match.';
                hint.style.color = 'var(--danger)';
                confirm.setCustomValidity('Passwords do not match.');
            }
        }

        pw.addEventListener('input', check);
        confirm.addEventListener('input', check);
    })();
</script>
</body>
</html>