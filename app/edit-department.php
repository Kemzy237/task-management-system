<?php 
session_name("admin");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id'])){
if (isset($_POST['name']) && $_SESSION['role'] == 'admin') {
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$name = validate_input($_POST['name']);
	$id = validate_input($_POST['id']);

	if (empty($name)) {
		$em = "Department name is required";
	    header("Location: ../edit-department.php?id=$id&error=$em");
	    exit();
	}else {
        include "model/department.php";
        $data = array($name, $id);
        update_department($conn, $data);

        $em = "Department Updated Succesfully";
	    header("Location: ../edit-department.php?id=$id&success=$em");
	    exit();
	}
}else {
   $em = "Unknown error occurred";
   header("Location: ../edit-department.php?id=$id&error=$em");
   exit();
}
} else{
	$em = "First login";
	header("Location: ../login.php?error=$em");
	exit();
}