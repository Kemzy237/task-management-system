<?php
session_name("admin");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
    $currentdate = date('Y-m-d');
    $text = "All Task";
    if(isset($_GET['due_date']) && $_GET['due_date'] == "Due Today"){
        $text = "Due Today";
        $tasks = get_all_tasks_due_today($conn);
        $num_task = count_tasks_due_today($conn);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Overdue"){
        $text = "Overdue";
        $tasks = get_all_tasks_overdue($conn);
        $num_task = count_tasks_overdue($conn);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "No Deadline"){
        $text = "No Deadline";
        $tasks = get_all_tasks_noDeadline($conn);
        $num_task = count_tasks_noDeadline($conn);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Due In 7 Days"){
        $text = "Due In 7 Days";
        $tasks = get_all_tasks_due_in_7_days($conn);
        $num_task = count_tasks_due_in_7_days($conn);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Due In 30 Days"){
        $text = "Due In 30 Days";
        $tasks = get_all_tasks_due_in_30_days($conn);
        $num_task = count_tasks_due_in_30_days($conn);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Pending"){
        $text = "Pending";
        $tasks = get_all_tasks_pending($conn);
        $num_task = count_tasks_pending($conn);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "In Progress"){
        $text = "In Progress";
        $tasks = get_all_tasks_in_progress($conn);
        $num_task = count_tasks_in_progress($conn);
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Completed"){
        $text = "Completed";
        $tasks = get_all_tasks_completed($conn);
        $num_task = count_completed($conn);
    }else{
        $tasks = get_all_tasks($conn);
        $num_task = count_tasks($conn);
    }

    $users = get_all_users($conn);
    
?>
<!DOCTYPE html>
<html>
<head>
	<title>All Task</title>
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
	<?php include "inc/nav.php"; ?>
		<section class="section-1">
			<h4 class="title-2">  
                <a href="create_task.php" class="btn">Create Task</a>
                <a href="task.php" class="btn">All Task</a>
                <a href="task.php?due_date=Due Today" class="btn">Due Today</a>
                <a href="task.php?due_date=Overdue" class="btn">Overdue</a>
                <a href="task.php?due_date=Completed" class="btn">Completed</a>
                <p class="d-inline-flex gap-1">
                    <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" id="more" role="button" aria-expanded="false" aria-controls="collapseExample">...</a>
                </p>
                <div class="collapse" id="collapseExample">
                    <div class="card card-body">
                        <div class="sorts">
                            <a href="task.php?due_date=Due In 7 Days"class="btn">Due In 7 Days</a>
                            <a href="task.php?due_date=Due In 30 Days"class="btn">Due In 30 Days</a>
                            <a href="task.php?due_date=Pending"class="btn">Pending</a>
                            <a href="task.php?due_date=In Progress"class="btn">In Progress</a>
                            <a href="task.php?due_date=No Deadline"class="btn">No Deadline</a>
                        </div>
                    </div>
                </div>
            </h4>
            <h4 class="title-2"><?=$text?> (<?=$num_task?>)</h4>
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
                    <th>Assigned To</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php $i=0; foreach($tasks as $task){ ?>
                <tr>
                    <td><?=++$i?></td>
                    <td><?=$task['title']?></td>
                    <td><?=$task['description']?></td>
                    <td>
                        <?php 
                        foreach($users as $user){
                        if($user['id']==$task['assigned_to']){
                            echo $user['full_name'];
                        } }?>
                    </td>
                    <td><?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00") echo "No Deadline";
                                    else echo $task['due_date'];?></td>
                    <td><?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00") echo $task['status'];
                              else if($task['due_date'] < $currentdate && $task['status'] != "completed") echo "Overdue";
                              else if($task['status'] == "pending") echo "Pending";
                              else if($task['status'] == "completed") echo "Completed";
                              else if($task['status'] == "in_progress") echo "In Progress";
                    else echo $task['status'];?></td>
                    <td>
                        <a href="edit-task.php?id=<?=$task['id']?>" class="edit-btn">Edit</a>
                        <a href="delete-task.php?id=<?=$task['id']?>" class="delete-btn">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <?php }else{ ?>
                <h3 class="title">Empty</h3>
            <?php }?>
		</section>
	</div>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(5)");
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