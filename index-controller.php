<?php
session_start();
//$username = $_SESSION['username'];

if(isset($_SESSION['username'])){
    $username = $_SESSION['username'];
    header("Location: send.php");
    exit;
}

