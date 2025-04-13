<?php
session_start();
$variableToSave = $_POST["variable"];
$variableNameToSave = $_POST["variable_name"];
$_SESSION[$variableNameToSave] = $variableToSave;
echo $variableNameToSave . " saved.";
