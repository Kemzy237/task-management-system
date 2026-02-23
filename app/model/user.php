<?php
function get_all_users($conn){
    $sql = "SELECT * FROM users WHERE role =?";
    $stmt = $conn->prepare($sql);
    $stmt->execute(["employee"]);

    if($stmt->rowCount() > 0){
        $users = $stmt->fetchAll();
    }else $users = 0;

    return $users;
}

function get_all_admins($conn){
    $sql = "SELECT * FROM users WHERE role =? AND department != ''";
    $stmt = $conn->prepare($sql);
    $stmt->execute(["admin"]);

    if($stmt->rowCount() > 0){
        $users = $stmt->fetchAll();
    }else $users = 0;

    return $users;
}

function insert_user($conn, $data){
    $sql = "INSERT INTO users (full_name, username, password, role, contact, department) VALUES(?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function update_user($conn, $data){
    $sql = "UPDATE users SET full_name=?, username=?, password=?, contact=?, department=? WHERE id=? AND role=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function delete_user($conn, $data){
    $sql = "DELETE FROM users WHERE id=? AND role=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_user_by_id($conn, $id){
    $sql = "SELECT * FROM users WHERE id =?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    if($stmt->rowCount() > 0){
        $user = $stmt->fetch();
    }else $user = 0;

    return $user;
}

function update_profile($conn, $data){
    $sql = "UPDATE users SET full_name=?, password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function count_users($conn){
    $sql = "SELECT id FROM users WHERE role='employee'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);
    return $stmt->rowCount();
}

function getFullName($conn, $id){
    $sql = "SELECT full_name FROM users WHERE id =?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    if($stmt->rowCount() > 0){
        $user = $stmt->fetch();
    }else $user = 0;

    return $user;
}

function get_users_by_department($conn, $data){
    $sql = "SELECT * FROM users WHERE role=? AND department=? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    if($stmt->rowCount() > 0){
        $users = $stmt->fetchAll();
    }else $users = 0;
    return $users;
}
