<?php

//Vigenere cipher.
//Repeats key to match the PT word
//Shifts keys by distance of keyword from A
//Sample Key = CONCORDIA
function polyalphabeticCipher($action, $inputText, $key)
{
    //Vigenère Cipher
    $plainAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $cipherText = '';
    $inputText = strtoupper($inputText); // Normalize input text to uppercase
    $key = strtoupper($key); // Normalize key to uppercase
    $keyLength = strlen($key);
    $keyIndex = 0;

    foreach (str_split($inputText) as $char) {
        if (ctype_alpha($char)) {
            $shift = strpos($plainAlphabet, $key[$keyIndex % $keyLength]); // Get shift value from the key
            $index = strpos($plainAlphabet, $char);

            if ($action == 'encrypt') {
                $newIndex = ($index + $shift) % 26; // Apply shift forward
            } elseif ($action == 'decrypt') {
                $newIndex = ($index - $shift + 26) % 26; // Apply shift backward
            } else {
                return ""; // Invalid action
            }

            $cipherText .= $plainAlphabet[$newIndex];
            $keyIndex++; // Move to the next letter in the key
        } else {
            $cipherText .= $char; // Keep non-alphabetic characters unchanged
        }
    }

    return $cipherText;
}
