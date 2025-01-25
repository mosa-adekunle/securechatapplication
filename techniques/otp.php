<?php
function otpCipher($action, $plaintext, $key) {
    $ciphertext = '';
    for ($i = 0; $i < strlen($plaintext); $i++) {
        $char = $plaintext[$i];
        $keyChar = $key[$i % strlen($key)];  // One-time pad logic
        if (ctype_alpha($char)) {
            $ciphertext .= chr(((ord($char) - ord('A') + ord($keyChar) - ord('A')) % 26) + ord('A'));
        } else {
            $ciphertext .= $char; // Non-alphabetic characters remain unchanged
        }
    }
    return $ciphertext;
}
?>
