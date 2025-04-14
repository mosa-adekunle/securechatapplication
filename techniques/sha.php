<?php
/**
 * Left rotates (circular shift) a 32-bit number by a given number of bits.
 *
 * @param int $num The 32-bit number.
 * @param int $bits The number of bits to rotate.
 * @return int The rotated number.
 */
function leftRotate($num, $bits) {
    return (($num << $bits) | ($num >> (32 - $bits))) & 0xFFFFFFFF;
}

/**
 * Custom SHA-1 hash implementation.
 *
 * @param string $msg The input message to hash.
 * @return string The SHA-1 hash as a 40-character hexadecimal string.
 */
function sha1_custom($msg) {
    // Initialize variables:
    $h0 = 0x67452301;
    $h1 = 0xEFCDAB89;
    $h2 = 0x98BADCFE;
    $h3 = 0x10325476;
    $h4 = 0xC3D2E1F0;

    // Pre-processing:
    $msgLen = strlen($msg);
    $msgBitLen = $msgLen * 8; // Length in bits

    // Append the bit '1' to the message (0x80)
    $msg .= chr(0x80);

    // Pad with zeros until message length in bytes mod 64 is 56.
    while ((strlen($msg) % 64) != 56) {
        $msg .= chr(0x00);
    }

    // Append the original message length as a 64-bit big-endian integer.
    $msg .= pack("N2", ($msgBitLen >> 32) & 0xFFFFFFFF, $msgBitLen & 0xFFFFFFFF);

    // Process the message in successive 512-bit (64-byte) chunks.
    $chunks = str_split($msg, 64);
    foreach ($chunks as $chunk) {
        // Break chunk into sixteen 32-bit big-endian words.
        $words = [];
        for ($i = 0; $i < 16; $i++) {
            $words[$i] = unpack("N", substr($chunk, $i * 4, 4))[1];
        }
        // Extend the sixteen 32-bit words into eighty 32-bit words.
        for ($i = 16; $i < 80; $i++) {
            $words[$i] = leftRotate($words[$i - 3] ^ $words[$i - 8] ^ $words[$i - 14] ^ $words[$i - 16], 1);
        }

        // Initialize hash value for this chunk.
        $a = $h0;
        $b = $h1;
        $c = $h2;
        $d = $h3;
        $e = $h4;

        // Main loop:
        for ($i = 0; $i < 80; $i++) {
            if ($i >= 0 && $i <= 19) {
                $f = ($b & $c) | ((~$b) & $d);
                $k = 0x5A827999;
            } elseif ($i >= 20 && $i <= 39) {
                $f = $b ^ $c ^ $d;
                $k = 0x6ED9EBA1;
            } elseif ($i >= 40 && $i <= 59) {
                $f = ($b & $c) | ($b & $d) | ($c & $d);
                $k = 0x8F1BBCDC;
            } else { // $i >= 60 && $i <= 79
                $f = $b ^ $c ^ $d;
                $k = 0xCA62C1D6;
            }

            $temp = (leftRotate($a, 5) + $f + $e + $k + $words[$i]) & 0xFFFFFFFF;
            $e = $d;
            $d = $c;
            $c = leftRotate($b, 30);
            $b = $a;
            $a = $temp;
        }

        // Add this chunk's hash to result so far.
        $h0 = ($h0 + $a) & 0xFFFFFFFF;
        $h1 = ($h1 + $b) & 0xFFFFFFFF;
        $h2 = ($h2 + $c) & 0xFFFFFFFF;
        $h3 = ($h3 + $d) & 0xFFFFFFFF;
        $h4 = ($h4 + $e) & 0xFFFFFFFF;
    }

    // Produce the final hash value as a hexadecimal string.
    return sprintf("%08x%08x%08x%08x%08x", $h0, $h1, $h2, $h3, $h4);
}

/**
 * shaCipher function using the given structure.
 *
 * For SHA-based "encryption":
 *   - The action 'encrypt' computes the SHA-1 hash of the input text.
 *   - The key parameter is ignored.
 *
 * For verification:
 *   - The action 'verify' computes the SHA-1 hash of the input text and
 *     compares it to the expected hash provided in $key.
 *   - Returns true if the computed hash matches $key, false otherwise.
 *
 * @param string $action     The action to perform ('encrypt' or 'verify').
 * @param string $inputText  The input text to hash.
 * @param string $key        The expected hash to verify against (for action 'verify').
 * @return mixed             Returns the computed hash (string) for 'encrypt',
 *                           a boolean for 'verify', or an empty string on failure.
 */

function shaCipher($action, $inputText) {

    if ($action == 'encrypt') {
        $cipherText = sha1_custom($inputText);
        return $inputText . "::" . $cipherText;
    } elseif ($action == 'decrypt') {

        $inputTextArray = explode("::", $inputText);

        // Check the format.
        if (count($inputTextArray) < 2) {
            return "Invalid format";
        }

        $plaintext = $inputTextArray[0];
        $providedHash = $inputTextArray[1];

        // Compute hash on the plaintext part.
        $computedHash = sha1_custom($plaintext);

        if ($computedHash === $providedHash) {
            return $plaintext . "(". "Verified" . ")";
        } else {
            return "Not Verified";
        }
    }
    return "";
}

//
//// Example usage:
//$inputText = "The quick brown fox jumps over the lazy dog";
//
//// Encrypt: Compute the SHA-1 hash.
//$hash = shaCipher('encrypt', $inputText);
//echo "SHA-1 Hash: " . $hash . "\n";
//
//// Verify: Check if the computed hash matches an expected value.
//$isVerified = shaCipher('verify', $inputText, $hash);
//echo "Verification result: " . ($isVerified ? "Match" : "No Match") . "\n";
?>
