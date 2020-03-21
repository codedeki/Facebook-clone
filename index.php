<?php
include("includes/handlers/config.php");
include("includes/footer.php");
include("includes/header.php");
include("includes/classes/User.php");
include("includes/classes/Post.php");

if (isset($_POST['post'])) {
    $post = new Post($con, $userLoggedIn);
    $post->submitPost($_POST['post_text'], 'none');
    header("Location: index.php");
}

?>

    <div class="user_details column">
        <a href="<?php echo $userLoggedIn; ?>"><img src="<?php echo $user['profilePic']; ?>" alt="profile picture"></a>

        <div class="user_details_left_right">
            <a href="<?php echo $userLoggedIn; ?>">
            <?php
                echo $user['firstName'] . " " . $user['lastName'];
            ?>
            </a>
            <?php 
                echo "Posts: " . $user['numPosts'] . "<br>";
                echo "Likes: " . $user['numLikes'];
            ?>
        </div>
    </div>

    <div class="main_column column">
        <form action="index.php" method="POST" class="post_form">
            <textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
            <input type="submit" name="post" id="post_button" value="Post">
        </form>

        <?php 
        
        $user_obj = new User($con, $userLoggedIn);
        echo $user_obj->getFirstAndLastName();
        
        
        ?>
    </div>