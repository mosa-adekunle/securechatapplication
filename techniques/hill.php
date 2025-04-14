<?php
//Only encrypts letters
//KEy is format 1,2,3,4
//Sample key: 3,3,2,5
// Matrix must be invertible mod 26 why?
/// Decryption requires multiplying by the inverse of the key matrix modulo 26.

function parseKeyMatrix($keyString, $size)
{
    $values = explode(',', $keyString);
    if (count($values) !== $size * $size) {
        return false; // Invalid matrix size
    }

    $matrix = [];
    for ($i = 0; $i < $size; $i++) {
        $matrix[] = array_map('intval', array_slice($values, $i * $size, $size));
    }
    return $matrix;
}

function modInverse($a, $m)
{
    $a = $a % $m;
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return -1; // No modular inverse found
}

function matrixMultiplyMod($matrix, $vector, $mod)
{
    $size = count($matrix);
    $result = array_fill(0, $size, 0);

    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            $result[$i] += $matrix[$i][$j] * $vector[$j];
        }
        $result[$i] %= $mod;
    }
    return $result;
}

function hillCipher($action, $inputText, $keyString)
{
    $plainAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $mod = 26;
    $size = 2; // 2x2 matrix

    // Clean and normalize input
    $inputText = strtoupper(preg_replace('/[^a-zA-Z]/', '', $inputText));// remove non-letters
    while (strlen($inputText) % $size !== 0) {
        $inputText .= 'X'; // pad with 'X'
    }

    $keyMatrix = parseKeyMatrix($keyString, $size);
    if (!$keyMatrix) {
        return "Error: Invalid key matrix format.";
    }

    // Handle decryption: compute inverse matrix mod 26
    if ($action === 'decrypt') {
        $det = ($keyMatrix[0][0] * $keyMatrix[1][1] - $keyMatrix[0][1] * $keyMatrix[1][0]) % $mod;
        if ($det < 0) $det += $mod;

        $detInv = modInverse($det, $mod);
        if ($detInv === -1) {
            return "Error: Matrix is not invertible.";
        }

        // Compute adjugate and multiply by detInv mod 26
        $keyMatrix = [
            [($keyMatrix[1][1] * $detInv) % $mod, (-$keyMatrix[0][1] * $detInv) % $mod],
            [(-$keyMatrix[1][0] * $detInv) % $mod, ($keyMatrix[0][0] * $detInv) % $mod]
        ];

        // Normalize negative numbers
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if ($keyMatrix[$i][$j] < 0) {
                    $keyMatrix[$i][$j] += $mod;
                }
            }
        }
    }

    // Encrypt or decrypt
    $outputText = '';
    for ($i = 0; $i < strlen($inputText); $i += $size) {
        $block = [];
        for ($j = 0; $j < $size; $j++) {
            $block[] = strpos($plainAlphabet, $inputText[$i + $j]);
        }

        $result = matrixMultiplyMod($keyMatrix, $block, $mod);
        foreach ($result as $num) {
            $outputText .= $plainAlphabet[$num];
        }
    }

    return $outputText;
}
