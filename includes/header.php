<?php
include("includes/handlers/config.php");
include("includes/classes/User.php");
include("includes/classes/Post.php");

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
        <a href="<?php echo $userLoggedIn; ?>">
            <?php echo $user['firstName']; ?>
        </a>
        <a href="index.php">
            <i class="fa fa-home fa-lg"></i>
        </a>
        <a href="#">
            <i class="fa fa-envelope fa-lg"></i>
        </a>
        <a href="#">
            <i class="fa fa-bell-o fa-lg"></i>
        </a>
        <a href="requests.php">
            <i class="fa fa-users fa-lg"></i>
        </a>
        <a href="#">
            <i class="fa fa-cog fa-lg"></i>
        </a>
        <a href="includes/handlers/logout.php">
            <i class="fa fa-sign-out fa-lg"></i>
        </a>
    </nav>
</div>

<div class="wrapper">