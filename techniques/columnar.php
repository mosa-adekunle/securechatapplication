<?php


function columnarCipher($mode, $text, $key, $padChar = 'X') {
    $key = strtolower($key);
    $keyLength = strlen($key);

    //Create an ordered map of key positions
    $keyOrder = getKeyOrder($key);

    if ($mode === "encrypt") {
        // Remove spaces and pad the text
        $text = preg_replace('/\s+/', '', $text);
        $textLength = strlen($text);
        $numRows = ceil($textLength / $keyLength);
        $paddedLength = $numRows * $keyLength;
        $text = str_pad($text, $paddedLength, $padChar);


        // Fill the matrix row-wise
        $matrix = str_split($text, $keyLength);


        // Read columns in sorted key order
        $cipherText = '';
        foreach ($keyOrder as $colIndex) {
            foreach ($matrix as $row) {
                $cipherText .= $row[$colIndex];
            }
        }
        return $cipherText;


    } elseif ($mode === "decrypt") {
        $textLength = strlen($text);
        $numRows = $textLength / $keyLength;


        // Each column gets equal characters due to padding
        $columns = [];
        $start = 0;
        foreach ($keyOrder as $colIndex) {
            $columns[$colIndex] = substr($text, $start, $numRows);
            $start += $numRows;
        }


        // Reconstruct the original matrix row-wise
        $plainText = '';
        for ($i = 0; $i < $numRows; $i++) {
            for ($j = 0; $j < $keyLength; $j++) {
                $plainText .= $columns[$j][$i];
            }
        }
        return rtrim($plainText, $padChar); // Remove padding if needed
    }


    return "Invalid mode. Use 'encrypt' or 'decrypt'.";
}


function getKeyOrder($key) {
    $letters = str_split($key);
    $indexed = [];
    foreach ($letters as $i => $char) {
        $indexed[] = [$char, $i];
    }


    // Sort alphabetically, preserving original index for ties
    usort($indexed, function ($a, $b) {
        return $a[0] <=> $b[0];
    });


    $order = array_fill(0, strlen($key), 0);
    foreach ($indexed as $orderIndex => [$char, $originalIndex]) {
        $order[$orderIndex] = $originalIndex;
    }


    // Invert the order array to map key index to actual column index
    $keyOrder = [];
    foreach ($order as $sortedIndex => $originalIndex) {
        $keyOrder[$sortedIndex] = $originalIndex;
    }


    return $keyOrder;
}
