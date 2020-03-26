<?php 

class Post {
    private $user_obj, $con;
    
    public function __construct($con, $user) {
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($body, $user_to) {
        $body = strip_tags($body); //remove html tags
        $body = mysqli_real_escape_string($this->con, $body);

        $body = str_replace('\r\n', '\n', $body);
        $body = nl2br($body); //add line breaks for spaces

        $check_empty = preg_replace('/\s+/', '', $body); //delets all spaces

        if ($check_empty != "") {

            //current date and time
            $date_added = date("Y-m-d H:i:s");
            //Get username
            $added_by = $this->user_obj->getUsername();
            //if user is on own profile, user_to is none
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            //insert post
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0')");
            $returned_id = mysqli_insert_id($this->con);

            //insert notification
            if ($user_to != 'none') {
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, "profile_post");
            }

            //update post count for user
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET numPosts='$num_posts' WHERE username='$added_by'");
        }
    }

    public function loadPostsFriends($data, $limit) {

        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1) 
            $start = 0;
        else 
            $start = ($page - 1) * $limit;

        $str = ""; //string to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //numbers of results checked(not necessarily posted)
            $count = 1;

            
            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['addedBy'];
                $date_time = $row['dateAdded'];

                //Prepare user_to string so it can be include even if not posted to a user

                if ($row['userTo'] == "none") {
                    $user_to = "";
                }
                else {
                    $user_to_obj = new User($this->con, $row['userTo']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['userTo'] . "'>" . $user_to_name . "</a>";
                }

                //check if user who posted has account closed
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    continue;
                }

                $user_logged_obj = new User($this->con, $userLoggedIn);
                if($user_logged_obj->isFriend($added_by)) {

                if ($num_iterations++ < $start) {
                    continue;
                }

                //once 10 posts have been loaded, break
                if ($count > $limit) {
                    break;
                } 
                else {
                    $count++;
                }

                if ($userLoggedIn == $added_by) {
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                }
                else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT firstName, lastName, profilePic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['firstName'];
                $last_name = $user_row['lastName'];
                $profile_pic = $user_row['profilePic'];

                ?>

                <script>
                    function toggle<?php echo $id; ?>() {

                        var target = $(event.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if (element.style.display == "block") {
                                element.style.display = "none";
                            } else {
                                element.style.display = "block";
                            }
                        }

                    }
                </script>


                <?php
                //check number of comments on post
                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE postId='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); //time of past
                $end_date = new DateTime($date_time_now); //current time
                $interval = $start_date->diff($end_date); // difference between dates

                //Time when the post was made (years, months, days, hours, minutes, and seconds)
                if ($interval->y >= 1) {
                    if ($interval == 1) 
                        $time_message = $interval->y . " year ago"; //produces "1 year ago"
                    else 
                        $time_message = $interval->y . " years ago"; //produces "2 years ago"  
                }
                else if ($interval-> m >= 1) {

                    if ($interval->d == 0) {
                        $days = " ago";
                    }
                    else if ($interval->d == 1) {
                        $days = $interval->d . " day ago";
                    }
                    else {
                        $days = $interval->d . " days ago";
                    }

                    if ($interval->m == 1) {
                        $time_message = $interval->m . " month" . $days;
                    } 
                    else {
                        $time_message = $interval->m . " months" . $days;
                    }
                }
                else if ($interval->d >= 1) {
                    if ($interval->d == 1) {
                        $time_message = "Yesterday";
                    }
                    else {
                        $time_message = $interval->d . " days ago";
                    }
                }
                else if ($interval->h >= 1) {
                    if ($interval->h == 1) {
                        $time_message = $interval->h . " hour ago";
                    }
                    else {
                        $time_message = $interval->h . " hours ago";
                    }
                }
                else if ($interval->i >= 1) {
                    if ($interval->i == 1) {
                        $time_message = $interval->i . " minute ago";
                    }
                    else {
                        $time_message = $interval->i . " minutes ago";
                    }
                }
                else  {
                    if ($interval->s < 30) {
                        $time_message = "Just now";
                    }
                    else {
                        $time_message = $interval->s . " seconds ago";
                    }
                }
                $str .= "<div class='status_post' onclick='javascript:toggle$id()'>
                            <div class='post_profile_pic'>
                            <a href='$added_by'> 
                                <img src='$profile_pic' width='50'>
                            </a>
                            </div>

                            <div class='postedBy' style='color:#acacac'>
                                <a href='$added_by'> $first_name $last_name</a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                                $delete_button
                            </div>

                            <div id='post_body'>
                                $body
                                <br>
                                <br>
                                <br>
                            </div>

                            <div class='newsfeedPostOptions'>
                                Comments($comments_check_num)&nbsp;&nbsp;
                                <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                            </div>

                        </div>
                        <div class='post_comment' id='toggleComment$id' style='display:none;'>
                            <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                        </div>
                        <hr>";
                }
                ?>
                <script>
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", { result: result });
                                
