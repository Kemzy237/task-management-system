<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Task Management System</title>
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
    <form method="POST" action="app/login.php" class="shadow p-4" id="form-shadow">
        <div class="mzx">
            <img src="favicon/android-chrome-192x192.png" alt="Logo">
        </div>
        <h3 class="display-4">LOGIN</h3>
        <?php if (isset($_GET['error'])) {?>
            <div class="alert alert-danger" role="alert">
                <?php echo stripcslashes($_GET['error']); ?>
            </div>
        <?php } ?>
        <?php if (isset($_GET['success'])) {?>
            <div class="alert alert-success" role="alert">
                <?php echo stripcslashes($_GET['success']); ?>
            </div>
        <?php } 
        // $pass = "123";
        // $pass = password_hash($pass, PASSWORD_DEFAULT);
        // echo $pass;
        ?>
        <div class="form-floating mb-3">
            <input type="text" id="username" placeholder="User Name" class="form-control log" name="user_name">
            <label for="username" class="form-label"><span><i class="fa fa-user" aria-hidden="true"></i></span>User name </label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control log" placeholder="Password" name="password" id="password">
            <label for="password" class="form-label"><i class="fa fa-key" aria-hidden="true"></i>Password</label>
        </div>
        <div class="box">
            <button type="submit" class="btn btn-primary">Login</button>
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