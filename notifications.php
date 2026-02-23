<?php
session_name("employee");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id'])){
    include "DB_connection.php";
    include "app/model/Notification.php";
    include "app/model/user.php";
    // include "app/model/user.php";
        $notifications = get_all_my_notifications($conn, $_SESSION['id']);
        $num_notification = count_my_notifications($conn, $_SESSION['id']);
    
    if($_SESSION['role']=="admin"){
        $num = 5;
    }else{
        $num = 4;
    }
?>
<!DOCTYPE html>
<html>
<head>
	<title>My Notifications</title>
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
			<h4 class="title">
                All Notifications 
                (<?=$num_notification?>)
            </h4>
            <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
            <?php } ?>
            <?php if ($notifications != 0) { ?>
            <table class="main-table table table-striped table-dark table-hover">
                <tr>
                    <th>#</th>
                    <th>Message</th>
                    <th>Type</th>
                    <th>Date</th>
                </tr>
                <?php $i=0; foreach($notifications as $notification){ ?>
                <tr>
                    <td><?=++$i?></td>
                    <td><?=$notification['message']?></td>
                    <td><?=$notification['type']?></td>
                    <td><?=$notification['date']?></td>
                </tr>
                <?php } ?>
            </table>
            <?php }else{ ?>
                <h3>You have zero notifications</h3>
            <?php }?>
		</section>
	</div>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(<?=$num?>)");
        active.classList.add("active");
    </script>
    <script src="javascript/all.js"></script>
    <script src="javascript/all.min.js"></script>
    <script src="javascript/jquery-3.7.1.js"></script>
    <script src="javascript/jquery-3.7.1.min.js"></script>
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