                                if (result) {
                                    location.reload();
                                }
                            })
                        });
                    });
                </script>
                <?php

            }  //end while loop

                if ($count > $limit) {
                    $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                                <input type='hidden' class='noMorePosts' value='false'>";
                } 
                else {
                    $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No
                    more posts to show!</p>";
                }
            }

        echo $str;
     }

    public function loadProfilePosts($data, $limit) {

        $page = $data['page'];
        $profileUser = $data['profileUsername'];
        $userLoggedIn = $this->user_obj->getUsername();

        if ($page == 1) 
            $start = 0;
        else 
            $start = ($page - 1) * $limit;

        $str = ""; //string to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((addedBy='$profileUser' AND userTo='none') OR userTo='$profileUser') ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; //numbers of results checked(not necessarily posted)
            $count = 1;

            
            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['addedBy'];
                $date_time = $row['dateAdded'];


                if ($num_iterations++ < $start) {
                    continue;
                }

                //once 10 posts have been loaded, break
                if ($count > $limit) {
                    break;
                } 
                else {
                    $count++;
                }

                if ($userLoggedIn == $added_by) {
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                }
                else {
                    $delete_button = "";
                }

                $user_details_query = mysqli_query($this->con, "SELECT firstName, lastName, profilePic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['firstName'];
                $last_name = $user_row['lastName'];
                $profile_pic = $user_row['profilePic'];

                ?>

                <script>
                    function toggle<?php echo $id; ?>() {

                        var target = $(event.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if (element.style.display == "block") {
                                element.style.display = "none";
                            } else {
                                element.style.display = "block";
                            }
                        }

                    }
                </script>


                <?php
                //check number of comments on post
                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE postId='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); //time of past
                $end_date = new DateTime($date_time_now); //current time
                $interval = $start_date->diff($end_date); // difference between dates

                //Time when the post was made (years, months, days, hours, minutes, and seconds)
                if ($interval->y >= 1) {
                    if ($interval == 1) 
                        $time_message = $interval->y . " year ago"; //produces "1 year ago"
                    else 
                        $time_message = $interval->y . " years ago"; //produces "2 years ago"  
                }
                else if ($interval-> m >= 1) {

                    if ($interval->d == 0) {
                        $days = " ago";
                    }
                    else if ($interval->d == 1) {
                        $days = $interval->d . " day ago";
                    }
                    else {
                        $days = $interval->d . " days ago";
                    }

                    if ($interval->m == 1) {
                        $time_message = $interval->m . " month" . $days;
                    } 
                    else {
                        $time_message = $interval->m . " months" . $days;
                    }
                }
                else if ($interval->d >= 1) {
                    if ($interval->d == 1) {
                        $time_message = "Yesterday";
                    }
                    else {
                        $time_message = $interval->d . " days ago";
                    }
                }
                else if ($interval->h >= 1) {
                    if ($interval->h == 1) {
                        $time_message = $interval->h . " hour ago";
                    }
                    else {
                        $time_message = $interval->h . " hours ago";
                    }
                }
                else if ($interval->i >= 1) {
                    if ($interval->i == 1) {
                        $time_message = $interval->i . " minute ago";
                    }
                    else {
                        $time_message = $interval->i . " minutes ago";
                    }
                }
                else  {
                    if ($interval->s < 30) {
                        $time_message = "Just now";
                    }
                    else {
                        $time_message = $interval->s . " seconds ago";
                    }
                }
                $str .= "<div class='status_post' onclick='javascript:toggle$id()'>
                            <div class='post_profile_pic'>
                            <a href='$added_by'> 
                                <img src='$profile_pic' width='50'>
                            </a>
                            </div>

                            <div class='postedBy' style='color:#acacac'>
                                <a href='$added_by'> $first_name $last_name</a> &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                                $delete_button
                            </div>

                            <div id='post_body'>
                                $body
                                <br>
                                <br>
                                <br>
                            </div>

                            <div class='newsfeedPostOptions'>
                                Comments($comments_check_num)&nbsp;&nbsp;
                                <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                            </div>

                        </div>
                        <div class='post_comment' id='toggleComment$id' style='display:none;'>
                            <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                        </div>
                        <hr>";
                ?>

                <script>
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", { result: result });
                                
                                if (result) {
                                    location.reload();
                                }
                            })
                        });
                    });
                </script>
                <?php

            }  //end while loop

                if ($count > $limit) {
                    $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
                                <input type='hidden' class='noMorePosts' value='false'>";
                } 
                else {
                    $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No
                    more posts to show!</p>";
                }
            }

        echo $str;
     }

     public function getSinglePost($post_id) {
       
        $userLoggedIn = $this->user_obj->getUsername();

        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE userTo='$userLoggedIn' AND link LIKE '%=$post_id'");

        $str = ""; //string to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

        if (mysqli_num_rows($data_query) > 0) {
            
            $row = mysqli_fetch_array($data_query);
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['addedBy'];
                $date_time = $row['dateAdded'];

                //Prepare user_to string so it can be include even if not posted to a user

                if ($row['userTo'] == "none") {
                    $user_to = "";
                }
                else {
                    $user_to_obj = new User($this->con, $row['userTo']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['userTo'] . "'>" . $user_to_name . "</a>";
                }

                //check if user who posted has account closed
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    return;
                }

                $user_logged_obj = new User($this->con, $userLoggedIn);
                if($user_logged_obj->isFriend($added_by)) {

                if ($userLoggedIn == $added_by) 
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                
                else 
                    $delete_button = "";
                

                $user_details_query = mysqli_query($this->con, "SELECT firstName, lastName, profilePic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['firstName'];
                $last_name = $user_row['lastName'];
                $profile_pic = $user_row['profilePic'];

                ?>

                <script>
                    function toggle<?php echo $id; ?>() {

                        var target = $(event.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if (element.style.display == "block") 
                                element.style.display = "none";
                             else 
                                element.style.display = "block";
                            
                        }
                    }
                </script>
                <?php
                //check number of comments on post
                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE postId='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time); //time of past
                $end_date = new DateTime($date_time_now); //current time
                $interval = $start_date->diff($end_date); // difference between dates

                //Time when the post was made (years, months, days, hours, minutes, and seconds)
                if ($interval->y >= 1) {
                    if ($interval == 1) 
                        $time_message = $interval->y . " year ago"; //produces "1 year ago"
                    else 
                        $time_message = $interval->y . " years ago"; //produces "2 years ago"  
                }
                else if ($interval-> m >= 1) {

                    if ($interval->d == 0) {
                        $days = " ago";
                    }
                    else if ($interval->d == 1) {
                        $days = $interval->d . " day ago";
                    }
                    else {
                        $days = $interval->d . " days ago";
                    }

                    if ($interval->m == 1) {
                        $time_message = $interval->m . " month" . $days;
                    } 
                    else {
                        $time_message = $interval->m . " months" . $days;
                    }
                }
                else if ($interval->d >= 1) {
                    if ($interval->d == 1) {
                        $time_message = "Yesterday";
                    }
                    else {
                        $time_message = $interval->d . " days ago";
                    }
                }
                else if ($interval->h >= 1) {
                    if ($interval->h == 1) {
                        $time_message = $interval->h . " hour ago";
                    }
                    else {
                        $time_message = $interval->h . " hours ago";
                    }
                }
                else if ($interval->i >= 1) {
                    if ($interval->i == 1) {
                        $time_message = $interval->i . " minute ago";
                    }
                    else {
                        $time_message = $interval->i . " minutes ago";
                    }
                }
                else  {
                    if ($interval->s < 30) {
                        $time_message = "Just now";
                    }
                    else {
                        $time_message = $interval->s . " seconds ago";
                    }
                }

                $str .= "<div class='status_post' onclick='javascript:toggle$id()'>
                            <div class='post_profile_pic'>
                            <a href='$added_by'> 
                                <img src='$profile_pic' width='50'>
                            </a>
                            </div>

                            <div class='postedBy' style='color:#acacac'>
                                <a href='$added_by'> $first_name $last_name</a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                                $delete_button
                            </div>

                            <div id='post_body'>
                                $body
                                <br>
                                <br>
                                <br>
                            </div>

                            <div class='newsfeedPostOptions'>
                                Comments($comments_check_num)&nbsp;&nbsp;
                                <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                            </div>

                        </div>
                        <div class='post_comment' id='toggleComment$id' style='display:none;'>
                            <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                        </div>
                        <hr>";

                ?>
                <script>
                    $(document).ready(function() {
                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Are you sure you want to delete this post?", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", { result: result });
                                
                                if (result) 
                                    location.reload();
                                
                            });
                        });
                    });
                </script>
                <?php
                }
                  else {
                      echo "<p>You cannot see this post because you are not friends with this user</p>";
                      return;
                  }
                }
                else {
                    echo "No post found. If you clicked a link, it may be broken</p>";
                    return;
                }

        echo $str;
     }
}
     



?>