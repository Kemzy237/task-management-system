<?php 
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id'])){
if (isset($_POST['id']) && isset($_POST['title']) && isset($_POST['status']) && isset($_POST['description']) && isset($_POST['assigned_to']) && $_SESSION['role'] == 'admin' && isset($_POST['due_date'])) {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$title = validate_input($_POST['title']);
	$description = validate_input($_POST['description']);
    $assigned_to = validate_input($_POST['assigned_to']);
    $id = validate_input($_POST['id']);
    $due_date = validate_input($_POST['due_date']);
	$status = validate_input($_POST['status']);

	if (empty($title)) {
		$em = "Title is required";
	    header("Location: ../edit-task-department.php?error=$em&id=$id");
	    exit();
	}else if (empty($description)) {
		$em = "Description is required";
	    header("Location: ../edit-task-department.php?error=$em&id=$id");
	    exit();
	}else if ($assigned_to== 0) {
		$em = "Select User";
	    header("Location: ../edit-task-department.php?error=$em&id=$id");
	    exit();
	}else if (empty($status)) {
		$em = "Status is required";
	    header("Location: ../edit-task-department.php?error=$em&id=$id");
	    exit();
	}else{
        include "model/Task.php";
        $data = array($title, $description, $assigned_to, $due_date, $status, $id);
        update_task($conn, $data);

        $em = "Task Updated Succesfully";
	    header("Location: ../edit-task-department.php?success=$em&id=$id");
	    exit();
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../edit-task-department.php?error=$em");
   exit();
}
} else{
	$em = "First login";
	header("Location: ../login-admin.php?error=$em");
	exit();
}