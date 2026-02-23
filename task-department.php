<?php
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
    $currentdate = date('Y-m-d');
    $text = "All Task";
    
	$role="employee";
    $data = array($role, $_SESSION['department']);
    $users = get_users_by_department($conn, $data);
	$tasks = get_all_tasks($conn);

    $num_tasks=0;
	$completed=0;
	$in_progress_task=0;
	$due_today_task=0;
	$overdue_task=0;
	$pending_task=0;
	foreach($tasks as $task){
		foreach($users as $user){
			if($task['assigned_to'] == $user['id']){
				$num_tasks++;
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

    if(isset($_GET['due_date']) && $_GET['due_date'] == "Due Today"){
        $text = "Due Today";
        $tasks = get_all_tasks_due_today($conn);
        $num_task = $due_today_task;
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Overdue"){
        $text = "Overdue";
        $tasks = get_all_tasks_overdue($conn);
        $num_task = $overdue_task;
    }else if(isset($_GET['due_date']) && $_GET['due_date'] == "Completed"){
        $text = "Completed";
        $tasks = get_all_tasks_completed($conn);
        $num_task = $completed;
    }else{
        $tasks = get_all_tasks($conn);
        $num_task = $num_tasks;
    }
    
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
	<?php include "inc/nav-admin.php"; ?>
		<section class="section-1">
			<h4 class="title-2">  
                <a href="create_task-department.php" class="btn">Create Task</a>
                <a href="task-department.php" class="btn">All Task</a>
                <a href="task-department.php?due_date=Due Today" class="btn">Due Today</a>
                <a href="task-department.php?due_date=Overdue" class="btn">Overdue</a>
                <a href="task-department.php?due_date=Completed" class="btn">Completed</a>
            </h4>
            <h4 class="title-2"><?=$text?> (<?=$num_task?>)</h4>
            <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
            <?php } ?>
            <?php if ($tasks != 0 && $num_task != 0) { ?>
                
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
                <?php $i=0; $j=0; foreach($tasks as $task){ 
                    foreach($users as $user){
                        if($user['department'] == $_SESSION['department'] && $user['id']==$task['assigned_to']){
                            ++$j;
                ?>
                <tr>
                    <td><?=++$i?></td>
                    <td><?=$task['title']?></td>
                    <td><?=$task['description']?></td>
                    <td>
                        <?php echo $user['full_name'];?>
                    </td>
                    <td><?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00") echo "No Deadline";
                                    else echo $task['due_date'];?></td>
                    <td><?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00" ) echo $task['status'];
                              else if($task['status'] == "completed") echo "Completed";
                              else if($task['due_date'] < $currentdate) echo "Overdue";
                              else if($task['status'] == "pending") echo "Pending";
                              else if($task['status'] == "in_progress") echo "In Progress";
                    else echo $task['status'];?></td>
                    <td>
                        <a href="edit-task-department.php?id=<?=$task['id']?>" class="edit-btn">Edit</a>
                        <a href="delete-task-department.php?id=<?=$task['id']?>" class="delete-btn">Delete</a>
                    </td>
                </tr>
                <?php }}} ?>
            
            </table>
            <?php if($j == 0){?>
                <h3 class="title">Empty</h3>
            <?php }?>
            <?php }else{ ?>
                <h3 class="title">Empty</h3>
            <?php }?>
		</section>
	</div>
    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(4)");
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