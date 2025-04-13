<?php

##SampleKey aB1dF2gH3jKlMnOpQrStUvWxYzAbCdEfGhIjKlMn

/**
 * Performs One Time Pad encryption and decryption.
 * @param $action
 * @param $inputText
 * @param $key
 * @return string|void
 */
function otpCipher($action, $inputText, $key)
{
    if ($action == "encrypt") {
        if (strlen($key) < strlen($inputText)) {
            die("Error: Key must be at least as long a s the input text.");
        }
        $keyBits = convertTextToAscii($key);
        $inputTextBits = convertTextToAscii($inputText);
        return performXOR($inputTextBits, $keyBits);
    }
        $keyBits = convertTextToAscii($key);
        $inputTextBits = $inputText;
        return  generateTextString(performXOR($inputTextBits, $keyBits));
}

/**
 * Performs an XOR operation on 2 input bytes.
 * @param $inputTextBits
 * @param $keyBits
 * @return string
 */
function performXOR($inputTextBits, $keyBits): string
{
    $result = "";
    foreach (str_split($inputTextBits) as $index => $bit) {
        if($bit == $keyBits[$index]){
            $result .= 0;
        }
        if($bit != $keyBits[$index]){
            $result .= 1;
        }
    }
    return $result;
}

/**
 * @param $text
 * @return string
 */
function convertTextToAscii($text): string
{
    $binaryArray = array_map(fn($char) => str_pad(decbin(ord($char)), 8, "0", STR_PAD_LEFT), str_split($text));
    return implode("", $binaryArray);
}

/**
 * @param $binary
 * @return string
 */
function convertAsciiToText($binary): string
{
    $asciiArray = explode(" ", $binary);
    return implode(array_map(fn($bin) => chr(bindec($bin)), $asciiArray));
}

/**
 * @param $bits
 * @return string
 */
function generateTextString($bits): string
{
    $chunks = str_split($bits, 8);
    $reassembledText = "";
    foreach ($chunks as $chunk) {
        $reassembledText .= convertAsciiToText($chunk);
    }
    return $reassembledText;
}
