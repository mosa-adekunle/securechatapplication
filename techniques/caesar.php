<?php
function caesarCipher($action, $inputText, $key)
{
    $cipherText = '';
    $plainText = '';
    $key = (int)$key;
    $chars = str_split($inputText);

    if ($action == 'encrypt') {
        // Iterate over each character in the plaintext
        foreach ($chars as $i => $char) {
            if (ctype_alpha($char)) {
                // Handle uppercase letters
                if (ctype_upper($char)) {
                    $cipherText .= chr((ord($char) - ord('A') + $key) % 26 + ord('A'));
                } // Handle lowercase letters
                else {
                    $cipherText .= chr((ord($char) - ord('a') + $key) % 26 + ord('a'));
                }
            } else {
                $cipherText .= $char; // Non-alphabetic characters are added unchanged
            }
        }
        return $cipherText;
    }

    elseif ($action == 'decrypt') {
//        $plainText = ""; // Initialize output

        foreach (str_split($inputText) as $char) {
            if (ctype_upper($char)) { // Uppercase letters
                $plainText .= chr((ord($char) - ord('A') - $key + 26) % 26 + ord('A'));
            } elseif (ctype_lower($char)) { // Lowercase letters
                $plainText .= chr((ord($char) - ord('a') - $key + 26) % 26 + ord('a'));
            } else {
                $plainText .= $char; // Keep spaces, punctuation, and numbers unchanged
            }
        }

        return $plainText;
    }

return "";
}
