<nav class="side-bar">
			<div class="user-p">
				<img src="img/shiny.jpg">
				<h4>@<?=$_SESSION['username']?></h4>
			</div>
			<?php
			 	if($_SESSION['role'] == "employee"){
			?>
			<!-- Employee Navigation Bar -->
			<ul id="navList" style="padding:1px;">
				<li>
					<a href="index.php">
						<i class="fa fa-tachometer font" aria-hidden="true"></i>
						<span>Dashboard</span>
					</a>
				</li>
				<li>
					<a href="my-task.php">
						<i class="fa fa-tasks font" aria-hidden="true"></i>
						<span>My Task</span>
					</a>
				</li>
				<li>
					<a href="profile.php">
						<i class="fa fa-user font" aria-hidden="true"></i>
						<span>Profile</span>
					</a>
				</li>
				<li>
					<a href="notifications.php">
						<i class="fa fa-bell font" aria-hidden="true"></i>
						<span>Notifications</span>
					</a>
				</li>
				<li>
					<a href="logout.php">
						<i class="fa fa-sign-out font" aria-hidden="true"></i>
						<span>Logout</span>
					</a>
				</li>
			</ul>
		<?php }else {?>
			<!-- Admin Navigation Bar -->
			<ul id="navList" style="padding:0px;">
				<li>
					<a href="index.php">
						<i class="fa fa-tachometer font" aria-hidden="true"></i>
						<span>Dashboard</span>
					</a>
				</li>
				<li>
					<a href="user.php">
						<i class="fa fa-users font" aria-hidden="true"></i>
						<span>Manage Users</span>
					</a>
				</li>
				<li>
					<a href="department.php">
						<i class="fa-solid fa-sitemap font" aria-hidden="true"></i>
						<span>Departments</span>
					</a>
				</li>
				<li>
					<a href="create_task.php">
						<i class="fa fa-plus font" aria-hidden="true"></i>
						<span>Create Task</span>
					</a>
				</li>
				<li>
					<a href="task.php">
						<i class="fa fa-tasks font" aria-hidden="true"></i>
						<span>All Tasks</span>
					</a>
				</li>
				<!-- <li>
					<a href="admin-profile.php">
						<i class="fa fa-user font" aria-hidden="true"></i>
						<span>Profile</span>
					</a>
				</li> -->
				<li>
					<a href="logout.php">
						<i class="fa fa-sign-out font" aria-hidden="true"></i>
						<span>Logout</span>
					</a>
				</li>
			</ul>
		<?php } ?>
		<!-- <a href="#" onclick="changeStyleSheet('css/nightmode.css'); return false" id="list"><i class="fas fa-moon font"></i></a>
    	<a style="float: right;" href="#" onclick="changeStyleSheet('css/styles.css'); return false" id="list"><i class="fas fa-sun font"></i></a>
			 -->
		</nav>