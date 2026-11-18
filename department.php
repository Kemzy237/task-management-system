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
include "app/model/user.php";
include "app/model/Department.php";

$departments = get_all_departments($conn);

/* Pre-compute member counts per department (best effort — tolerant of missing model helpers) */
$memberCounts = [];
if (!empty($departments)) {
    foreach ($departments as $d) {
        $memberCounts[$d['id']] = 0;
        if (function_exists('get_users_by_department')) {
            $members = get_users_by_department($conn, ['employee', $d['id']]);
            if (!empty($members)) {
                $memberCounts[$d['id']] = count($members);
            }
        }
    }
}

/* Rotating accent colors for card stripes */
$accents = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments · Task Management</title>

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

        .page-actions { display: flex; gap: 10px; flex-wrap: wrap; }

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

        /* ============ DEPARTMENT GRID ============ */
        .dept-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 18px;
        }

        .dept-card {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 14px;
            overflow: hidden;
        }

        .dept-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent, var(--primary));
        }

        .dept-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: transparent;
        }

        .dept-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .dept-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: var(--accent-soft, var(--primary-soft));
            color: var(--accent, var(--primary));
            flex-shrink: 0;
        }

        .dept-icon svg { width: 22px; height: 22px; }

        .dept-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .dept-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: var(--surface-alt);
            border: 1px solid var(--border);
            color: var(--text-muted);
            white-space: nowrap;
        }

        .dept-badge svg { width: 11px; height: 11px; }

        .dept-name {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--text);
            line-height: 1.3;
        }

        .dept-card-footer {
            display: flex;
            gap: 8px;
            margin-top: auto;
            padding-top: 4px;
        }

        .dept-action {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 12px;
            border-radius: 9px;
            font-size: 12.5px;
            font-weight: 600;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-muted);
            transition: var(--transition);
            cursor: pointer;
        }

        .dept-action svg { width: 13px; height: 13px; }

        .dept-action.primary {
            background: var(--accent-soft, var(--primary-soft));
            color: var(--accent, var(--primary));
            border-color: transparent;
        }

        .dept-action.primary:hover {
            background: var(--accent, var(--primary));
            color: #fff;
        }

        .dept-action:hover {
            background: var(--surface-alt);
            color: var(--text);
        }

        /* ============ ADD CARD ============ */
        .add-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-height: 172px;
            padding: 22px;
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            background: transparent;
            color: var(--text-muted);
            transition: var(--transition);
            cursor: pointer;
        }

        .add-card:hover {
            border-color: var(--primary);
            background: var(--primary-soft);
            color: var(--primary);
        }

        .add-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--surface);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            transition: var(--transition);
        }

        .add-card:hover .add-card-icon {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            transform: rotate(90deg);
        }

        .add-card-icon svg { width: 22px; height: 22px; }

        .add-card-label {
            font-size: 13.5px;
            font-weight: 600;
        }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            padding: 80px 20px;
            text-align: center;
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: var(--radius);
        }

        .empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: var(--surface-alt);
            display: grid;
            place-items: center;
            margin: 0 auto 16px;
            color: var(--text-subtle);
        }

        .empty-icon svg { width: 26px; height: 26px; }

        .empty-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 4px;
        }

        .empty-sub {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 20px;
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
        }
    </style>
</head>
<body>
<div class="app-shell">
    <?php include "inc/nav.php"; ?>

    <main class="app-main">

        <!-- ============ HEADER ============ -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Departments</h1>
                <p class="page-subtitle">
                    Organize your teams, assign members, and manage department-level settings.
                </p>
            </div>
            <div class="page-actions">
                <a href="add-department.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Department
                </a>
            </div>
        </div>

        <!-- ============ DEPARTMENT GRID ============ -->
        <?php if (!empty($departments) && $departments != 0): ?>
            <div class="dept-grid">
                <?php foreach ($departments as $idx => $department):
                    $accent     = $accents[$idx % count($accents)];
                    $memberCount = $memberCounts[$department['id']] ?? 0;
                ?>
                    <div class="dept-card" style="--accent: <?= $accent ?>; --accent-soft: <?= $accent ?>1a;">
                        <div class="dept-card-top">
                            <div class="dept-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 21h18"/>
                                    <path d="M5 21V7l8-4v18"/>
                                    <path d="M19 21V11l-6-4"/>
                                    <line x1="9" y1="9" x2="9" y2="9.01"/>
                                    <line x1="9" y1="12" x2="9" y2="12.01"/>
                                    <line x1="9" y1="15" x2="9" y2="15.01"/>
                                    <line x1="9" y1="18" x2="9" y2="18.01"/>
                                </svg>
                            </div>
                            <div class="dept-meta">
                                <span class="dept-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                    <?= (int)$memberCount ?> member<?= $memberCount === 1 ? '' : 's' ?>
                                </span>
                            </div>
                        </div>

                        <div class="dept-name"><?= htmlspecialchars($department['name']) ?></div>

                        <div class="dept-card-footer">
                            <a href="manage-department.php?id=<?= (int)$department['id'] ?>" class="dept-action primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                Manage
                            </a>
                            <a href="edit-department.php?id=<?= (int)$department['id'] ?>" class="dept-action">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Add new department card -->
                <a href="add-department.php" class="add-card">
                    <div class="add-card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </div>
                    <span class="add-card-label">Add Department</span>
                </a>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"/>
                        <path d="M5 21V7l8-4v18"/>
                        <path d="M19 21V11l-6-4"/>
                    </svg>
                </div>
                <div class="empty-title">No departments yet</div>
                <div class="empty-sub">Create your first department to start organizing your teams.</div>
                <a href="add-department.php" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Department
                </a>
            </div>
        <?php endif; ?>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>
</body>
</html>