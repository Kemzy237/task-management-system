<style>
    body{
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .box{
        padding: 10px;
    }
    .graph{
        border: 1px solid black;
        border-radius: 10px;
    }
</style>
<body>
<?php

    $departments = get_all_departments($conn);
    $tasks = get_all_tasks($conn);
    $m = 0;

    foreach($departments as $department){
        $role="employee";
        $data = array($role, $department['id']);
        $users = get_users_by_department($conn, $data);
        if($users != 0){
        $num=0;
        foreach($users as $user){
            $num++;
        }
        $currentdate = date('Y-m-d');
        $num_task=0;
        $finished=0;
        $in_progress=0;
        $overdue=0;
        $pending=0;
        foreach($tasks as $task){
            foreach($users as $user){
                if($task['assigned_to'] == $user['id']){
                    $num_task++;
                    if($task['status'] == "completed"){
                        $finished++;
                    }
                    if($task['status'] == "in_progress" && $task['due_date'] == $currentdate || ($task['due_date'] == "" || $task['due_date'] == "0000-00-00")){
                        $in_progress++;
                    }
                    if($task['due_date'] < $currentdate && ($task['due_date'] == "" || $task['due_date'] != "0000-00-00") && $task['status'] != "completed"){
                        $overdue++;
                    }
                    if($task['status'] == "pending" && $task['due_date'] >= $currentdate || ($task['due_date'] == "" || $task['due_date'] == "0000-00-00") && $task['status'] != "in_progress"){
                        $pending++;
                    }
                }
            }
        }
        $m++;

?>
	<script type="text/javascript" src="loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Status', 'Number of tasks'],
          ['Pending',     <?=$pending?>],
          ['Completed',      <?=$finished?>],
          ['In Progress',  <?=$in_progress?>],
          ['Overdue',    <?=$overdue?>]
        ]);

        var options = {
          title: '<?=$department['name']?>(<?=$num?>)',
          is3D: true,
		  legend: {textStyle: {color: 'black'}},
		  titleTextStyle: {color: 'black'}
        };

        var chart = new google.visualization.PieChart(document.getElementById('<?=$m?>'));
        chart.draw(data, options);
      }
    </script>

            <div class="box">
                <div class="graph" id="<?=$m?>" style="width: 600px; height: 300px;"></div>
            </div>
        <?php }else{?>
<?php }}?>
</body>
<script src="bootstrap/css/bootstrap.js"></script>