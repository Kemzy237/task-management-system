<?php 
session_name("employee");
session_start();
	include "../DB_connection.php";

    function validate_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	$user_name = validate_input($_POST['user_name']);
	$password = validate_input($_POST['password']);
    $full_name = validate_input($_POST['full_name']);
    $contact = validate_input($_POST['contact']);
    $department = validate_input($_POST['department']);

	if (empty($user_name)) {
		$em = "User name is required";
	    header("Location: ../register.php?error=$em");
	    exit();
	}else if (empty($password)) {
		$em = "Password is required";
	    header("Location: ../register.php?error=$em");
	    exit();
	}else if (empty($full_name)) {
		$em = "Full name is required";
	    header("Location: ../register.php?error=$em");
	    exit();
	}else if(empty($contact)){
		$em = "Contact is required";
		header("Location: ../register.php?error=$em");
		exit();
	}else if(empty($department)){
		$em = "Department is required";
		header("Location: ../register.php?error=$em");
		exit();
	}else {
        include "model/user.php";
        $password = password_hash($password, PASSWORD_DEFAULT);
        $data = array($full_name, $user_name, $password, "employee", $contact, $department);
        insert_user($conn, $data);

		$sql = "SELECT * FROM users WHERE username = ?";
       	$stmt = $conn->prepare($sql);
       	$stmt->execute([$user_name]);
		$user=$stmt->fetch();
		$_SESSION['role'] = $user['role'];
		$_SESSION['id'] = $user['id'];
		$_SESSION['username'] = $user['username'];
		header("Location: ../index-user.php");


        // $em = "Registered Successfully. Please Login To Continue";
	    // header("Location: ../login.php?success=$em");
	    // exit();
	}