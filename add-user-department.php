<?php
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/user.php";
    include "app/model/Department.php";
    $user = get_user_by_id($conn, $_SESSION['id']);
    $departments = get_all_departments($conn);

?>
<!DOCTYPE html>
<html>
<head>
	<title>Add User</title>
	<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
	<link rel="stylesheet" href="css/styles.css">
	<link rel="stylesheet" href="css/all.css">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/all.min.css">
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
	<link rel="manifest" href="favicon/site.webmanifest">
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php"; ?>
	<div class="body">
	<?php include "inc/nav-admin.php"; ?>
		<section class="section-1">
			<h4 class="title">Add Users  <a href="user-department.php">Users</a></h4><br>
            <form class="form-1" method="POST" action="app/add-user-department.php">
                <?php if (isset($_GET['error'])) {?>
                    <div class="danger" role="alert">
                        <?php echo stripcslashes($_GET['error']); ?>
                    </div>
                <?php } ?>
                <?php if (isset($_GET['success'])) {?>
                    <div class="success" role="alert">
                        <?php echo stripcslashes($_GET['success']); ?>
                    </div>
                <?php }?>
                    <div class="form-floating mb-3 input-holder">
                        <input type="text" class="form-control input-1" id="fullname" name="full_name" placeholder="Full Name"><br>
                        <label for="fullname"><i class="fas fa-user"></i>Full Name</label>
                    </div>
                    <div class="form-floating mb-3 input-holder">
                        <input type="text" id="username" class="form-control input-1" name="user_name" placeholder="Username"><br>
                        <label for="username"><i class="fas fa-user"></i>Username</label>
                    </div>
                    <div class="form-floating mb-3 input-holder">
                        <input type="text" id="password" class="form-control input-1" name="password" placeholder="Password"><br>
                        <label for="password"><i class="fas fa-key"></i>Password</label>
                    </div>
                    <div class="form-floating mb-3 input-holder">
                        <input type="text" id="contact" class="form-control input-1" name="contact" placeholder="Contact"><br>
                        <label for="contact"><i class="fas fa-address-book"></i>Contact</label>
                    </div>
                    <input type="text" name="department" value="<?=$_SESSION['department']?>" hidden>
                    <input type="text" name="role" value="employee" hidden>
                    <button class="edit-btn">Add</button>
            </form>
		</section>
	</div>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(2)");
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