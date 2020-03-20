<?php
ob_start();

if(!isset($_SESSION))  {
    session_start(); 
}

$timezone = date_default_timezone_set("America/Toronto");

$con = mysqli_connect("localhost", "root", "", "Fakebook");

if (mysqli_connect_errno()) {
    echo "Error connecting to database" . mysqli_connect_errno();
}

?>