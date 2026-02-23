<?php
    include "DB_connection.php";
    include "app/model/Department.php";
    $departments = get_all_departments($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Task Management System</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
	<link rel="manifest" href="favicon/site.webmanifest">
</head>
<body class="login-body">
    <form method="POST" action="app/register.php" class="shadow p-4" id="form-shadow">
        <div class="mzx">
            <img src="favicon/android-chrome-192x192.png" alt="Logo">
        </div>
        <h3 class="display-4" style="text-align: center;">WELCOME</h3>
        <?php if (isset($_GET['error'])) {?>
            <div class="alert alert-danger" role="alert">
                <?php echo stripcslashes($_GET['error']); ?>
            </div>
        <?php } ?>
        <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
        <?php }?>
        <div class="form-floating mb-3 input-holder ">
            <input type="text" class="form-control input-1 log" id="fullname" name="full_name" placeholder="Full Name"><br>
            <label for="fullname"><i class="fas fa-user"> </i>Full Name</label>
        </div>
        <div class="form-floating mb-3 input-holder">
            <input type="text" id="username" class="form-control input-1 log" name="user_name" placeholder="Username"><br>
            <label for="username"><i class="fas fa-user"> </i>Username</label>
        </div>
        <div class="form-floating mb-3 input-holder">
            <input type="text" id="password" class="form-control input-1 log" name="password" placeholder="Password"><br>
            <label for="password"><i class="fas fa-key"> </i>Password</label>
        </div>
        <div class="form-floating mb-3 input-holder">
            <input type="text" id="contact" class="form-control input-1 log" name="contact" placeholder="Contact"><br>
            <label for="contact"><i class="fas fa-address-book"> </i>Contact</label>
        </div>
        <div class="form-floating mb-3 input-holder">
            <select name="department" id="department" class="form-control input-1">
                <option value="0">Select department</option>
                <?php if ($departments !=0) { 
                    foreach ($departments as $department) {
                ?>
                <option value="<?=$department['id']?>"><?=$department['name']?></option>
                <?php } } ?>
            </select>
            <label for="department">Department</label>
        </div><br>
        <div class="box">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
        <div class="back" style="text-align: center; color: black;">
            <a href="homepage.php"><i class="fa fa-reply"></i></a>
        </div>
        
    </form>


    <script src="javascript/all.js"></script>
    <script src="javascript/all.min.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>