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

    public function getNumPosts() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT numPosts FROM users WHERE username='$username'");
        $row = mysqli_fetch_array($query);
        return $row['numPosts'];
    }

    public function getUsername() {
        return $this->user['username'];
    }
}


?>