<?php
session_start();
include "includes/ensure-login.php";
$username = $_SESSION['username'];

$encryptedUsing = "";
$encryptedMessage = "";

