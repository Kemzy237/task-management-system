<?php
function insert_department($conn, $data){
    $sql = "INSERT INTO department (name) VALUES(?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_all_departments($conn){
    $sql = "SELECT * FROM department";
    $stmt = $conn->prepare($sql);
    $stmt->execute([]);

    if($stmt->rowCount() > 0){
        $departments = $stmt->fetchAll();
    }else $departments = 0;

    return $departments;
}

function get_department_by_id($conn, $id){
    $sql = "SELECT * FROM department WHERE id =?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    if($stmt->rowCount() > 0){
        $department = $stmt->fetch();
    }else $department = 0;

    return $department;
}

function delete_department($conn, $data){
    $sql = "DELETE FROM department WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function update_department($conn, $data){
    $sql = "UPDATE department SET name=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_department_name($conn, $id){
    $sql = "SELECT name FROM department WHERE id =?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    if($stmt->rowCount() > 0){
        $department = $stmt->fetch();
    }else $department = 0;

    return $department;
}
