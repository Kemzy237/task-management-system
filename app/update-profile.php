<?php
session_name("employee");
session_start();

if (!isset($_SESSION['role']) || !isset($_SESSION['id']) || $_SESSION['role'] !== 'employee') {
    $em = "First login";
    header("Location: ../login.php?error=" . urlencode($em));
    exit();
}

if (!isset($_POST['full_name'], $_POST['password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $em = "Unknown error occurred";
    header("Location: ../edit_profile.php?error=" . urlencode($em));
    exit();
}

include "../DB_connection.php";
include "model/user.php";

function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// --- Collect inputs ---
// Password fields are intentionally NOT htmlspecialchars'd — they must be verified/hashed raw.
$full_name        = validate_input($_POST['full_name']);
$password         = (string)$_POST['password'];
$new_password     = (string)$_POST['new_password'];
$confirm_password = (string)$_POST['confirm_password'];
$id               = (int)$_SESSION['id'];

$wantsPasswordChange = ($new_password !== '' || $confirm_password !== '' || $password !== '');

// --- Validation ---
if (empty($full_name)) {
    $em = "Full name is required";
    header("Location: ../edit_profile.php?error=" . urlencode($em));
    exit();
}

if ($wantsPasswordChange) {
    if ($password === '' || $new_password === '' || $confirm_password === '') {
        $em = "To change your password, fill in all three password fields";
        header("Location: ../edit_profile.php?error=" . urlencode($em));
        exit();
    }
    if (strlen($new_password) < 6) {
        $em = "New password must be at least 6 characters";
        header("Location: ../edit_profile.php?error=" . urlencode($em));
        exit();
    }
    if ($confirm_password !== $new_password) {
        $em = "New password and confirm password do not match";
        header("Location: ../edit_profile.php?error=" . urlencode($em));
        exit();
    }
}

// --- Apply ---
$user = get_user_by_id($conn, $id);
if ($user == 0) {
    $em = "Unknown error occurred";
    header("Location: ../edit_profile.php?error=" . urlencode($em));
    exit();
}

if ($wantsPasswordChange) {
    if (!password_verify($password, $user['password'])) {
        $em = "Incorrect current password";
        header("Location: ../edit_profile.php?error=" . urlencode($em));
        exit();
    }

    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $data   = array($full_name, $hashed, $id);
    update_profile($conn, $data);

    $em = "Profile and password updated successfully";
} else {
    // Name-only update: keep the existing password hash untouched.
    // update_profile() requires a password value in its current signature,
    // so pass the user's existing hash back in.
    $data = array($full_name, $user['password'], $id);
    update_profile($conn, $data);

    $em = "Profile updated successfully";
}

header("Location: ../edit_profile.php?success=" . urlencode($em));
exit();