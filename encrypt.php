<?php
session_start();

$action = $_POST['action'];
$technique = $_POST["technique"];
$plaintext = $_POST['plaintext'] ?? '';
$key = $_POST['key'] ?? '';
$action = $_POST['action'] ?? '';

$_SESSION['plaintext'] = $plaintext;
$_SESSION['technique'] = $technique;
$_SESSION['key'] = $key;

switch ($technique) {
    case 'caesar':
        include "techniques/caesar.php";
        $_SESSION["encrypted_message"] = caesarCipher($action, $plaintext, $key);
        break;

    case 'monoalphabetic':
        include "techniques/monoalphabetic.php";
        $_SESSION["encrypted_message"] = monoalphabeticCipher($action, $plaintext, $key);
        break;

    case 'polyalphabetic':
        include "techniques/polyalphabetic.php";
        $_SESSION["encrypted_message"] = polyalphabeticCipher($action, $plaintext, $key);
        break;

    case 'hill':
        include "techniques/hill.php";
        $_SESSION["encrypted_message"] = hillCipher($action, $plaintext, $key);
        break;

    case 'playfair':
        include "techniques/playfair.php";
        $_SESSION["encrypted_message"] = playfairCipher($action, $plaintext, $key);
        break;

    case 'otp':
        include "techniques/otp.php";
        $_SESSION["encrypted_message"] = otpCipher($action, $plaintext, $key);
        break;

    case 'rail-fence':
        include "techniques/railfence.php";
        $_SESSION["encrypted_message"] = railFenceCipher($action, $plaintext, $key);
        break;

    case 'columnar':
        include "techniques/columnar.php";
        $_SESSION["encrypted_message"] = columnarCipher($action, $plaintext, $key);
        break;

    case 'des':
        include "techniques/des.php";
        $_SESSION["encrypted_message"] = desCipher($action, $plaintext, $key);
        break;

    case 'aes':
        include "techniques/aes.php";
        $_SESSION["encrypted_message"] = aesCipher($action, $plaintext, $key);
        break;

    case 'rc4':
        include "techniques/rc4.php";
        $_SESSION["encrypted_message"] = rc4Cipher($action, $plaintext, $key);
        break;

    case 'rsa':
        include "techniques/rsa.php";
        $_SESSION["encrypted_message"] = rsaCipher($action, $plaintext, $key);
        break;

    case 'ecc':
        include "techniques/ecc.php";
        $_SESSION["encrypted_message"] = eccCipher($action, $plaintext, $key);
        break;

    default:
        echo "Invalid technique selected.";
        break;
}

//var_dump( $_SESSION["encrypted_message"]);die();
header("Location: send.php");
exit;
