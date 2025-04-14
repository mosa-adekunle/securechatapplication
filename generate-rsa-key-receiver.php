
//    $p = 61;  // Prime 1
//    $q = 53;  // Prime 2
//    $e = 17;  // Public exponent
<?php
session_start();
function rsaGenerateKeys($p, $q) {

    $n = $p * $q; // Modulus
    $phi = ($p - 1) * ($q - 1);
    $e = generateE($p, $q);
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

/**
 * Generate a valid public exponent e for RSA given two primes p and q.
 * The function computes φ(n) = (p-1)*(q-1) and iterates through odd numbers
 * (starting at 3) until it finds the first e for which gcd(e, φ(n)) == 1.
 *
 * @param int $p First prime number.
 * @param int $q Second prime number.
 * @return int|false A valid e or false if none found.
 */
function generateE($p, $q) {
    $phi = ($p - 1) * ($q - 1);

    for ($e = 3; $e < $phi; $e += 2) {
        if (gcd($e, $phi) == 1) {
            return $e;
        }
    }

    // If no valid e is found (which is unusual for proper RSA primes), return false.
    return false;
}




$keys = rsaGenerateKeys((int)$_POST["rsa_p"], (int)$_POST["rsa_q"]);

$_SESSION["receiver_public_key"] = $keys["public"];
$_SESSION["receiver_private_key"] = $keys["private"];


var_dump($keys, $_SESSION);
//die();
header("Location: receive.php");
exit;
