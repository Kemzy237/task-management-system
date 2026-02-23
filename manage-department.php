<?php
session_name("admin");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/user.php";
    include "app/model/Task.php";
    include "app/model/Department.php";
    if(!isset($_GET['id'])){
        header("Location: department.php");
        exit();
    }
    $id = $_GET['id'];
    $department = get_department_by_id($conn, $id);
    if($department == 0){
        header("Location: department.php");
        exit();
    }
    $role="employee";
    $data = array($role, $department['id']);
    $users = get_users_by_department($conn, $data);
    $tasks = get_all_tasks($conn);
    $admins=get_all_admins($conn);
    $currentdate = date('Y-m-d');
    foreach($admins as $admin){
        if($admin["department"] == $id){
            $name = $admin['full_name'];
            $contact = $admin['contact'];
            $username = $admin['username'];
        }
    }
    $num_users=0;
    $num_task=0;
    if($users!=0){
        $members = get_users_by_department($conn, $data);
        foreach($members as $member){
            $num_users++;
        }
        $jobs = get_all_tasks($conn);
        foreach($jobs as $job){
            foreach($members as $member){
                if($job['assigned_to'] == $member['id']){
                    $num_task++;
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
	<title>Department of <?=$department['name']?></title>
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
			<h3 class="display-6"><?=$department['name']?> <span><a href="edit-department.php?id=<?=$id?>" class="btn btn-success">Edit</a></span> <span><a href="delete-department.php?id=<?=$id?>" class="btn btn-danger">Delete</a></span> <span><a href="department.php" class="btn btn-primary">Departments</a></span></h3>
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
            <hr>
            <?php if(!empty($name) && !empty($username) && !empty($contact)){ ?>
                <div class="info">
                    <div class="info-item">
                        <p><b>Admin of department</b></p>
                        <p><?=$name?></p>
                    </div>
                    <div class="info-item">
                        <p><b>User Name</b></p>
                        <p><?=$username?></p>
                    </div>
                    <div class="info-item">
                        <p><b>Contact</b></p>
                        <p><?=$contact?></p>
                    </div>
                </div>
            <?php }else{ ?>
                <h3 class="title">Admin info has not yet being entered <span><a href="add-user.php" class="btn btn-success">Add</a></span> </h3>
            <?php }?>
            <hr>
            <h3 class="title">Users(<?=$num_users?>)</h3>
            <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
            <?php } ?>
            <?php if ($users != 0) { ?>
            <table class="main-table table table-striped table-dark table-hover">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th colspan="2">Action</th>
                </tr>
                <?php $i=0; foreach($users as $user){ ?>
                <tr>
                    <td><?=++$i?></td>
                    <td><?=$user['full_name']?></td>
                    <td><?=$user['username']?></td>
                    <td>
                        <a href="edit-user.php?id=<?=$user['id']?>" class="edit-btn">Edit</a>
                        <a href="delete-user.php?id=<?=$user['id']?>" class="delete-btn">Delete</a>
                        <a href="task-details.php?id=<?=$user['id']?>" class="detail-btn">Details</a>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <?php }else{ ?>
                <h3>Empty</h3>
            <?php }?>
            <hr>
            <h4 class="title-2">Tasks (<?=$num_task?>)</h4>
            <?php if($users!=0){ ?>
                <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
            <?php } ?>
            <?php if ($tasks != 0) { $j=0;?>
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
                <?php $i=0; foreach($tasks as $task){
                foreach($users as $user){
                if($task['assigned_to'] == $user['id']){
                    $j++;
                ?>
                <tr>
                    <td><?=++$i?></td>
                    <td><?=$task['title']?></td>
                    <td><?=$task['description']?></td>
                    <td><?=$user['full_name']?></td>
                    <td><?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00") echo "No Deadline";
                                    else echo $task['due_date'];?></td>
                    <td><?php if($task['due_date'] == "" || $task['due_date'] == "0000-00-00") echo $task['status'];
                              else if($task['status'] == "completed") echo "Completed";
                              else if($task['due_date'] < $currentdate) echo "Overdue";
                              else if($task['status'] == "pending") echo "Pending";
                              else if($task['status'] == "in_progress") echo "In Progress";
                    else echo $task['status'];?></td>
                    <td>
                        <a href="edit-task.php?id=<?=$task['id']?>" class="edit-btn">Edit</a>
                        <a href="delete-task.php?id=<?=$task['id']?>" class="delete-btn">Delete</a>
                    </td>
                </tr>
                <?php }} } ?>
            </table>
            <?php }else{ ?>
                <h3 class="title">Empty</h3>
            <?php }?>
            <?php } else{ ?>
                <h3 class="title">Empty</h3>
            <?php } ?>
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