<?php
include("includes/handlers/config.php");

$fname = ""; //first name
$lname = ""; //last name
$em = ""; //emails
$em2 = "";
$password = "";
$password2 = "";
$date = ""; //sign up date
$error_array = array();

if (isset($_POST['register_button'])) {

    //first name
    $fname = strip_tags($_POST['reg_fname']); //remove html tags
    $fname = str_replace(' ', '', $fname); //remove empty spaces
    $fname = ucfirst(strtolower($fname));
    $_SESSION['reg_fname'] = $fname;

    //last name
    $lname = strip_tags($_POST['reg_lname']); //remove html tags
    $lname = str_replace(' ', '', $lname); //remove empty spaces
    $lname = ucfirst(strtolower($lname));
    $_SESSION['reg_lname'] = $lname;

    //email
    $em = strip_tags($_POST['reg_email']); //remove html tags
    $em = str_replace(' ', '', $em); //remove empty spaces
    $em = ucfirst(strtolower($em));
    $_SESSION['reg_email'] = $em;

    //email 2
    $em2 = strip_tags($_POST['reg_email2']); //remove html tags
    $em2 = str_replace(' ', '', $em2); //remove empty spaces
    $em2 = ucfirst(strtolower($em2));
    $_SESSION['reg_email2'] = $em2;

    //Password
    $password = strip_tags($_POST['reg_password']); //remove html tags
    $password2 = strip_tags($_POST['reg_password2']); //remove html tags

    $date = date("Y-m-d");

    if ($em == $em2) {
        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
            //Check if email is in valid format
            $em = filter_var($em, FILTER_VALIDATE_EMAIL);
            //check if email aready exists
            $e_check = mysqli_query($con, "SELECT email FROM users WHERE email ='$em'");
            //Count number of rows returned
            $num_rows = mysqli_num_rows($e_check);
            
            if ($num_rows > 0) {
                array_push($error_array, "Email already in use<br>");
            }
        }
        else {
            array_push($error_array, "Invalid email format<br>");
        }
    }
    else {
        array_push($error_array, "Emails don't match<br>");
    }

    if (strlen($fname) > 25 || strlen($fname) < 2) {
        array_push($error_array, "Your first name must be between 2 and 25 characters<br>");
    }

    if (strlen($lname) > 25 || strlen($lname) < 2) {
        array_push($error_array, "Your last name must be between 2 and 25 characters<br>");
    }

    if ($password != $password2) {
        array_push($error_array, "Your passwords do not match<br>");
    } 
    else {
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            array_push($error_array, "Your password can only contain characters or numbers<br>");
        }
    }

    if (strlen($password > 30 || strlen($password) < 5)) {
        array_push($error_array, "Your password must be between 5 and 30 characters<br>");
    }

    if (empty($error_array)) {
        $password = md5($password); //encrypt password before sending to db
        $username = strtolower($fname . "_" . $lname);
        $check_username_query = mysqli_query($con, "SELECT username from users WHERE username='$username'");

        $i = 0;
        //if username exists add number to username
        while (mysqli_num_rows($check_username_query) != 0) {
            $i++; //Add 1 to username if taken (e.g. if user takes username 'Bob', which is already in use, the username will automatically be Bob1, etc.)
            $username = $username . "_" . $i;
            $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
        }

        //Profile picture random assignment
        $rand = rand(1, 2); 
        
        if($rand == 1) 
        $profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
        else if ($rand == 2) 
        $profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";

        $query = mysqli_query($con, "INSERT INTO users VALUES ('', '$fname', '$lname', '$username', '$em', '$password', '$date', '$profile_pic', '0', '0', 'no', ',')");

        //push success message into array if successful insert into db
        array_push($error_array, "<span style='color: #14C800'>You're all set! Go ahead and login!</span><br>");

        //Clear session variables if successful insert into db
        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_email2'] = "";
    }
}

?>