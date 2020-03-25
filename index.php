<?php
include_once("includes/handlers/config.php");
include_once("includes/footer.php");
include_once("includes/header.php");
include_once("includes/classes/User.php");
include_once("includes/classes/Post.php");

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
            <br>
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

        <div class="posts_area"></div>
        <img src="assets/images/icons/loading.gif" alt="loading screen" id="loading">
    </div>

    <script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';
        $(document).ready(function() {
            $('#loading').show();
            //ajax request for loading ifrst posts
            $.ajax({
                url:  "includes/handlers/ajax/ajax_load_posts.php",
                type: "POST",
                data: "page=1&userLoggedIn=" + userLoggedIn,
                cache: false,

                success: function(data) {
                    $("#loading").hide();
                    $(".posts_area").html(data);
                    
                }
            });

            $(window).scroll(function() {
                var height = $('.posts_area').height(); //div containing posts
                var scroll_top = $(this).scrollTop();
                var page = $('.posts_area').find('.nextPage').val();
                var noMorePosts = $('.posts_area').find('.noMorePosts').val();

                if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
                    $('#loading').show();
                    $.ajax({
                    url:  "includes/handlers/ajax/ajax_load_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache: false,

                    success: function(response) {

                        $('.posts_area').find('.nextPage').remove(); //removes current .nextpage
                        $('.posts_area').find('.noMorePosts').remove(); //removes current .nextpage

                        $("#loading").hide();
                        $(".posts_area").append(response);
                    
                            }
                        });
                } //End if 

                return false;

            }); //End (window).scroll function
        });


    </script>