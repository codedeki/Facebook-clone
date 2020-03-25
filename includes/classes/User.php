<?php 

class User {
    private $user, $con;
    
    public function __construct($con, $user) {
        $this->con = $con;
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$user'");
        $this->user = mysqli_fetch_array($user_details_query);
    }

    public function getFirstAndLastName() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT firstName, lastName FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['firstName'] . " " . $row['lastName'];
    }

    public function getProfilePic() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT profilePic FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['profilePic'];
    }

    public function getFriendArray() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT friendArray FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['friendArray'];
    }

    public function getNumPosts() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT numPosts FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['numPosts'];
    }

    public function getUsername() {
        return $this->user['username'];
    }

    public function isClosed() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT userClosed FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        if ($row['userClosed'] == 'yes') {  
            return true;
        } 
        else {
            return false;
        }
    }

    public function isFriend($username_to_check) {
        $usernameComma = "," . $username_to_check . ",";

        if ((strstr($this->user['friendArray'], $usernameComma) || $username_to_check == $this->user['username'])) {
            return true;
        }
        else {
            return false;
        }
    }
    
    public function didReceiveRequest($user_from) {
        $user_to = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friendRequests WHERE userTo='$user_to' AND userFrom='$user_from'");
        if (mysqli_num_rows($check_request_query) > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function didSendRequest($user_to) {
        $user_from = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friendRequests WHERE userTo='$user_to' AND userFrom='$user_from'");
        if (mysqli_num_rows($check_request_query) > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function removeFriend($user_to_remove) {
        $logged_in_user = $this->user['username'];

        $query = mysqli_query($this->con, "SELECT friendArray FROM users WHERE username='$user_to_remove'");
        $row = mysqli_fetch_array($query);
        $friend_array_username = $row['friendArray'];

        $new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friendArray']);
        $remove_friend = mysqli_query($this->con, "UPDATE users SET friendArray='$new_friend_array' WHERE username='$logged_in_user'");

        $new_friend_array = str_replace($this->user['username'] . ",", "", $friend_array_username);
        $remove_friend = mysqli_query($this->con, "UPDATE users SET friendArray='$new_friend_array' WHERE username='$user_to_remove'");
    }

    public function addFriend($user_to) {
        $user_from = $this->user['username'];
        $query = mysqli_query($this->con, "INSERT INTO friendRequests VALUES('', '$user_to', '$user_from')");
    }

    public function getMutualFriends($user_to_check) {
        $mutualFriends = 0;
        $user_array = $this->user['friendArray'];
        $user_array_explode = explode(",", $user_array);

        $query = mysqli_query($this->con, "SELECT friendArray FROM users WHERE username='$user_to_check'");
        $row = mysqli_fetch_array($query);
        $user_to_check_array = $row['friendArray'];
        $user_to_check_array_explode = explode(",", $user_to_check_array);

        foreach ($user_array_explode as $i) {
            foreach ($user_to_check_array_explode as $j) {
                if ($i == $j && $i != "") {
                    $mutualFriends++;
                }
            }
        }
        return $mutualFriends;
    }
}


?>
