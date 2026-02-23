<?php
session_name("admin");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/user.php";
    include "app/model/Task.php";
    if(!isset($_GET['id'])){
        header("Location: user.php");
        exit();
    }
    $id = $_GET['id'];
    $user = get_user_by_id($conn, $id);
    if($user == 0){
        header("Location: user.php");
        exit();
    }
    $num = count_my_task($conn, $id);
    if($num!=0){
        $sm = "User still has tasks assigned to him/her";
        header("Location: user.php?error=$sm");
        exit();
    }
    $data = array($id, "employee");
    delete_user($conn, $data);
    $sm = "Deleted Successfully";
    header("Location: user.php?success=$sm");
	exit();
    
} else{
	$em = "First login";
	header("Location: login.php?error=$em");
	exit();
}
?>