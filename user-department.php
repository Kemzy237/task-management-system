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

$departmentId = (int)($_SESSION['department'] ?? 0);

/* ---------- Read filter inputs ---------- */
$search = trim($_GET['q'] ?? '');

/* ---------- Fetch department employees ---------- */
$data = ["employee", $departmentId];
$allUsers = get_users_by_department($conn, $data);
if ($allUsers == 0 || empty($allUsers)) $allUsers = [];

/* ---------- Apply search filter ---------- */
$users = $allUsers;
if ($search !== '') {
    $needle = mb_strtolower($search);
    $users = array_values(array_filter($allUsers, function ($u) use ($needle) {
        return str_contains(mb_strtolower($u['full_name']), $needle)
            || str_contains(mb_strtolower($u['username']),  $needle)
            || str_contains(mb_strtolower((string)$u['contact']), $needle);
    }));
}

$totalUsers = count($allUsers);
$hasFilter  = ($search !== '');
$num_displayed = count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users · Task Management</title>

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

        /* ============ SEARCH BAR ============ */
        .filter-bar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 16px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 240px;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .search-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-wrap svg {
            position: absolute;
            left: 12px;
            width: 16px;
            height: 16px;
            color: var(--text-subtle);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--surface);
            color: var(--text);
            font-family: inherit;
            font-size: 13.5px;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .search-input::placeholder { color: var(--text-subtle); }

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        /* ============ RESULT META ============ */
        .result-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .result-count {
            font-size: 13px;
            color: var(--text-muted);
        }
        .result-count strong { color: var(--text); font-weight: 700; }

        .active-filters {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid #c7d2fe;
        }

        .filter-chip a {
            display: grid;
            place-items: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: rgba(79, 70, 229, 0.15);
            color: var(--primary);
            transition: var(--transition);
        }
        .filter-chip a:hover { background: var(--primary); color: #fff; }
        .filter-chip svg { width: 10px; height: 10px; }

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

        .cell-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .user-info { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
        .user-name { font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-handle { font-size: 12px; color: var(--text-subtle); }

        .cell-muted { color: var(--text-muted); }
        .cell-muted.mono { font-variant-numeric: tabular-nums; }

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
        .action-detail:hover { background: var(--info-soft);    color: var(--info);    border-color: var(--info); }
        .action-edit:hover   { background: var(--primary-soft); color: var(--primary); border-color: var(--primary); }
        .action-delete:hover { background: var(--danger-soft);  color: var(--danger);  border-color: var(--danger); }

        /* ============ EMPTY STATE ============ */
        .empty-state { padding: 70px 20px; text-align: center; }
        .empty-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: var(--surface-alt);
            display: grid;
            place-items: center;
            margin: 0 auto 14px;
            color: var(--text-subtle);
        }
        .empty-icon svg { width: 24px; height: 24px; }
        .empty-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
        .empty-sub { font-size: 13px; color: var(--text-muted); margin-bottom: 18px; }

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
            .filter-group { min-width: 100%; }
            .data-table thead th,
            .data-table tbody td { padding: 12px 14px; }
            .hide-sm { display: none; }
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
                <h1 class="page-title">Manage Users</h1>
                <p class="page-subtitle">
                    Everyone in your department — add, edit, or remove team members.
                </p>
            </div>
            <div class="page-actions">
                <a href="add-user-department.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                    Add User
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

        <!-- ============ SEARCH BAR ============ -->
        <form class="filter-bar" method="GET" action="user-department.php">
            <div class="filter-group">
                <label class="filter-label" for="q">Search</label>
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input
                        type="text"
                        id="q"
                        name="q"
                        class="search-input"
                        placeholder="Search by name, username, or contact"
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Search
                </button>
                <?php if ($hasFilter): ?>
                    <a href="user-department.php" class="btn btn-ghost">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- ============ RESULT META ============ -->
        <?php if ($hasFilter): ?>
            <div class="result-meta">
                <div class="result-count">
                    Showing <strong><?= (int)$num_displayed ?></strong>
                    of <strong><?= (int)$totalUsers ?></strong>
                    <?= $totalUsers === 1 ? 'user' : 'users' ?>
                </div>
                <div class="active-filters">
                    <span class="filter-chip">
                        Search: "<?= htmlspecialchars($search) ?>"
                        <a href="user-department.php" aria-label="Clear search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </a>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============ USERS TABLE ============ -->
        <?php if (!empty($users)): ?>
            <div class="table-card">
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>User</th>
                                <th class="hide-sm">Contact</th>
                                <th class="hide-sm">Joined</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0; foreach ($users as $u): $i++;
                            $initial = strtoupper(substr($u['full_name'], 0, 1));
                            $joined  = !empty($u['created_at']) ? date('M j, Y', strtotime($u['created_at'])) : '—';
                        ?>
                            <tr>
                                <td class="cell-id"><?= $i ?></td>
                                <td>
                                    <div class="cell-user">
                                        <div class="user-avatar"><?= htmlspecialchars($initial) ?></div>
                                        <div class="user-info">
                                            <span class="user-name"><?= htmlspecialchars($u['full_name']) ?></span>
                                            <span class="user-handle">@<?= htmlspecialchars($u['username']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="cell-muted mono hide-sm"><?= htmlspecialchars($u['contact']) ?></td>
                                <td class="cell-muted hide-sm"><?= htmlspecialchars($joined) ?></td>
                                <td>
                                    <div class="actions" style="justify-content:flex-end;">
                                        <a href="task-details-department.php?id=<?= (int)$u['id'] ?>" class="action-btn action-detail" title="View details">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="11" cy="11" r="8"/>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                            </svg>
                                            Details
                                        </a>
                                        <a href="edit-user-department.php?id=<?= (int)$u['id'] ?>" class="action-btn action-edit" title="Edit user">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="delete-user-department.php?id=<?= (int)$u['id'] ?>" class="action-btn action-delete" title="Delete user" onclick="return confirm('Delete <?= htmlspecialchars(addslashes($u['full_name'])) ?>? This cannot be undone.');">
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
        <?php elseif ($hasFilter): ?>
            <div class="table-card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <div class="empty-title">No users match your search</div>
                    <div class="empty-sub">Try a different name, username, or contact.</div>
                    <a href="user-department.php" class="btn btn-ghost">Clear search</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="empty-title">No employees in your department yet</div>
                    <div class="empty-sub">Add your first team member to start assigning tasks.</div>
                    <a href="add-user-department.php" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Add User
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="page-footer">
            &copy; <?= date('Y') ?> Task Management System · All rights reserved
        </div>
    </main>
</div>
</body>
</html>