<?php
include "DB_connection.php";
include "app/model/Department.php";

$departments = get_all_departments($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register · Task Management System</title>

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
            --shadow-lg:     0 20px 60px rgba(15, 23, 42, 0.10);
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
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background-image:
                radial-gradient(circle at 15% 10%, rgba(79, 70, 229, 0.10), transparent 45%),
                radial-gradient(circle at 85% 90%, rgba(124, 58, 237, 0.08), transparent 45%);
        }

        a { text-decoration: none; color: inherit; }

        /* ============ AUTH CARD ============ */
        .auth-card {
            width: 100%;
            max-width: 960px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            display: grid;
            grid-template-columns: 380px 1fr;
        }

        /* ============ ASIDE / BRAND PANEL ============ */
        .auth-aside {
            position: relative;
            padding: 40px 36px;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.14), transparent 55%),
                linear-gradient(150deg, #4f46e5 0%, #6d28d9 60%, #7c3aed 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
            overflow: hidden;
        }

        .auth-aside::before {
            content: "";
            position: absolute;
            bottom: -120px;
            left: -120px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            pointer-events: none;
        }
        .auth-aside::after {
            content: "";
            position: absolute;
            top: -80px;
            right: -80px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            pointer-events: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.25);
            display: grid;
            place-items: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .brand-logo img { width: 100%; height: 100%; object-fit: cover; }

        .brand-name {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .brand-tag {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .aside-headline {
            position: relative;
            z-index: 1;
        }
        .aside-title {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
        }
        .aside-text {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.6;
        }

        .aside-points {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: relative;
            z-index: 1;
        }
        .aside-point {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.88);
        }
        .aside-point-icon {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .aside-point-icon svg {
            width: 12px; height: 12px;
            fill: none; stroke: currentColor; stroke-width: 2.5;
            stroke-linecap: round; stroke-linejoin: round;
        }

        /* ============ FORM PANEL ============ */
        .auth-main {
            padding: 40px 40px 32px;
        }

        .auth-header {
            margin-bottom: 24px;
        }

        .auth-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid #c7d2fe;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 12px;
        }
        .auth-eyebrow svg {
            width: 11px; height: 11px;
            fill: none; stroke: currentColor; stroke-width: 2.5;
            stroke-linecap: round; stroke-linejoin: round;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text);
            margin-bottom: 4px;
        }

        .auth-sub {
            font-size: 13.5px;
            color: var(--text-muted);
        }

        /* ============ ALERTS ============ */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 18px;
            border: 1px solid;
        }
        .alert svg { flex-shrink: 0; width: 15px; height: 15px; }
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

        /* ============ FIELDS ============ */
        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
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

        /* ============ SUBMIT ============ */
        .form-actions {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
        }
        .btn svg { width: 15px; height: 15px; }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28);
            width: 100%;
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-primary:active { transform: translateY(0); }

        .auth-footnote {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }
        .auth-footnote a {
            color: var(--primary);
            font-weight: 600;
            transition: color var(--transition);
        }
        .auth-footnote a:hover { color: var(--primary-hover); }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 12.5px;
            color: var(--text-subtle);
            font-weight: 500;
            margin-top: 4px;
            transition: color var(--transition);
        }
        .back-link:hover { color: var(--primary); }
        .back-link svg { width: 12px; height: 12px; }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 860px) {
            .auth-card { grid-template-columns: 1fr; max-width: 520px; }
            .auth-aside { padding: 28px; }
            .aside-points { display: none; }
            .aside-title { font-size: 22px; }
            .auth-main { padding: 28px 24px; }
        }

        @media (max-width: 520px) {
            body { padding: 16px; }
            .auth-main { padding: 24px 20px; }
            .field-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="auth-card">

        <!-- ============ BRAND / ASIDE ============ -->
        <aside class="auth-aside">
            <div class="brand">
                <div class="brand-logo">
                    <img src="favicon/android-chrome-192x192.png" alt="">
                </div>
                <div>
                    <div class="brand-name">Task Management</div>
                    <div class="brand-tag">Register your account</div>
                </div>
            </div>

            <div class="aside-headline">
                <div class="aside-title">Join your team's workspace.</div>
                <p class="aside-text">
                    Create an account to receive tasks, track deadlines, and stay in sync with your department.
                </p>
            </div>

            <ul class="aside-points">
                <li class="aside-point">
                    <span class="aside-point-icon">
                        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    <span>See every task assigned to you in one place.</span>
                </li>
                <li class="aside-point">
                    <span class="aside-point-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                    <span>Stay ahead of overdue and due-today deadlines.</span>
                </li>
                <li class="aside-point">
                    <span class="aside-point-icon">
                        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </span>
                    <span>Get notified the moment work is assigned to you.</span>
                </li>
            </ul>
        </aside>

        <!-- ============ FORM ============ -->
        <main class="auth-main">

            <div class="auth-header">
                <span class="auth-eyebrow">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New account
                </span>
                <h1 class="auth-title">Create your account</h1>
                <p class="auth-sub">Fill in the details below to register.</p>
            </div>

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

            <form method="POST" action="app/register.php" autocomplete="off">
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
                        <div class="field-hint">This will be your login name.</div>
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
                            pattern="\d{1,10}"
                            title="Up to 10 digits"
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
                                autocomplete="new-password"
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
                        <div class="field-hint">At least <strong>6 characters</strong>.</div>
                    </div>

                    <div class="field full">
                        <label class="field-label" for="department">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21h18"/>
                                <path d="M5 21V7l8-4v18"/>
                                <path d="M19 21V11l-6-4"/>
                            </svg>
                            Department
                        </label>
                        <select name="department" id="department" class="select" required>
                            <option value="0" disabled selected>Select department</option>
                            <?php if ($departments != 0): ?>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?= (int)$department['id'] ?>">
                                        <?= htmlspecialchars($department['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="field-hint">Choose the department you belong to.</div>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Create account
                    </button>

                    <div class="auth-footnote">
                        Already have an account? <a href="login.php">Sign in</a>
                    </div>

                    <a href="homepage.php" class="back-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Back to home
                    </a>
                </div>
            </form>

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
    </script>
</body>
</html>