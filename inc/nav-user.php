<nav class="side-bar">
			<div class="user-p">
				<img src="img/shiny.jpg">
				<h4>@<?=$_SESSION['username']?></h4>
			</div>
			<!-- Employee Navigation Bar -->
			<ul id="navList" style="padding:1px;">
				<li>
					<a href="index-user.php">
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
		</nav>