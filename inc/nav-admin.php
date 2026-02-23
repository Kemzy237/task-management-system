<nav class="side-bar">
			<div class="user-p">
				<img src="img/shiny.jpg">
				<h4>@<?=$_SESSION['username']?></h4>
			</div>
			<!-- Admin Navigation Bar -->
			<ul id="navList" style="padding:0px;">
				<li>
					<a href="index-admin.php">
						<i class="fa fa-tachometer font" aria-hidden="true"></i>
						<span>Dashboard</span>
					</a>
				</li>
				<li>
					<a href="user-department.php">
						<i class="fa fa-users font" aria-hidden="true"></i>
						<span>Manage Users</span>
					</a>
				</li>
				<li>
					<a href="create_task-department.php">
						<i class="fa fa-plus font" aria-hidden="true"></i>
						<span>Create Task</span>
					</a>
				</li>
				<li>
					<a href="task-department.php">
						<i class="fa fa-tasks font" aria-hidden="true"></i>
						<span>All Tasks</span>
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