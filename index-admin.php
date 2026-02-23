<?php
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id'])){
	include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
    include "app/model/Department.php";
	$role="employee";
    $data = array($role, $_SESSION['department']);
    $users = get_users_by_department($conn, $data);
	$tasks = get_all_tasks($conn);
	$due_todays = get_all_tasks_due_today($conn);
	$currentdate = date('Y-m-d');
	$num_users=0;
	foreach($users as $user){
		if($user['department'] == $_SESSION['department']){
			$num_users++;
		}
	}
	$num_task=0;
	$completed=0;
	$in_progress_task=0;
	$due_today_task=0;
	$overdue_task=0;
	$pending_task=0;
	foreach($tasks as $task){
		foreach($users as $user){
			if($task['assigned_to'] == $user['id']){
				$num_task++;
				if($task['status'] == "completed"){
					$completed++;
				}
				if($task['status'] == "in_progress" && $task['due_date'] >= $currentdate || ($task['due_date'] == "" || $task['due_date'] == "0000-00-00")){
					$in_progress_task++;
				}
				if($task['status'] != "completed" && $task['due_date'] == $currentdate){
					$due_today_task++;
				}
				if($task['due_date'] < $currentdate && ($task['due_date'] == "" || $task['due_date'] != "0000-00-00") && $task['status'] != "completed"){
					$overdue_task++;
				}
				if($task['status'] == "pending" && $task['due_date'] >= $currentdate || ($task['due_date'] == "" || $task['due_date'] == "0000-00-00") && $task['status'] != "in_progress"){
					$pending_task++;
				}
			}
		}
	}
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
	<script type="text/javascript" src="loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Status', 'Number of tasks'],
          ['Pending',     <?=$pending_task?>],
          ['Completed',      <?=$completed?>],
          ['In Progress',  <?=$in_progress_task?>],
          ['Overdue',    <?=$overdue_task?>]
        ]);

        var options = {
          title: 'Tasks Overview',
          is3D: true,
		  legend: {textStyle: {color: 'black'}},
		  titleTextStyle: {color: 'black'}
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
</head>
<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php"; ?>
	<div class="body">
	<?php include "inc/nav-admin.php"; ?>
		<section class="section-1">
				<div class="dashboard">
					<a href="user-department.php"><div class="dashboard-item">
						<i class="fa fa-users font"></i><br>
						<span>Employee(s)</span><br>
						<span>(<?=$num_users?>)</span>
					</div></a>
					<a href="task-department.php"><div class="dashboard-item">
						<i class="fa fa-tasks font"></i><br>
						<span>All Tasks</span><br>
						<span>(<?=$num_task?>)</span>
					</div></a>
					<a href="task-department.php?due_date=Completed"><div class="dashboard-item">
						<i class="fa fa-check-square font"></i><br>
						<span>Completed Tasks</span><br>
						<span>(<?=$completed?>)</span>
					</div></a>
					<a href="task-department.php?due_date=Due Today"><div class="dashboard-item">
						<i class="fa fa-exclamation-triangle font"></i><br>
						<span>Tasks Due Today</span><br>
						<span>(<?=$due_today_task?>)</span>
					</div></a>
					<a href="task-department.php?due_date=Pending"><div class="dashboard-item">
						<i class="fa fa-square font"></i><br>
						<span>Pending Tasks</span><br>
						<span>(<?=$pending_task?>)</span>
					</div></a>
				</div>
				<div class="graphs">
					<div id="piechart_3d" style="width: 900px; height: 500px;"></div>
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
	header("Location: login-admin.php?error=$em");
	exit();
}
?>