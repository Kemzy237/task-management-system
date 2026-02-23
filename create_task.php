<?php
session_name("admin");
session_start();
if(isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin"){
    include "DB_connection.php";
    include "app/model/user.php";
    $users = get_all_users($conn);
	$user = get_user_by_id($conn, $_SESSION['id']);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Create Task</title>
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
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
			<h4 class="title">Create Task </h4>
		   <form class="form-1" method="POST" action="app/add-task.php">
			      <?php if (isset($_GET['error'])) {?>
      	  	<div class="danger" role="alert">
			  <?php echo stripcslashes($_GET['error']); ?>
			</div>
      	  <?php } ?>

      	  <?php if (isset($_GET['success'])) {?>
      	  	<div class="success" role="alert">
			  <?php echo stripcslashes($_GET['success']); ?>
			</div>
      	  <?php } ?>
				<div class="form-floating mb-3 input-holder">
					<input type="text" id="title" name="title" class="form-control input-1" placeholder="Title"><br>
					<label for="title">Title</label>
				</div>
				<div class="form-floating mb-3 input-holder">
					<textarea type="text" id="description" name="description" class="form-control input-1" placeholder="Description"></textarea><br>
					<label for="description">Description</label>
				</div>
				<div class="form-floating mb-3 input-holder">
					<input type="date" id="due_date" name="due_date" class="form-control input-1" placeholder="Due Date"><br>
					<label for="due_date">Due Date</label>
				</div>
				<div class="form-floating mb-3 input-holder">
					<select name="assigned_to" id="assigned_to" class="form-control input-1">
						<option value="0">Select employee</option>
						<?php if ($users !=0) { 
							foreach ($users as $userr) {
						?>
                        <option value="<?=$userr['id']?>"><?=$userr['full_name']?></option>
						<?php } } ?>
					</select>
					<label for="assigned_to">Assigned to</label>
				</div><br>
				<button class="edit-btn">Create Task</button>
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