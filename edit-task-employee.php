<?php
session_name("employee");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "employee"){
    include "DB_connection.php";
    include "app/model/Task.php";
    include "app/model/user.php";
    if(!isset($_GET['id'])){
        header("Location: task.php");
        exit();
    }
    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);
    if($task == 0){
        header("Location: task.php");
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
	<?php include "inc/header-user.php"; ?>
	<div class="body">
	<?php include "inc/nav-user.php"; ?>
		<section class="section-1">
			<h4 class="title">Edit Task     <a href="my-task.php">My Task</a></h4>
            <form class="form-1" method="POST" action="app/update-task-employee.php">
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
                <div class="input-holder">
                    <label></label>
                    <p><b>Title: </b><?=$task['title']?></p><br>
                </div>
                <div class="input-holder">
                    <label></label>
                    <p><b>Description: </b><?=$task['description']?></p><br>
                </div>
                <?php if($task['status'] == "completed"){?>
                    <div class="input-holder">
                        <label></label>
                        <p><b>Status: </b><?=$task['status']?></p><br>
                    </div>
                <?php } else{?>
                <div class="form-floating mb-3 input-holder">
					<select id="status" name="status" class="form-control input-1">
						<option <?php if($task['status'] == "pending") echo"selected";?>>pending</option>
						<option <?php if($task['status'] == "in_progress") echo"selected";?>>in_progress</option>
						<option <?php if($task['status'] == "completed") echo"selected";?>>completed</option>
					</select><br>
                    <label for="status"><i class="fas fa-hourglass"></i>Status</label>
				</div>
                <?php } ?>
                <input type="text" name="id" value="<?=$task['id']?>" hidden>
                <button class="edit-btn">Update</button>
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