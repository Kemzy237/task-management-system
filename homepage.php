<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Task Master — assign and track tasks efficiently across your team.">
    <title>Task Master · Assign tasks efficiently</title>

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
            --text-inverse:  #ffffff;

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
        html { scroll-behavior: smooth; }
        html, body { height: 100%; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 15px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; color: inherit; }

        /* ============ HEADER ============ */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: saturate(180%) blur(12px);
            -webkit-backdrop-filter: saturate(180%) blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 17px;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .brand-logo {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }

        .nav-links a {
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .nav-links a:hover {
            background: var(--surface-alt);
            color: var(--text);
        }

        .nav-links a.is-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .nav-links a.is-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .mobile-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border: 1px solid var(--border);
            background: var(--surface);
            border-radius: 10px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text);
        }

        .mobile-toggle svg { width: 20px; height: 20px; }

        /* ============ HERO ============ */
        .hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 20% 0%, rgba(79, 70, 229, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 100%, rgba(124, 58, 237, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, #ffffff 0%, #f4f6fb 100%);
            padding: 96px 24px 120px;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse at center, #000 30%, transparent 75%);
            -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 75%);
            pointer-events: none;
        }

        .hero-inner {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 100px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 28px;
            box-shadow: var(--shadow-sm);
        }

        .hero-eyebrow .dot {
            width: 6px;
            height: 6px;
            background: var(--success);
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .hero h1 {
            font-size: clamp(36px, 6vw, 64px);
            font-weight: 900;
            letter-spacing: -0.035em;
            line-height: 1.05;
            color: var(--text);
            margin-bottom: 20px;
        }

        .hero h1 .accent {
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-sub {
            font-size: clamp(16px, 2vw, 19px);
            color: var(--text-muted);
            max-width: 640px;
            margin: 0 auto 36px;
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 22px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
            white-space: nowrap;
        }

        .btn svg { width: 16px; height: 16px; }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 12px 28px rgba(79, 70, 229, 0.4); }

        .btn-ghost {
            background: var(--surface);
            color: var(--text);
            border-color: var(--border);
        }
        .btn-ghost:hover { background: var(--surface-alt); border-color: var(--text-subtle); }

        /* ============ SECTION ============ */
        .section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 24px;
        }

        .section-header {
            text-align: center;
            max-width: 640px;
            margin: 0 auto 48px;
        }

        .section-eyebrow {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: clamp(26px, 3.5vw, 36px);
            font-weight: 800;
            letter-spacing: -0.025em;
            color: var(--text);
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .section-sub {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* ============ FEATURE CARDS ============ */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            margin-bottom: 18px;
            background: var(--primary-soft);
            color: var(--primary);
        }
        .feature-icon.success { background: var(--success-soft); color: var(--success); }
        .feature-icon.warning { background: var(--warning-soft); color: var(--warning); }
        .feature-icon.info    { background: var(--info-soft);    color: var(--info); }
        .feature-icon.danger  { background: var(--danger-soft);  color: var(--danger); }

        .feature-icon svg { width: 22px; height: 22px; }

        .feature-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text);
            margin-bottom: 8px;
        }

        .feature-text {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* ============ ROLE CARDS ============ */
        .roles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 22px;
        }

        .role-card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            overflow: hidden;
        }

        .role-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, #7c3aed 100%);
        }

        .role-card.is-admin::before {
            background: linear-gradient(90deg, #ef4444 0%, #f59e0b 100%);
        }

        .role-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: var(--primary-soft);
            color: var(--primary);
        }
        .role-card.is-admin .role-icon {
            background: var(--danger-soft);
            color: var(--danger);
        }
        .role-icon svg { width: 24px; height: 24px; }

        .role-title {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.015em;
            color: var(--text);
        }

        .role-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .role-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 4px;
        }

        .role-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13.5px;
            color: var(--text-muted);
        }

        .role-list li svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 2px;
            color: var(--success);
        }

        .role-cta {
            margin-top: auto;
            padding-top: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--primary);
            transition: var(--transition);
        }
        .role-cta:hover { gap: 10px; color: var(--primary-hover); }
        .role-card.is-admin .role-cta { color: var(--danger); }
        .role-card.is-admin .role-cta:hover { color: #b91c1c; }

        .role-cta svg { width: 14px; height: 14px; }

        /* ============ CTA BAND ============ */
        .cta-band {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px 80px;
        }

        .cta-inner {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 56px 40px;
            text-align: center;
            color: #fff;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.3);
        }

        .cta-inner::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }

        .cta-inner::after {
            content: "";
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .cta-inner h2 {
            position: relative;
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
        }

        .cta-inner p {
            position: relative;
            font-size: 15px;
            opacity: 0.9;
            max-width: 560px;
            margin: 0 auto 28px;
            line-height: 1.6;
        }

        .cta-actions {
            position: relative;
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-white {
            background: #fff;
            color: var(--primary);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .btn-white:hover { background: #f8fafc; transform: translateY(-2px); }

        .btn-outline-white {
            background: transparent;
            color: #fff;
            border-color: rgba(255, 255, 255, 0.4);
        }
        .btn-outline-white:hover { background: rgba(255, 255, 255, 0.1); border-color: #fff; }

        /* ============ FOOTER ============ */
        .site-footer {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 48px 24px 32px;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .footer-brand {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            flex-direction: column;
        }

        .footer-brand p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 320px;
        }

        .footer-col h4 {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 14px;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-col a {
            font-size: 13.5px;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .footer-col a:hover { color: var(--primary); }

        .footer-bottom {
            max-width: 1200px;
            margin: 32px auto 0;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 12.5px;
            color: var(--text-subtle);
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 900px) {
            .footer-inner {
                grid-template-columns: 1fr;
                gap: 32px;
            }
        }

        @media (max-width: 720px) {
            .mobile-toggle { display: inline-flex; }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--surface);
                border-bottom: 1px solid var(--border);
                padding: 12px 16px 16px;
                flex-direction: column;
                align-items: stretch;
                box-shadow: var(--shadow-md);
            }

            .nav-links.is-open { display: flex; }

            .nav-links a {
                padding: 12px 14px;
                text-align: center;
            }

            .header-inner { position: relative; }

            .hero { padding: 64px 20px 80px; }
            .section { padding: 56px 20px; }
            .cta-inner { padding: 44px 24px; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            * { transition: none !important; }
        }
    </style>
</head>
<body>

<!-- ============ HEADER ============ -->
<header class="site-header">
    <div class="header-inner">
        <a href="login.php" class="brand">
            <span class="brand-logo">TM</span>
            Task Master
        </a>

        <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navLinks">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        <ul class="nav-links" id="navLinks">
            <li><a href="register.php">Register</a></li>
            <li><a href="login-user.php">User Login</a></li>
            <li><a href="login-admin.php" class="is-primary">Department Admin</a></li>
        </ul>
    </div>
</header>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-eyebrow">
            <span class="dot"></span>
            Task management, simplified
        </div>

        <h1>
            Assign work.<br>
            <span class="accent">Get it done.</span>
        </h1>

        <p class="hero-sub">
            Task Master keeps your team aligned — assign tasks, track progress, and never lose sight of a deadline again.
        </p>

        <div class="hero-actions">
            <a href="login-admin.php" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Admin Sign In
            </a>
            <a href="login-user.php" class="btn btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Employee Sign In
            </a>
        </div>
    </div>
</section>

<!-- ============ FEATURES ============ -->
<section class="section">
    <div class="section-header">
        <div class="section-eyebrow">Why Task Master</div>
        <h2 class="section-title">Everything you need to stay organized</h2>
        <p class="section-sub">
            From assigning tasks to tracking completion, Task Master gives your team the tools to move work forward.
        </p>
    </div>

    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="feature-title">Task Assignment</div>
            <div class="feature-text">Assign tasks to individuals or departments in a couple of clicks. Every task has a clear owner.</div>
        </div>

        <div class="feature-card">
            <div class="feature-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="feature-title">Deadline Tracking</div>
            <div class="feature-text">Set due dates and get clear visual indicators when tasks are due soon or already overdue.</div>
        </div>

        <div class="feature-card">
            <div class="feature-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18"/>
                    <path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>
                </svg>
            </div>
            <div class="feature-title">Progress Dashboards</div>
            <div class="feature-text">See what's pending, in progress, and completed — across your whole team — at a glance.</div>
        </div>

        <div class="feature-card">
            <div class="feature-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="feature-title">Team Departments</div>
            <div class="feature-text">Organize users into departments so tasks and reporting stay clean and scoped.</div>
        </div>

        <div class="feature-card">
            <div class="feature-icon danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <div class="feature-title">Notifications</div>
            <div class="feature-text">Task assignments, deadline reminders, and status changes land right in your inbox.</div>
        </div>

        <div class="feature-card">
            <div class="feature-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <div class="feature-title">Role-Based Access</div>
            <div class="feature-text">Admins manage users, departments, and all tasks. Employees see only what's assigned to them.</div>
        </div>
    </div>
</section>

<!-- ============ ROLES ============ -->
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div class="section-eyebrow">Built for two roles</div>
        <h2 class="section-title">Different needs, same workspace</h2>
        <p class="section-sub">
            Whether you're organizing a team or doing the work, Task Master adapts to you.
        </p>
    </div>

    <div class="roles">
        <div class="role-card">
            <div class="role-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="role-title">For Employees</div>
            <div class="role-desc">Focus on what matters. See your tasks, update their status, and never miss a deadline.</div>
            <ul class="role-list">
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    View all tasks assigned to you
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Update task status and progress
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Get notified about new assignments
                </li>
            </ul>
            <a href="login-user.php" class="role-cta">
                Sign in as employee
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>

        <div class="role-card is-admin">
            <div class="role-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div class="role-title">For Department Admins</div>
            <div class="role-desc">Run your team from one place. Assign work, manage users, and keep the whole department moving.</div>
            <ul class="role-list">
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Create and assign tasks to anyone
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Manage users and departments
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Full visibility across every task
                </li>
            </ul>
            <a href="login-admin.php" class="role-cta">
                Sign in as admin
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- ============ CTA BAND ============ -->
<div class="cta-band">
    <div class="cta-inner">
        <h2>Ready to get organized?</h2>
        <p>Join your team on Task Master and start assigning work in minutes.</p>
        <div class="cta-actions">
            <a href="register.php" class="btn btn-white">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Create an account
            </a>
            <a href="login-admin.php" class="btn btn-outline-white">
                Sign in
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- ============ FOOTER ============ -->
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="homepage.php" class="brand">
                <span class="brand-logo">TM</span>
                Task Master
            </a>
            <p>Assign tasks, track progress, and keep your team moving — all in one place.</p>
        </div>

        <div class="footer-col">
            <h4>Product</h4>
            <ul>
                <li><a href="register.php">Create account</a></li>
                <li><a href="login-user.php">User login</a></li>
                <li><a href="login-admin.php">Admin login</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Access</h4>
            <ul>
                <li><a href="login.php">Admin panel</a></li>
                <li><a href="login-user.php">Employee portal</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> Task Master · Kemgang Wilfried</span>
        <span>Built with care.</span>
    </div>
</footer>

<script>
    /* ---------- Mobile nav toggle ---------- */
    (function () {
        const toggle = document.getElementById('mobileToggle');
        const nav    = document.getElementById('navLinks');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function () {
            const open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        // Close on link click (mobile)
        nav.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => {
                if (window.innerWidth <= 720) {
                    nav.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    })();
</script>
</body>
</html>