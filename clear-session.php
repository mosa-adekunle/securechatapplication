<?php
session_start();


// Store the username if it exists
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Clear all session variables
$_SESSION = array();

// Restore the username if it was set
if ($username !== null) {
    $_SESSION['username'] = $username;
}


if (isset($_SERVER['HTTP_REFERER'])) {
    // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit(); // Make sure to call exit after header redirection
} else {
    // If no referer, redirect to a default page
    header('Location: default_page.php');
    exit();
}
