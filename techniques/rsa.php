<?php
// Greatest Common Divisor using recursion
function gcd($a, $b) {
    return ($b == 0) ? $a : gcd($b, $a % $b);
}

// Computes the modular inverse of $a modulo $m
function modInverse($a, $m) {
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return -1; // No inverse found
}

// Generate RSA keys using two small primes
// Returns keys as comma separated strings.
function rsaGenerateKeys() {
    $p = 61;         // First prime
    $q = 53;         // Second prime
    $n = $p * $q;    // Modulus
    $phi = ($p - 1) * ($q - 1);  // Euler's Totient function

    $e = 17;  // Public exponent (a common starting value)
    while (gcd($e, $phi) != 1) {
        $e++;
    }

    $d = modInverse($e, $phi); // Private exponent

    // Return keys as comma-separated strings: "e,n" for public, "d,n" for private
    return [
        'public' => $e . "," . $n,
        'private' => $d . "," . $n
    ];
}

// RSA cipher function that works similar to desCipher.
// Accepts an action ("encrypt" or "decrypt"), the input text, and a key provided as a comma-separated string "exp,n".
function rsaCipher($action, $plaintext, $key) {

    // Split the key string by comma and validate its format.
    $keyParts = explode(',', $key);
    if (count($keyParts) != 2) {
        return "Invalid key format. Please use 'exp,n'.";
    }

    $exp = (int) trim($keyParts[0]);
    $n = (int) trim($keyParts[1]);

    if ($action === 'encrypt') {
        $cipherArray = [];

        foreach (str_split($plaintext) as $char) {
            $m = ord($char);
            // Compute c = m^exp mod n using bcpowmod() for large number arithmetic.
            $c = bcpowmod($m, $exp, $n);
            $cipherArray[] = $c;
        }

        return implode(' ', $cipherArray);
    } elseif ($action === 'decrypt') {

        $output = "";
        $tokens = explode(' ', $plaintext);
        foreach ($tokens as $token) {
            if (trim($token) === "") {
                continue;
            }
            // Compute m = c^exp mod n
            $m = bcpowmod($token, $exp, $n);
            $output .= chr($m);
        }
        return $output;
    }
    return "";
}
