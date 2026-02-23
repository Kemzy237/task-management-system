<?php
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/user.php";
    if(!isset($_GET['id'])){
        header("Location: user-department.php");
        exit();
    }
    $id = $_GET['id'];
    $user = get_user_by_id($conn, $id);
    if($user == 0){
        header("Location: user-department.php");
        exit();
    }
    $data = array($id, "employee");
    delete_user($conn, $data);
    $sm = "Deleted Successfully";
    header("Location: user-department.php?success=$sm");
	exit();
    
} else{
	$em = "First login";
	header("Location: login-admin.php?error=$em");
	exit();
}
?>