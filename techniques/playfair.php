<?php
function playfairCipher($action, $inputText, $key)
{
    $inputText = strtoupper(str_replace('J', 'I', preg_replace('/[^A-Z]/', '', strtoupper($inputText))));
    $key = strtoupper(str_replace('J', 'I', preg_replace('/[^A-Z]/', '', $key)));

    $matrix = generatePlayfairMatrix($key);

    echo "Playfair Matrix:\n";
    printMatrix($matrix);

    if ($action == 'encrypt') {
        return playfairEncrypt($inputText, $matrix);
    } elseif ($action == 'decrypt') {
        return playfairDecrypt($inputText, $matrix);
    }

    return "";
}

function generatePlayfairMatrix($key)
{
    $alphabet = "ABCDEFGHIKLMNOPQRSTUVWXYZ";
    $keyString = "";

    foreach (str_split($key . $alphabet) as $char) {
        if (strpos($keyString, $char) === false) {
            $keyString .= $char;
        }
    }

    return array_chunk(str_split($keyString), 5);
}

function printMatrix($matrix)
{
    foreach ($matrix as $row) {
        echo implode(" ", $row) . "\n";
    }
    echo "\n";
}

function findPosition($matrix, $letter)
{
    foreach ($matrix as $rowIndex => $row) {
        foreach ($row as $colIndex => $col) {
            if ($col == $letter) {
                return [$rowIndex, $colIndex];
            }
        }
    }
    return null;
}

function playfairEncrypt($text, $matrix)
{
    $textPairs = createPairs($text);
    $cipherText = "";

    echo "Encrypting Pairs:\n";

    foreach ($textPairs as [$first, $second]) {
        [$r1, $c1] = findPosition($matrix, $first);
        [$r2, $c2] = findPosition($matrix, $second);

        echo "$first$second -> ";

        if ($r1 == $r2) {
            $cipherText .= $matrix[$r1][($c1 + 1) % 5] . $matrix[$r2][($c2 + 1) % 5];
        } elseif ($c1 == $c2) {
            $cipherText .= $matrix[($r1 + 1) % 5][$c1] . $matrix[($r2 + 1) % 5][$c2];
        } else {
            $cipherText .= $matrix[$r1][$c2] . $matrix[$r2][$c1];
        }

        echo "$cipherText\n";
    }
//die();
    return $cipherText;
}

function playfairDecrypt($text, $matrix)
{
    $textPairs = createPairs($text);
    $plainText = "";

    echo "Decrypting Pairs:\n";

    foreach ($textPairs as [$first, $second]) {
        [$r1, $c1] = findPosition($matrix, $first);
        [$r2, $c2] = findPosition($matrix, $second);

        echo "$first$second -> ";

        if ($r1 == $r2) {
            $plainText .= $matrix[$r1][($c1 + 4) % 5] . $matrix[$r2][($c2 + 4) % 5];
        } elseif ($c1 == $c2) {
            $plainText .= $matrix[($r1 + 4) % 5][$c1] . $matrix[($r2 + 4) % 5][$c2];
        } else {
            $plainText .= $matrix[$r1][$c2] . $matrix[$r2][$c1];
        }

        echo "$plainText\n";
    }

    return $plainText;
}

function createPairs($text)
{
    $pairs = [];
    $i = 0;

    while ($i < strlen($text)) {
        $first = $text[$i];
        $second = ($i + 1 < strlen($text) && $text[$i] != $text[$i + 1]) ? $text[$i + 1] : 'X';
        $pairs[] = [$first, $second];
        $i += ($first == $second) ? 1 : 2;
    }

    return $pairs;
}
