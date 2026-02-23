<?php
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
    if(!isset($_GET['id'])){
        header("Location: task-department.php");
        exit();
    }
    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);
    if($task == 0){
        header("Location: task-department.php");
        exit();
    }
    
    $data = array($id);
    delete_task($conn, $data);
    $sm = "Deleted Successfully";
    header("Location: task-department.php?success=$sm");
	exit();
    
} else{
	$em = "First login";
	header("Location: login-admin.php?error=$em");
	exit();
}
?>