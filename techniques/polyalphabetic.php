<?php
function polyalphabeticCipher($action, $plaintext, $key) {
    $ciphertext = '';
    $keyIndex = 0;
    $key = str_repeat($key, ceil(strlen($plaintext) / strlen($key))); // Repeat the key

    for ($i = 0; $i < strlen($plaintext); $i++) {
        $char = $plaintext[$i];
        if (ctype_alpha($char)) {
            $keyChar = $key[$keyIndex];
            $keyIndex++;
            if (ctype_upper($char)) {
                $ciphertext .= chr((ord($char) - ord('A') + ord(strtoupper($keyChar)) - ord('A')) % 26 + ord('A'));
            } else {
                $ciphertext .= chr((ord($char) - ord('a') + ord(strtolower($keyChar)) - ord('a')) % 26 + ord('a'));
            }
        } else {
            $ciphertext .= $char; // Non-alphabetic characters are added unchanged
        }
    }
    return $ciphertext;
}
?>
