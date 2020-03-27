<?php

include("../config.php");
include("../../classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);

//if query contains underscore, assume user is searching for usernames

if (strpos($query, '_') !== false) {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND userClosed='no' LIMIT 8");
}
//if there are two words, assume they are first and last names respectively
else if (count($names) == 2) {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (firstName LIKE '$names[0]%' AND lastName LIKE '$names[1]%') AND userClosed='no' LIMIT 8");
}
//if query has one word only, search first names or last names
else  {
    $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (firstName LIKE '$names[0]%' OR lastName LIKE '$names[0]%') AND userClosed='no' LIMIT 8");
}

if ($query != "") {
    while ($row = mysqli_fetch_array($usersReturnedQuery)) {
        $user = new User($con, $userLoggedIn);

        if ($row['username'] != $userLoggedIn) {
            $mutual_friends = $user->getMutualFriends($row['username']) . " friends in common";
        }
        else {
            $mutual_friends == "";
        }

        echo "<div class='resultDisplay'>
                <a href='" . $row['username'] . "' style='color: #1485bd'>
                    <div class='liveSearchProfilePic'>
                        <img src='" . $row['profilePic'] . "'> 
                    </div>

                    <div class='liveSearchText'>
                        " . $row['firstName'] . " " . $row['lastName']. "
                        <p>" . $row['username'] . "</p>
                        <p id='grey'>" . $mutual_friends . "</p>
                    </div>
                </a>
              </div>";
    }
}

?>