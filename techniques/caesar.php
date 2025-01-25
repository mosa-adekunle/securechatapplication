<?php
function caesarCipher($action, $plaintext, $key) {

//    var_dump($action, $plaintext, $key);
//    die();
    $ciphertext = '';
    $key = (int)$key; // Ensure the key is an integer

    // Iterate over each character in the plaintext
    for ($i = 0; $i < strlen($plaintext); $i++) {
        $char = $plaintext[$i];

        if (ctype_alpha($char)) {
            // Handle uppercase letters
            if (ctype_upper($char)) {
                $ciphertext .= chr((ord($char) - ord('A') + $key) % 26 + ord('A'));
            }
            // Handle lowercase letters
            else {
                $ciphertext .= chr((ord($char) - ord('a') + $key) % 26 + ord('a'));
            }
        } else {
            $ciphertext .= $char; // Non-alphabetic characters are added unchanged
        }
    }
//
//    var_dump($action, $plaintext, $key, $ciphertext);
//    die();
    return $ciphertext;
}
