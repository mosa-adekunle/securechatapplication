<?php
session_start();
function rsaGenerateKeys($p, $q, $e) {
//    $p = 61;  // Prime 1
//    $q = 53;  // Prime 2
    $n = $p * $q; // Modulus
    $phi = ($p - 1) * ($q - 1);
//    $e = 17;  // Public exponent

    while (gcd($e, $phi) != 1) {
        $e++;
    }

    $d = modInverse($e, $phi); // Private exponent

    return [
        'public' => ['e' => $e, 'n' => $n],
        'private' => ['d' => $d, 'n' => $n]
    ];
}

// RSA Functions
function gcd($a, $b) {
    return ($b == 0) ? $a : gcd($b, $a % $b);
}

function modInverse($a, $m) {
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return -1;
}



$keys = rsaGenerateKeys($_POST["rsa_p"], $_POST["rsa_q"], $_POST["rsa_e"]);

$_SESSION["receiver_public_key"] = $keys["public"];
$_SESSION["receiver_private_key"] = $keys["private"];


var_dump($keys, $_SESSION);
//die();
header("Location: receive.php");
exit;
