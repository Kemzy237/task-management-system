<header class="header">
    <h2 class="u-name">Task <b>Master</b>
        <label for="checkbox">
            <i id="navbtn" class="fas fa-bars font" aria-hidden="true"></i>
        </label>
    </h2>
    <span class="notification" id="notificationBtn">
        <i class="fas fa-bell font" aria-hidden="true"></i>
        <span id="notificationNum"></span>
    </span>
</header>
<div class="notification-bar" id="notificationBar">
    <ul id="notifications">
        
    </ul>
</div>
<script type="text/javascript">

    var openNotification = false;
    
    const notification = ()=> {
        let notificationBar = document.querySelector("#notificationBar");
        if(openNotification){
            notificationBar.classList.remove('open-notification');
            openNotification = false;
        }else{
            notificationBar.classList.add('open-notification');
            openNotification = true;
        }
    }
    let notificationBtn = document.querySelector("#notificationBtn");
    notificationBtn.addEventListener("click", notification);
</script>
<script src="jquery.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#notificationNum").load("app/notification-count.php");
        $("#notifications").load("app/notification.php");
    });
</script>