<?php
session_start();

$action = $_POST['action'];
$technique = $_POST["technique"];
$cipherText = $_POST['ciphertext'] ?? '';
$key = $_POST['key'] ?? '';
$action = $_POST['action'] ?? '';

$_SESSION['plaintext'] = $cipherText;
$_SESSION['technique'] = $technique;
$_SESSION['key'] = $key;

switch ($technique) {
    case 'caesar':
        include "techniques/caesar.php";
        $_SESSION["decrypted_message"] = caesarCipher($action, $cipherText, $key);
        break;

    case 'monoalphabetic':
        include "techniques/monoalphabetic.php";
        $_SESSION["decrypted_message"] = monoalphabeticCipher($action, $cipherText, $key);
        break;

    case 'polyalphabetic':
        include "techniques/polyalphabetic.php";
        $_SESSION["decrypted_message"] = polyalphabeticCipher($action, $cipherText, $key);
        break;

    case 'hill':
        include "techniques/hill.php";
        $_SESSION["decrypted_message"] = hillCipher($action, $cipherText, $key);
        break;

    case 'playfair':
        include "techniques/playfair.php";
        $_SESSION["decrypted_message"] = playfairCipher($action, $cipherText, $key);
        break;

    case 'otp':
        include "techniques/otp.php";
        $_SESSION["decrypted_message"] = otpCipher($action, $cipherText, $key);
        break;

    case 'rail-fence':
        include "techniques/railfence.php";
        $_SESSION["decrypted_message"] = railFenceCipher($action, $cipherText, $key);
        break;

    case 'columnar':
        include "techniques/columnar.php";
        $_SESSION["decrypted_message"] = columnarCipher($action, $cipherText, $key);
        break;

    case 'des':
        include "techniques/des.php";
        $_SESSION["decrypted_message"] = desCipher($action, $cipherText, $key);
        break;

    case 'aes':
        include "techniques/aes.php";
        $_SESSION["decrypted_message"] = aesCipher($action, $cipherText, $key);
        break;

    case 'rc4':
        include "techniques/rc4.php";
        $_SESSION["decrypted_message"] = rc4Cipher($action, $cipherText, $key);
        break;

    case 'rsa':
        include "techniques/rsa.php";
        $_SESSION["decrypted_message"] = rsaCipher($action, $cipherText, $key);
        break;

    case 'ecc':
        include "techniques/ecc.php";
        $_SESSION["decrypted_message"] = eccCipher($action, $cipherText, $key);
        break;

    case 'sha':
        include "techniques/sha.php";
        $_SESSION["decrypted_message"] = shaCipher($action, $cipherText);
        break;

    default:
        echo "Invalid technique selected.";
        break;
}

header("Location: receive.php");
exit;
