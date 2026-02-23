<?php
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/user.php";
    include "app/model/Task.php";
    include "app/model/Department.php";
    if(!isset($_GET['id'])){
        header("Location: user-department.php");
        exit();
    }
    $departments = get_all_departments($conn);
    $id = $_GET['id'];
    $user = get_user_by_id($conn, $id);
    if($user == 0){
        header("Location: user-department.php");
        exit();
    }
    $num = count_my_task($conn, $user['id']);
    $overdue = count_overdue_task($conn, $user['id']);
    $noDeadline = count_my_tasks_noDeadline($conn, $user['id']);
    $pending = count_my_tasks_pending($conn, $user['id']);
    $inProgress = count_my_tasks_in_progress($conn, $user['id']);
    $completed = count_my_completed($conn, $user['id']);

    $tasks = get_all_tasks_by_id($conn, $user['id']);
    $num_task = count_my_task($conn, $user['id']);

?>
<!DOCTYPE html>
<html>
<head>
	<title>User Details</title>
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
    <script type="text/javascript" src="loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {

        var data = google.visualization.arrayToDataTable([
          ['Status', 'Number of tasks'],
          ['Pending(<?=$pending?>)',     <?=$pending?>],
          ['Completed(<?=$completed?>)',      <?=$completed?>],
          ['Overdue(<?=$overdue?>)',  <?=$overdue?>],
          ['No Deadline(<?=$noDeadline?>)', <?=$noDeadline?>],
          ['In Progress(<?=$inProgress?>)',    <?=$inProgress?>]
        ]);

        var options = {
          title: 'Tasks Statistics',
          legend: {textStyle: {color: 'black'}},
		  titleTextStyle: {color: 'black'}
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

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
			<div class="head">
                <div class="head-item">
                    <h2>Name</h2>
                    <p><?=$user['full_name']?></p>
                </div>
                <div class="head-item">
                    <h2>Username</h2>
                    <p><?=$user['username']?></p>
                </div>
                <div class="head-item">
                    <h2>Contact</h2>
                    <p><?=$user['contact']?></p>
                </div>
                <div class="head-item">
                    <h2>Date Joined</h2>
                    <p><?=$user['created_at']?></p>
                </div>
                <div class="head-item" id="head-item-link">
                    <a href="edit-user-department.php?id=<?=$user['id']?>" class="edit-btn">Edit Info</a>
                    <a href="user-department.php" class="detail-btn">Users</a>
                </div>
            </div><hr>
            <h2 class="title">Task Assigned(<?=$num?>)</h2>
            <br>
            <?php if($num != 0){ ?>
                <div id="piechart" style="width: 1000px; height: 600px;"></div>
                <p id="chart"></p>
            <?php }else{ ?>
                <h3 class="title">Nothing to show here</h3>
            <?php } ?>
            <hr>
            <h2 class="title">Tasks</h2>
            <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
            <?php } ?>
            <?php if ($tasks != 0) { ?>
            <table class="main-table table table-striped table-dark table-hover">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
                <?php $i=0; foreach($tasks as $task){ ?>
                <tr>
                    <td><?=++$i?></td>
                    <td><?=$task['title']?></td>
                    <td><?=$task['description']?></td>
                    <td><?=$task['status']?></td>
                    <td><?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00") echo "No Deadline";
                                    else echo $task['due_date'];?></td>
                    <td>
                        <a href="edit-task-department.php?id=<?=$task['id']?>" class="edit-btn">Edit</a>
                        <a href="delete-task-department.php?id=<?=$task['id']?>" class="delete-btn">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <?php }else{ ?>
                <h3>Empty</h3>
            <?php }?>
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
	header("Location: login-admin.php?error=$em");
	exit();
}
?>