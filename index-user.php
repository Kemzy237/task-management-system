<?php
session_name("employee");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id'])){
	include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";

    $num_my_task = count_my_task($conn, $_SESSION['id']);
    $task_overdue = count_overdue_task($conn, $_SESSION['id']);
    $my_completed = count_my_completed($conn, $_SESSION['id']);
    $my_due_today_task = count_my_tasks_due_today($conn, $_SESSION['id']);
    $my_noDeadline_task = count_my_tasks_noDeadline($conn, $_SESSION['id']);
    $my_pending_task = count_my_tasks_pending($conn, $_SESSION['id']);
    $my_in_progress_task = count_my_tasks_in_progress($conn, $_SESSION['id']);
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Dashboard</title>
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
	<?php include "inc/header-user.php"; ?>
	<div class="body">
	<?php include "inc/nav-user.php"; ?>
		<section class="section-1">
				<div class="dashboard">
					<a href="my-task.php"><div class="dashboard-item">
						<i class="fa fa-tasks font"></i><br>
						<span>My Tasks</span><br>
						<span>(<?=$num_my_task?>)</span>
					</div></a>
					<a href="my-task.php?due_date=Overdue"><div class="dashboard-item">
						<i class="fa fa-window-close font"></i><br>
						<span>Overdue Tasks</span><br>
						<span>(<?=$task_overdue?>)</span>
					</div></a>
					<a href="my-task.php?due_date=Completed"><div class="dashboard-item">
						<i class="fa fa-check-square font"></i><br>
						<span>Completed Tasks</span><br>
						<span>(<?=$my_completed?>)</span>
					</div></a>
					<a href="my-task.php?due_date=Due Today"><div class="dashboard-item">
						<i class="fa fa-calendar font"></i><br>
						<span>Tasks Due Today</span><br>
						<span>(<?=$my_due_today_task?>)</span>
					</div></a>
					<a href="my-task.php?due_date=Pending"><div class="dashboard-item">
						<i class="fa fa-square font"></i><br>
						<span>Pending Tasks</span><br>
						<span>(<?=$my_pending_task?>)</span>
					</div></a>
					<a href="my-task.php?due_date=In Progress"><div class="dashboard-item">
						<i class="fa fa-spinner font"></i><br>
						<span>Tasks In Progress</span><br>
						<span>(<?=$my_in_progress_task?>)</span>
					</div></a>
					<a href="my-task.php?due_date=No Deadline"><div class="dashboard-item">
						<i class="fa fa-clock font"></i><br>
						<span>No Deadline</span><br>
						<span>(<?=$my_noDeadline_task?>)</span>
					</div></a>
				</div>
		</section>
	</div>
	<script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(1)");
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