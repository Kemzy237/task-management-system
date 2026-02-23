<?php
session_name("admin");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id'])){
	include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
    include "app/model/Department.php";

    $departments = get_all_departments($conn);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Departments</title>
	<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
	<link rel="stylesheet" id="pagestyle" href="css/styles.css">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

	<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
	<link rel="manifest" href="favicon/site.webmanifest">
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php"; ?>
	<div class="body">
	<?php include "inc/nav.php"; ?>
		<section class="section-1">
            <div class="dashboard">
                <a href="add-department.php"><div class="dashboard-item">
					<i class="fa fa-plus font"></i><br>
					<span>Add a department</span>
				</div></a>
				<?php if($departments != 0){?>
                <?php foreach($departments as $department){?>
                    <a href="manage-department.php?id=<?=$department['id']?>">
						<div class="dashboard-item">
							<i class="fa fa-gear font"></i><br>
							<span><?=$department['name']?></span>
				    	</div>
					</a>
                <?php }?>
				<?php }else{?>
					<h3 class="title">Empty</h3>
				<?php }?>
            </div>
		</section>
	</div>
	<script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(3)");
        active.classList.add("active");
    </script>
	<script src="javascript/all.js"></script>
    <script src="javascript/all.min.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
<?php } else{
	$em = "First login";
	header("Location: login.php?error=$em");
	exit();
}
?>