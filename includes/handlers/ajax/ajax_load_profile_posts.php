<?php 

include("../config.php");
include("../../classes/User.php");
include("../../classes/Post.php");
include("../../classes/Notification.php");

$limit = 10; //number of pots to be loaded per call

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadProfilePosts($_REQUEST, $limit);

?>