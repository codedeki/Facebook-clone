<?php 

include("includes/header.php");

if (isset($_GET['q'])) {
    $query = $_GET['q'];
}
else {
    $query = "";
}

if (isset($_GET['type'])) {
    $type = $_GET['type'];
}
else {
    $type = "name";
}

?>

<div class="main_column column" id="main_column">
    <?php 
    if ($query == "") 
        echo "You must enter something in the search box";

    if ($type == "username") 
        $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND userClosed='no' LIMIT 8");
    
    else {
        $names = explode(" ", $query);

        if (count($names) == 3) 
            $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (firstName LIKE '$names[0]%' AND lastName LIKE '$names[2]%') AND userClosed='no'");
        
        else if (count($names) == 2) 
            $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (firstName LIKE '$names[0]%' AND lastName LIKE '$names[1]%') AND userClosed='no'");
        
        else 
            $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (firstName LIKE '$names[0]%' OR lastName LIKE '$names[0]%') AND userClosed='no'");
    }

    if (mysqli_num_rows($usersReturnedQuery) == 0) 
        echo "We can't find anyone with a " . $type . " like: " . $query;
    else 
        echo mysqli_num_rows($usersReturnedQuery) . " results found: <br> <br>";
    
    echo "<p id='grey'>Try searching for: </p>";
    echo "<a href='search.php?q=". $query ."&type=name'>Names</a>, <a href='search.php?q=". $query . "&type=username'>Usernames</a><br><br><hr id='search_hr'>";
    
    while ($row = mysqli_fetch_array($usersReturnedQuery)) {
        $user_obj = new User($con, $user['username']);

        $button = "";
        $mutual_friends = "";

        if ($user['username'] != $row['username']) {

            //generate button depending on friend status
            if ($user_obj->isFriend($row['username'])) 
                $button = "<input type='submit' name='" . $row['username'] ."' class='danger' value='Remove Friend'>";
            else if ($user_obj->didReceiveRequest($row['username'])) 
                $button = "<input type='submit' name='" . $row['username'] ."' class='warning' value='Respond to Request'>";
            else if ($user_obj->didSendRequest($row['username'])) 
                $button = "<input type='submit' class='default' value='Request Sent'>";
            else 
                $button = "<input type='submit' name='" . $row['username'] ."' class='success' value='Add Friend'>";

        
        $mutual_friends = $user_obj->getMutualFriends($row['username']) . " friends in common";

        //Button forms
            if (isset($_POST[$row['username']])) {
                if ($user_obj->isFriend($row['username'])) {
                    $user_obj->removeFriend($row['username']);
                    header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                }
                else if ($user_obj->didReceiveRequest($row['username'])) {
                    header("Location: requests.php");
                }
                else if ($user_obj->didSendRequest($row['username'])) {
                    //Empty for now, fill if necessary
                }
                else {
                    $user_obj->addFriend($row['username']);
                    header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                }
            }
        
        }

        echo "<div class='search_result'>
                <div class='searchPageFriendButtons'>
                    <form action='' method='POST'>
                    ". $button . "
                    <br>
                    </form>
                </div>

                <div class='result_profile_pic'>
                    <a href='". $row['username'] ."'><img src='".$row['profilePic']."' style='height: 100px;'></a>
                </div>

                    <a href='". $row['username'] ."'>".$row['firstName']. " " . $row['lastName']."
                    <p id='grey'>" . $row['username'] . "</p>
                    </a>
                    <br>
                    " . $mutual_friends . "<br>
                </div>
                <hr id='search_hr'>";
    } //End while

    ?>

</div>
