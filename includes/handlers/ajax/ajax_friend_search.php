<?php 
include("../config.php");
include("../../classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];

$names = explode(" ", $query);
//if underscore in query, then we guess user is searching for a username
if (strpos($query, "_") !== false) {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND userClosed='no' LIMIT 8");
}
else if (count($names) == 2) {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (firstName LIKE '%$names[0]%' AND lastName LIKE '%$names[1]%') AND userClosed='no' LIMIT 8");
}
else {
    $usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (firstName LIKE '%$names[0]%' OR lastName LIKE '%$names[0]%') AND userClosed='no' LIMIT 8");
}

if ($query != "") {
    while ($row = mysqli_fetch_array($usersReturned)) {
        $user = new User ($con, $userLoggedIn);

        if ($row['username'] != $userLoggedIn) {
            $mutual_friends = $user->getMutualFriends($row['username']) . " friends in common";
        }
        else {
            $mutual_friends = "";
        }

        if ($user->isFriend($row['username'])) {
            echo "<div class='resultDisplay'>
                    <a href='messages.php?u=" . $row['username'] . "' style='color:#000'>
                        <div class='liveSearchProfilePic'>
                            <img src='" . $row['profilePic'] . "'> 
                        </div>

                        <div class='liveSearchText'>
                            ". $row['firstName'] . " " . $row['lastName'] . "
                            <p style='margin: 0;'>" . $row['username'] . "</p>
                            <p id='grey'>" . $mutual_friends . "</p>
                        </div>
                        </a>
                    </div>";
        }
    }
}

?>