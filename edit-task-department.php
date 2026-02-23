<?php
session_name("admin-department");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
    if(!isset($_GET['id'])){
        header("Location: task-department.php");
        exit();
    }
    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);
    if($task == 0){
        header("Location: task-department.php");
        exit();
    }
    $users = get_all_users($conn);
    $user = get_user_by_id($conn, $_SESSION['id']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Task</title>
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
			<h4 class="title">Edit Task  <a href="task-department.php">Task</a></h4>
            <form class="form-1" method="POST" action="app/update-task-department.php">
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
                <div class="form-floating input-holder">
                    <input type="text" id="title" class="form-control input-1" name="title" placeholder="Title" value="<?=$task['title']?>"><br>
                    <label for="title"><i class="fas fa-tasks"></i>Title</label>
                </div>
                <div class="form-floating mb-3 input-holder">
                    <textarea id="description" class="form-control input-1" name="description" placeholder="Description"><?=$task['description']?></textarea><br>
                    <label for="description"><i class="fas fa-book"></i>Description</label>
                </div>
                <div class="form-floating input-holder">
                    <input type="date" id="due_date" class="form-control input-1" name="due_date" placeholder="Snooze" value="<?=$task['due_date']?>"><br>
                    <label for="due_date"><i class="fas fa-calendar"></i>Deadline</label>
                </div>
                <div class="form-floating mb-3 input-holder">
					<select id="status" name="status" class="form-control input-1">
						<option <?php if($task['status'] == "pending") echo"selected";?>>pending</option>
						<option <?php if($task['status'] == "in_progress") echo"selected";?>>in_progress</option>
						<option <?php if($task['status'] == "completed") echo"selected";?>>completed</option>
					</select><br>
                    <label for="status"><i class="fas fa-hourglass"></i>Status</label>
				</div>
                <div class="form-floating mb-3 input-holder">
					<select id="assigned_to" name="assigned_to" class="form-control input-1">
						<option value="0">Select employee</option>
						<?php if ($users !=0) { 
							foreach ($users as $user) {
                                if($task['assigned_to']==$user['id']){?>
                                <option selected value="<?=$user['id']?>"><?=$user['full_name']?></option>
						<?php }else{ if($user['department'] == $_SESSION['department']){?>
                        <option value="<?=$user['id']?>"><?=$user['full_name']?></option>
						<?php } } } }?>
					</select><br>
                    <label for="assigned_to"><i class="fas fa-user"></i>Assigned to</label>
				</div>
                <input type="text" name="id" value="<?=$task['id']?>" hidden>
                <button class="edit-btn">Update</button>
            </form>
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
	header("Location: login.php?error=$em");
	exit();
}
?>