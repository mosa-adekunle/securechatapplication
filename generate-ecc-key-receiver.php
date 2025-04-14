
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$ecc_p = 67;               // Prime modulus
$ecc_a = 2;                // Curve parameter a
$ecc_b = 3;                // Curve parameter b
$ecc_G = [ 'x' => 2, 'y' => 22 ];  // Base point on the curve



// Computes the modular inverse of $a modulo $m (brute-force approach for small numbers).
function modInverse($a, $m) {
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return -1; // No inverse found.
}


// Computes scalar multiplication k * P using the double-and-add algorithm.
function eccScalarMult($P, $k, $p, $a) {
    $R = null; // Represents the point at infinity.
    $Q = $P;
    while ($k > 0) {
        if ($k & 1) {
            $R = eccPointAdd($R, $Q, $p, $a);
        }
        $Q = eccPointDouble($Q, $p, $a);
        $k = $k >> 1;
    }
    return $R;
}

// Doubles a point on the elliptic curve.
function eccPointDouble($P, $p, $a) {
    if ($P === null) return null;
    // Slope: (3*x^2 + a) / (2*y) mod p
    $num = (3 * $P['x'] * $P['x'] + $a) % $p;
    $denom = (2 * $P['y']) % $p;
    $inv = modInverse($denom, $p);
    $lambda = ($num * $inv) % $p;

    $xR = ($lambda * $lambda - 2 * $P['x']) % $p;
    $yR = ($lambda * ($P['x'] - $xR) - $P['y']) % $p;
    return [ 'x' => ($xR + $p) % $p, 'y' => ($yR + $p) % $p ];
}

// Adds two points on the elliptic curve over GF(p).
// Points P and Q are associative arrays with keys 'x' and 'y'. Null represents the point at infinity.
function eccPointAdd($P, $Q, $p, $a) {
    if ($P === null) return $Q;
    if ($Q === null) return $P;

    // If points are inverses, return the point at infinity.
    if ($P['x'] == $Q['x'] && (($P['y'] + $Q['y']) % $p == 0))
        return null;

    if ($P['x'] == $Q['x'] && $P['y'] == $Q['y']) {
        return eccPointDouble($P, $p, $a);
    } else {
        // Slope: (y2 - y1) / (x2 - x1) mod p
        $num = ($Q['y'] - $P['y'] + $p) % $p;
        $denom = ($Q['x'] - $P['x'] + $p) % $p;
        $inv = modInverse($denom, $p);
        $lambda = ($num * $inv) % $p;
    }
    $xR = ($lambda * $lambda - $P['x'] - $Q['x']) % $p;
    $yR = ($lambda * ($P['x'] - $xR) - $P['y']) % $p;
    return [ 'x' => ($xR + $p) % $p, 'y' => ($yR + $p) % $p ];
}



// --- ECC Key Generation ---

// Generates ECC key pairs using the domain parameters defined above.
// Returns keys as:
//   public: "Qx,Qy"
//   private: private key d (as a string)
function eccGenerateKeys($max_private_key_val) {
    global $ecc_p, $ecc_a, $ecc_G;
    $d = rand(1, $max_private_key_val);
    $Q = eccScalarMult($ecc_G, $d, $ecc_p, $ecc_a);
//
//    var_dump($Q);
//    die("here");


    return [
        'public' => $Q['x'] . "," . $Q['y'],
        'private' => $d . ""
    ];
}
$keys = eccGenerateKeys($_POST['ecc_max']);


$_SESSION["ecc_receiver_public_key"] = $keys["public"];
$_SESSION["ecc_receiver_private_key"] = $keys["private"];


//var_dump($_SESSION["ecc_receiver_public_key"], $_SESSION["ecc_receiver_private_key"] );
//die("here");

header("Location: receive.php");
exit;
