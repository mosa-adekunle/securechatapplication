<?php
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

    // Parse key matrix from string
    $size = 2; // Default to 2x2 matrix (extendable)
    $keyMatrix = parseKeyMatrix($keyString, $size);
    if (!$keyMatrix) {
        return "Error: Invalid key matrix format.";
    }

    // Prepare text (uppercase, remove spaces, ensure even length)
    $inputText = strtoupper(preg_replace('/[^A-Z]/', '', $inputText)); // Remove non-alphabetic characters
    $outputText = '';

    // Ensure input length is even
    while (strlen($inputText) % $size != 0) {
        $inputText .= 'X'; // Padding
    }

    // Encryption and Decryption
    if ($action == 'decrypt') {
        // Compute determinant and modular inverse
        $det = ($keyMatrix[0][0] * $keyMatrix[1][1] - $keyMatrix[0][1] * $keyMatrix[1][0]) % $mod;
        if ($det < 0) {
            $det += $mod;
        }
        $detInv = modInverse($det, $mod);
        if ($detInv == -1) {
            return "Error: Matrix is not invertible.";
        }

        // Compute inverse matrix mod 26
        $keyMatrix = [
            [($keyMatrix[1][1] * $detInv) % $mod, (-$keyMatrix[0][1] * $detInv) % $mod],
            [(-$keyMatrix[1][0] * $detInv) % $mod, ($keyMatrix[0][0] * $detInv) % $mod]
        ];

        // Ensure positive values
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                if ($keyMatrix[$i][$j] < 0) {
                    $keyMatrix[$i][$j] += $mod;
                }
            }
        }
    }

    // Process text in blocks
    $inputIndex = 0;
    for ($i = 0; $i < strlen($inputText); $i += $size) {
        if (!ctype_alpha($inputText[$i])) {
            $outputText .= $inputText[$i]; // Add spaces and non-alphabet characters as is
            continue;
        }

        // Convert text into numbers (A = 0, B = 1, ..., Z = 25)
        $pair = [];
        for ($j = 0; $j < $size; $j++) {
            $pair[] = strpos($plainAlphabet, $inputText[$inputIndex]);
            $inputIndex++;
        }

        // Apply matrix transformation
        $result = matrixMultiplyMod($keyMatrix, $pair, $mod);

        // Convert numbers back to letters
        for ($j = 0; $j < $size; $j++) {
            $outputText .= $plainAlphabet[$result[$j]];
        }
    }

    return $outputText;
}
