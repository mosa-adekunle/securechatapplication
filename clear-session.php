<?php
session_start();
$_SESSION["technique"] = $_SESSION["plaintext"] = $_SESSION["encrypted_message"] = $_SESSION["key"] = "";

if (isset($_SERVER['HTTP_REFERER'])) {
    // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit(); // Make sure to call exit after header redirection
} else {
    // If no referer, redirect to a default page
    header('Location: default_page.php');
    exit();
}
