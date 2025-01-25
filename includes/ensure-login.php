<?php

if (!array_key_exists('username', $_SESSION)) {
    header("Location: login.php");
    exit;
}
