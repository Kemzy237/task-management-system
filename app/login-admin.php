<?php
session_name('admin-department');
session_start();

/* ---------- Guard: only accept POST ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login-admin.php");
    exit();
}

/* ---------- Guard: required fields present ---------- */
if (!isset($_POST['user_name'], $_POST['password'], $_POST['department'])) {
    $em = "Unknown error occurred";
    header("Location: ../login-admin.php?error=" . urlencode($em));
    exit();
}

include "../DB_connection.php";

/* ---------- Sanitizers ---------- */
/**
 * Sanitize a value for *display* (error messages, logs).
 * Do NOT use this on passwords — it corrupts them before hashing.
 */
function validate_input(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/* ---------- Read inputs ---------- */
/*  Username: trim + escape is fine (it's used in a prepared query and echoed nowhere). */
$user_name = trim((string)$_POST['user_name']);

/*  Password: leave raw. Only trim *leading/trailing* whitespace if that's your policy. */
/*  (Most apps do NOT trim passwords — we keep it raw to match what the user typed.) */
$password = (string)$_POST['password'];

/*  Department: cast to int, 0 means "not selected". */
$department_id = isset($_POST['department']) ? (int)$_POST['department'] : 0;

/* ---------- Basic validation ---------- */
if ($user_name === '') {
    $em = "User name is required";
    header("Location: ../login-admin.php?error=" . urlencode($em));
    exit();
}

if ($password === '') {
    $em = "Password is required";
    header("Location: ../login-admin.php?error=" . urlencode($em));
    exit();
}

if ($department_id <= 0) {
    $em = "Please select a department";
    header("Location: ../login-admin.php?error=" . urlencode($em));
    exit();
}

/* ---------- Look up the user ---------- */
$sql  = "SELECT id, username, password, role, department FROM users WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_name]);

/*  Unify "user not found" and "wrong password" into one message
    so attackers can't enumerate usernames by comparing error text. */
$invalidCredentials = "Invalid username or password";

if ($stmt->rowCount() !== 1) {
    header("Location: ../login-admin.php?error=" . urlencode($invalidCredentials));
    exit();
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* ---------- Verify password ---------- */
if (!password_verify($password, $user['password'])) {
    header("Location: ../login-admin.php?error=" . urlencode($invalidCredentials));
    exit();
}

/* ---------- Verify role + department ---------- */
if ($user['role'] !== 'admin') {
    $em = "This account is not an admin account.";
    header("Location: ../login-admin.php?error=" . urlencode($em));
    exit();
}

if ((int)$user['department'] !== $department_id) {
    $em = "You don't belong to the selected department.";
    header("Location: ../login-admin.php?error=" . urlencode($em));
    exit();
}

/* ---------- All checks passed — start session ---------- */
session_regenerate_id(true); // prevent session fixation

$_SESSION['role']       = $user['role'];
$_SESSION['id']         = (int)$user['id'];
$_SESSION['username']   = $user['username'];
$_SESSION['department'] = (int)$user['department'];

header("Location: ../index-admin.php");
exit();