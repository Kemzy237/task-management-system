<header class="header">
    <h2 class="u-name">Task <b>Master</b>
        <label for="checkbox">
            <i id="navbtn" class="fas fa-bars font" aria-hidden="true"></i>
        </label>
    </h2>
</header>
<div class="notification-bar" id="notificationBar">
    <ul id="notifications">
        
    </ul>
</div>
<script src="jquery.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#notificationNum").load("app/notification-count.php");
        $("#notifications").load("app/notification.php");
    });
</script>