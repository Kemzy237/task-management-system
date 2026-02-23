<?php
session_name("employee");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id'])){
    include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
     $text = "My Tasks";
    if(isset($_GET['due_date']) && $_GET['due_date'] == "Due Today"){
        $text = "Due Today";
        $tasks = get_all_my_tasks_due_today($conn, $_SESSION['id']);
        $num_task = count_my_tasks_due_today($conn, $_SESSION['id']);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Overdue"){
        $text = "Overdue";
        $tasks = get_all_my_tasks_overdue($conn, $_SESSION['id']);
        $num_task = count_overdue_task($conn, $_SESSION['id']);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "No Deadline"){
        $text = "No Deadline";
        $tasks = get_all_my_tasks_noDeadline($conn, $_SESSION['id']);
        $num_task = count_my_tasks_noDeadline($conn, $_SESSION['id']);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Pending"){
        $text = "Pending";
        $tasks = get_all_my_tasks_pending($conn, $_SESSION['id']);
        $num_task = count_my_tasks_pending($conn, $_SESSION['id']);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "In Progress"){
        $text = "In Progress";
        $tasks = get_all_my_tasks_in_progress($conn, $_SESSION['id']);
        $num_task = count_my_tasks_in_progress($conn, $_SESSION['id']);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Completed"){
        $text = "Completed";
        $tasks = get_all_my_tasks_completed($conn, $_SESSION['id']);
        $num_task = count_my_completed($conn, $_SESSION['id']);
    }else{
        $tasks = get_all_tasks_by_id($conn, $_SESSION['id']);
        $num_task = count_my_task($conn, $_SESSION['id']);
    }
    
    
?>
<!DOCTYPE html>
<html>
<head>
	<title>My Tasks</title>
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
                <?=$text?> 
                (<?=$num_task?>)
                <a href="my-task.php" class="btn">My Tasks</a>
                <a href="my-task.php?due_date=Due Today" class="btn">Due Today</a>
                <a href="my-task.php?due_date=Overdue" class="btn">Overdue</a>
                <a href="my-task.php?due_date=Completed" class="btn">Completed</a>
                <a href="my-task.php?due_date=Pending" class="btn">Pending</a>
                <a href="my-task.php?due_date=In Progress" class="btn">In Progress</a>
                <a href="my-task.php?due_date=No Deadline" class="btn">No Deadline</a>
            </h4>
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
                    <?php if($task['status'] == "completed"){
                        echo "Completed";
                    }else{?>
                        <a href="edit-task-employee.php?id=<?=$task['id']?>" class="edit-btn">Edit</a>
                    <?php } ?>
                        
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
	header("Location: login.php?error=$em");
	exit();
}
?>