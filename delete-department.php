<?php
session_name("admin");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/Department.php";
    include "app/model/user.php";
    if(!isset($_GET['id'])){
        header("Location: department.php");
        exit();
    }
    $id = $_GET['id'];
    $department = get_department_by_id($conn, $id);
    if($department == 0){
        header("Location: department.php");
        exit();
    }
    $users = get_all_users($conn);
    $i=0;
    foreach($users as $user){
        if($user['department'] == $id){
            ++$i;
        }
    }
    if($i!=0){
        $sm = "This deparment still contains users";
        header("Location: manage-department.php?id=$id&error=$sm");
        exit();
    }
    $data = array($id);
    delete_department($conn, $data);
    header("Location: department.php");
	exit();
    
} else{
	$em = "First login";
	header("Location: login-admin.php?error=$em");
	exit();
}
?>