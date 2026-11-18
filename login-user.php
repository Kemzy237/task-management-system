<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login · Task Master</title>

    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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

            --radius:        16px;
            --radius-sm:     10px;
            --shadow-sm:     0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-md:     0 4px 16px rgba(15, 23, 42, 0.06);
            --shadow-lg:     0 20px 40px rgba(15, 23, 42, 0.12);
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-image:
                radial-gradient(circle at 15% 20%, rgba(14, 165, 233, 0.10) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(79, 70, 229, 0.08) 0%, transparent 40%);
        }

        a { text-decoration: none; color: inherit; }

        /* ============ AUTH CARD ============ */
        .auth-card {
            width: 100%;
            max-width: 960px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        /* ---------- Left: form ---------- */
        .auth-form-side {
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 36px;
            font-weight: 800;
            font-size: 15px;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .auth-brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .auth-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: var(--text);
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* ---------- Alerts ---------- */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 18px;
            border: 1px solid;
            line-height: 1.4;
        }
        .alert svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; }
        .alert-danger {
            background: var(--danger-soft);
            border-color: #fecaca;
            color: #b91c1c;
        }
        .alert-success {
            background: var(--success-soft);
            border-color: #a7f3d0;
            color: #047857;
        }

        /* ---------- Fields ---------- */
        .field { margin-bottom: 18px; }

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

        /* ---------- Submit button ---------- */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 13px 20px;
            margin-top: 6px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            background: var(--primary);
            color: #fff;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            transition: var(--transition);
        }
        .btn-submit:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 12px 24px rgba(79, 70, 229, 0.4); }
        .btn-submit svg { width: 16px; height: 16px; }

        .auth-footer-link {
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 24px;
        }
        .auth-footer-link a {
            color: var(--primary);
            font-weight: 600;
            transition: var(--transition);
        }
        .auth-footer-link a:hover { color: var(--primary-hover); }

        /* ---------- Role hint banner ---------- */
        .role-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            background: var(--info-soft);
            border: 1px solid #bae6fd;
            color: #0369a1;
            font-size: 12.5px;
            font-weight: 500;
            margin-bottom: 20px;
            line-height: 1.45;
        }
        .role-banner svg { width: 16px; height: 16px; flex-shrink: 0; color: var(--info); }
        .role-banner a {
            font-weight: 700;
            color: #0369a1;
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        .role-banner a:hover { color: var(--primary); }

        /* ---------- Right: side panel ---------- */
        .auth-side {
            position: relative;
            background: linear-gradient(160deg, #0ea5e9 0%, #4f46e5 100%);
            color: #fff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .auth-side::before {
            content: "";
            position: absolute;
            top: -30%;
            right: -30%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }
        .auth-side::after {
            content: "";
            position: absolute;
            bottom: -30%;
            left: -20%;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            pointer-events: none;
        }

        .side-top {
            position: relative;
            z-index: 1;
        }

        .side-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: grid;
            place-items: center;
            margin-bottom: 24px;
        }
        .side-icon svg { width: 26px; height: 26px; }

        .side-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 12px;
        }

        .side-text {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.9;
            max-width: 320px;
        }

        .side-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 28px;
            position: relative;
            z-index: 1;
        }

        .side-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13.5px;
            opacity: 0.95;
            line-height: 1.5;
        }

        .side-list svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 2px;
            opacity: 0.85;
        }

        .side-bottom {
            position: relative;
            z-index: 1;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .side-bottom-text {
            font-size: 12.5px;
            opacity: 0.85;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            transition: var(--transition);
        }
        .btn-back:hover { background: rgba(255, 255, 255, 0.25); }
        .btn-back svg { width: 13px; height: 13px; }

        /* ---------- Responsive ---------- */
        @media (max-width: 860px) {
            .auth-card { grid-template-columns: 1fr; max-width: 480px; }
            .auth-side { display: none; }
            .auth-form-side { padding: 36px 28px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <!-- ============ LEFT: FORM ============ -->
    <div class="auth-form-side">

        <a href="homepage.php" class="auth-brand">
            <span class="auth-brand-logo">TM</span>
            Task Master
        </a>

        <h1 class="auth-title">Employee Sign In</h1>
        <p class="auth-subtitle">Access your assigned tasks and update progress.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span><?= htmlspecialchars(stripcslashes($_GET['error'])) ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span><?= htmlspecialchars(stripcslashes($_GET['success'])) ?></span>
            </div>
        <?php endif; ?>

        <!-- Role hint -->
        <div class="role-banner">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <span>Employee account? You're in the right place. <a href="login-admin.php">Admin login →</a></span>
        </div>

        <form method="POST" action="app/login-user.php" autocomplete="off">

            <div class="field">
                <label class="field-label" for="username">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Username
                </label>
                <input
                    type="text"
                    id="username"
                    name="user_name"
                    class="input"
                    placeholder="Enter your username"
                    required
                    autofocus
                >
            </div>

            <div class="field">
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
                        placeholder="Enter your password"
                        required
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
            </div>

            <button type="submit" class="btn-submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Sign In
            </button>

            <p class="auth-footer-link">
                Need an account? <a href="register.php">Register</a>
            </p>
        </form>
    </div>

    <!-- ============ RIGHT: SIDE PANEL ============ -->
    <aside class="auth-side">
        <div class="side-top">
            <div class="side-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>

            <h2 class="side-title">Focus on your work.</h2>
            <p class="side-text">
                See exactly what's assigned to you, update your progress, and stay ahead of every deadline.
            </p>

            <ul class="side-list">
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    View all tasks assigned to you
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Update task status as you work
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Get notified about new assignments
                </li>
            </ul>
        </div>

        <div class="side-bottom">
            <span class="side-bottom-text">Not an employee?</span>
            <a href="homepage.php" class="btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to home
            </a>
        </div>
    </aside>

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