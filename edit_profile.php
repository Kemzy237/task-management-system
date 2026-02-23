<?php
session_name("employee");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee"){
    include "DB_connection.php";
    include "app/model/user.php";
    $user = get_user_by_id($conn, $_SESSION['id']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Profile</title>
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
	<?php include "inc/header-user.php"; ?>
	<div class="body">
	<?php include "inc/nav-user.php"; ?>
		<section class="section-1">
			<h4 class="title">Edit Profile     <a href="profile.php">Profile</a></h4>
            <form class="form-1" method="POST" action="app/update-profile.php">
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
                    <input type="text" id="fullname" class="form-control input-1" name="full_name" placeholder="Full Name" value="<?=$user['full_name']?>"><br>
                    <label for="fullname"><i class="fas fa-user"></i>Full Name</label>
                </div>
                <div class="form-floating mb-3 input-holder">
                    <input type="text" id="password" class="form-control input-1" name="password" placeholder="Password" value="**********"><br>
                    <label for="password"><i class="fas fa-key"></i>Old Password</label>
                </div>
                <div class="form-floating mb-3 input-holder">
                    <input type="text" id="new_password" class="form-control input-1" name="new_password" placeholder="New Password"><br>
                    <label for="new_password"><i class="fas fa-key"></i>New Password</label>
                </div>
                <div class="form-floating mb-3 input-holder">
                    <input type="text" id="confirm_password" class="form-control input-1" name="confirm_password" placeholder="Confirm Password"><br>
                    <label for="confirm_password"><i class="fas fa-key"></i>Confirm Password</label>
                </div>
                <button class="edit-btn">Change</button>
            </form>
            
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