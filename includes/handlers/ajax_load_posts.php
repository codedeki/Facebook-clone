<?php 

include("../handlers/config.php");
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10; //number of pots to be loaded per call

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadPostsFriends($_REQUEST, $limit);



?>