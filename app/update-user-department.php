<?php 
session_name("admin-department");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    $em = "First login";
    header("Location: ../edit-user-department.php?error=" . urlencode($em));
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    $em = "You don't have permission to perform this action.";
    header("Location: ../user-department.php?error=" . urlencode($em));
    exit();
}

if (!isset($_POST['user_name'], $_POST['full_name'], $_POST['contact'], $_POST['id'])) {
    $em = "Unknown error occurred";
    header("Location: ../edit-user-department.php?error=" . urlencode($em));
    exit();
}

include "../DB_connection.php";

function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// --- Collect & validate inputs ---
$id        = (int) $_POST['id'];
$user_name = validate_input($_POST['user_name']);
$full_name = validate_input($_POST['full_name']);
$contact   = validate_input($_POST['contact']);
$password  = $_POST['password'] ?? '';   // do NOT htmlspecialchars a password

// The department is always the admin's own department (frontend never sends it).
$department = (int) ($_SESSION['department'] ?? 0);

// --- Validation ---
if (empty($user_name)) {
    $em = "User name is required";
    header("Location: ../edit-user-department.php?error=" . urlencode($em) . "&id=$id");
    exit();
} elseif (empty($full_name)) {
    $em = "Full name is required";
    header("Location: ../edit-user-department.php?error=" . urlencode($em) . "&id=$id");
    exit();
} elseif (empty($contact)) {
    $em = "Contact is required";
    header("Location: ../edit-user-department.php?error=" . urlencode($em) . "&id=$id");
    exit();
} elseif (!preg_match('/^\d{1,10}$/', $contact)) {
    $em = "Contact must be 1–10 digits";
    header("Location: ../edit-user-department.php?error=" . urlencode($em) . "&id=$id");
    exit();
} elseif ($password !== '' && strlen($password) < 6) {
    $em = "Password must be at least 6 characters";
    header("Location: ../edit-user-department.php?error=" . urlencode($em) . "&id=$id");
    exit();
} else {
    include "model/user.php";

    // --- Ownership / role guard (mirror of the frontend check) ---
    $target = get_user_by_id($conn, $id);

    if ($target == 0
        || $target['role'] !== 'employee'
        || (int)$target['department'] !== $department
    ) {
        $em = "You don't have permission to edit this user.";
        header("Location: ../user-department.php?error=" . urlencode($em));
        exit();
    }

    // --- Decide whether to update the password ---
    if ($password === '') {
        // Keep the existing password hash
        $sql = "UPDATE users 
                SET full_name=?, username=?, contact=?, department=? 
                WHERE id=? AND role=?";
        $data = array($full_name, $user_name, $contact, $department, $id, "employee");
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users 
                SET full_name=?, username=?, password=?, contact=?, department=? 
                WHERE id=? AND role=?";
        $data = array($full_name, $user_name, $hashed, $contact, $department, $id, "employee");
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($data);

    $em = "User Updated Successfully";
    header("Location: ../edit-user-department.php?success=" . urlencode($em) . "&id=$id");
    exit();
}