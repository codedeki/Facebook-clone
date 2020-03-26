<?php
include("includes/handlers/config.php");
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");
include("includes/classes/Notification.php");

//user must be logged in to access index.php
if(isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = mysqli_fetch_array($user_details_query); //returns all info of user as array
} 
else {
    header("Location: register.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Facebook</title>
    <!-- Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/bootbox.min.js"></script>
    <script src="assets/js/submit_post.js"></script>
    <!-- CSS -->
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head> 
<body>

<div class="top_bar">
    <div class="logo">
        <a href="index.php">Fakebook</a>
    </div>

    <nav>

        <?php 
            //unread messages
            $messages = new Message($con, $userLoggedIn);
            $num_messages = $messages->getUnreadNumber();

            //unread notifications
            $notifications = new Notification($con, $userLoggedIn);
            $num_notifications = $notifications->getUnreadNumber();

            //unread notifications
            $user_obj = new User($con, $userLoggedIn);
            $num_requests = $user_obj->getNumberOfFriendRequests();
        ?>

        <a href="<?php echo $userLoggedIn; ?>">
            <?php echo $user['firstName']; ?>
        </a>
        <a href="index.php">
            <i class="fa fa-home fa-lg"></i>
        </a>
        <a href="Javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')">
            <i class="fa fa-envelope fa-lg"></i>
            <?php
            if ($num_messages > 0) {
              echo '<span class="notification_badge" id="unread_message">' . $num_messages . '</span>';
            }
            ?>
        </a>
        <a href="Javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
            <i class="fa fa-bell-o fa-lg"></i>
            <?php
            if ($num_notifications > 0) {
              echo '<span class="notification_badge" id="unread_notification">' . $num_notifications . '</span>';
            }
            ?>
        </a>
        <a href="requests.php">
            <i class="fa fa-users fa-lg"></i>
            <?php
            if ($num_requests > 0) {
              echo '<span class="notification_badge" id="unread_requests">' . $num_requests . '</span>';
            }
            ?>
        </a>
        <a href="#">
            <i class="fa fa-cog fa-lg"></i>
        </a>
        <a href="includes/handlers/logout.php">
            <i class="fa fa-sign-out fa-lg"></i>
        </a>
    </nav>

    <div class="dropdown_data_window" style="height:0px; border:none;"></div>
    <input type="hidden" id="dropdown_data_type" value="">

</div>

<script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';
        $(document).ready(function() {

            $('.dropdown_data_window').scroll(function() {
                var inner_height = $('.dropdown_data_window').innerHeight(); //div containing data
                var scroll_top = $('.dropdown_data_window').scrollTop();
                var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
                var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

                if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {
               
                    var pageName; // holds name of page to send ajax request to
                    var type = $('#dropdown_data_type').val();

                    if (type == 'notification') {
                        pageName = "ajax_load_notifications.php";
                    } else if (type == 'mesage') {
                        pageName = "ajax_load_messages.php";
                    }

                    var ajaxReq = $.ajax({
                    url:  "includes/handlers/ajax/" + pageName,
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache: false,

                    success: function(response) {

                        $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //removes current .nextpage
                        $('.dropdown_data_window').find('.noMoreDropdownData').remove(); //removes current .nextpage

                        $(".dropdown_data_window").append(response);
                    
                            }
                        });
                } //End if 

                return false;

            }); //End (window).scroll function
        });


</script>

<div class="wrapper">