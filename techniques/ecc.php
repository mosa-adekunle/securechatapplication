<?php
//ECC Domain Parameters-
$ecc_p = 67;               // Prime modulus
$ecc_a = 2;                // Curve parameter a
$ecc_b = 3;                // Curve parameter b
$ecc_G = [ 'x' => 2, 'y' => 22 ];  // Base point on the curve

// Adds two points on the elliptic curve over GF(p).
// Points P and Q are associative arrays with keys 'x' and 'y'. Null represents the point at infinity.
function eccPointAdd($P, $Q, $p, $a) {
    if ($P === null) return $Q;
    if ($Q === null) return $P;

    // If points are inverses, return the point at infinity.
    if ($P['x'] == $Q['x'] && (($P['y'] + $Q['y']) % $p == 0))
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

// Greatest Common Divisor (same as RSA code)
function gcd($a, $b) {
    return ($b == 0) ? $a : gcd($b, $a % $b);
}

// Computes the modular inverse of $a modulo $m (brute-force approach for small numbers).
function modInverse($a, $m) {
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return -1; // No inverse found.
}

// --- ECC Key Generation ---

// Generates ECC key pairs using the domain parameters defined above.
// Returns keys as:
//   public: "Qx,Qy"
//   private: private key d (as a string)
function eccGenerateKeys() {
    global $ecc_p, $ecc_a, $ecc_G;
    // For demo, choose a small random private key in [1, 20]
    $d = rand(1, 20);
    $Q = eccScalarMult($ecc_G, $d, $ecc_p, $ecc_a);
    return [
        'public' => $Q['x'] . "," . $Q['y'],
        'private' => $d . ""
    ];
}

// --- Simple XOR Cipher for Symmetric Encryption ---
//
// This function XORs each character of $text with a one-byte key.
function xorCipher($text, $keyByte) {
    $result = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $result .= chr(ord($text[$i]) ^ $keyByte);
    }
    return $result;
}

// --- ECC Cipher Function ---
//
// Similar in structure to your RSA cipher function.
// For encryption ($action == "encrypt"):
//   - The $key parameter is the recipient's public key in the format "Qx,Qy".
//   - A random ephemeral scalar k is chosen and R = k * G is computed.
//   - The shared secret S = k * recipientPublic is computed.
//   - A symmetric key is derived from S['x'] mod 256.
//   - The plaintext is XOR-encrypted with this key.
//   - The function returns a string in the format "R_x,R_y|ciphertext".
// For decryption ($action == "decrypt"):
//   - The $plaintext parameter should be in the format "R_x,R_y|ciphertext".
//   - The $key parameter is the recipient's private key.
//   - R is parsed and the shared secret S' = d * R is computed.
//   - The symmetric key is derived and used to XOR-decrypt the ciphertext.
function eccCipher($action, $plaintext, $key) {
    global $ecc_p, $ecc_a, $ecc_G;

    if ($action === 'encrypt') {
        // Expect $key in format "Qx,Qy"
        $keyParts = explode(',', $key);
        if (count($keyParts) != 2) {
            return "Invalid key format. Expected 'Qx,Qy'.";
        }
        $recipientQ = [
            'x' => (int)trim($keyParts[0]),
            'y' => (int)trim($keyParts[1])
        ];
        // Choose random ephemeral scalar k.
        $k = rand(1, 20);
        // Compute ephemeral public key R = k * G.
        $R = eccScalarMult($ecc_G, $k, $ecc_p, $ecc_a);
        // Compute shared secret S = k * recipientPublic.
        $S = eccScalarMult($recipientQ, $k, $ecc_p, $ecc_a);
        // Derive one-byte symmetric key.
        $symmetricKey = $S['x'] % 256;
        // Encrypt plaintext using XOR cipher.
        $encrypted = xorCipher($plaintext, $symmetricKey);
        // Encode ciphertext in base64 for safe transmission.
        $encodedCiphertext = base64_encode($encrypted);
        // Return ephemeral public key and ciphertext in format "R_x,R_y|ciphertext".
        return $R['x'] . "," . $R['y'] . "|" . $encodedCiphertext;
    } elseif ($action === 'decrypt') {
        // $plaintext is expected in format "R_x,R_y|ciphertext"
        $parts = explode("|", $plaintext);
        if (count($parts) != 2) {
            return "Invalid ciphertext format.";
        }
        // Extract R from first part.
        $Rparts = explode(",", $parts[0]);
        if (count($Rparts) != 2) {
            return "Invalid ephemeral key format.";
        }
        $R = [
            'x' => (int)trim($Rparts[0]),
            'y' => (int)trim($Rparts[1])
        ];
        // Decode ciphertext.
        $ciphertext = base64_decode($parts[1]);
        // $key is the recipient's private key (as a string).
        $d = (int)trim($key);
        // Compute shared secret S' = d * R.
        $S = eccScalarMult($R, $d, $ecc_p, $ecc_a);
        $symmetricKey = $S['x'] % 256;
        // Decrypt the ciphertext.
        $decrypted = xorCipher($ciphertext, $symmetricKey);
        return $decrypted;
    }
    return "";
}

// --- Example Usage ---

// Generate ECC keys.
$keys = eccGenerateKeys();
$recipientPublic = $keys['public'];
$recipientPrivate = $keys['private'];

echo "Recipient Public Key: " . $recipientPublic . "\n";
echo "Recipient Private Key: " . $recipientPrivate . "\n";

$message = "Hello ECC!";

// Encrypt the message using the recipient's public key.
$encrypted = eccCipher("encrypt", $message, $recipientPublic);
echo "Encrypted (format: R_x,R_y|ciphertext): " . $encrypted . "\n";

// Decrypt the message using the recipient's private key.
$decrypted = eccCipher("decrypt", $encrypted, $recipientPrivate);
echo "Decrypted Message: " . $decrypted . "\n";
?>
