<?php
session_start();

if(isset($_POST["username"])){
    $_SESSION['username']= $_POST["username"];
}

if(isset($_SESSION['username'])){
    $username = $_SESSION['username'];
    $_SESSION["technique"] = $_SESSION["plaintext"] = $_SESSION["encrypted_message"] = $_SESSION["key"] = "";
    header("Location: send.php");
    exit;
}

else{
    header("Location: index.php?msg=login-failed");
    exit;
}

//var_dump($_SESSION);
