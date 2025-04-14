<?php
//Maps each key to is corresponding albertic position. Key must be 26 letters long.
//sample key: QWERTYUIOPASDFGHJKLZXCVBNM
function monoalphabeticCipher($action, $inputText, $key)
{
    $plainAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $cipherText = '';
    $inputText = strtoupper($inputText); // Normalize case

    if ($action == 'encrypt') {
        foreach (str_split($inputText) as $char) {
            if (ctype_alpha($char)) {
                $index = strpos($plainAlphabet, $char);
                // Substitute with the corresponding letter in the provided substitution alphabet
                $cipherText .= $key[$index];
            } else {
                $cipherText .= $char; // Keep non-alphabetic characters unchanged
            }
        }
        return $cipherText;
    }

    elseif ($action == 'decrypt') {
        foreach (str_split($inputText) as $char) {
            if (ctype_alpha($char)) {
                // Find the index of the letter in the substitution alphabet
                $index = strpos($key, $char);
                // Replace with the corresponding letter from the plain alphabet
                $cipherText .= $plainAlphabet[$index];
            } else {
                $cipherText .= $char; // Keep non-alphabetic characters unchanged
            }
        }
        return $cipherText;
    }

    return "";
}
