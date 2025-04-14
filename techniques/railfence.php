<?php
#ZipZag pattern
# Key is number of rails

function railFenceCipher($action, $text, $key)
{
    if (!is_numeric($key)) {
        die("Error: Key must be an integer.");
    }

    $rails = (int)$key;

    if ($action == "encrypt") {
        return encryptRailFence($text, $rails);
    } elseif ($action == "decrypt") {
        return decryptRailFence($text, $rails);
    } else {
        die("Error: Invalid action. Use 'encrypt' or 'decrypt'.");
    }
}

function encryptRailFence($text, $rails)
{
    $fence = array_fill(0, $rails, []);
    $rail = 0;
    $direction = 1;

    echo "<br>";

    var_dump($direction);
    echo "<br>";

    foreach (str_split($text) as $char) {
        $fence[$rail][] = $char;

        if ($rail == 0) {
            $direction = 1;
        } elseif ($rail == $rails - 1) {
            $direction = -1;
        }

        $rail += $direction;
    }
    $ciphertext = "";
    foreach ($fence as $railLine) {
        $ciphertext .= implode("", $railLine);
    }
    return $ciphertext;
}

function decryptRailFence($ciphertext, $rails)
{
    $fence = array_fill(0, $rails, array_fill(0, strlen($ciphertext), null));
    $rail = 0;
    $direction = 1;

    for ($i = 0; $i < strlen($ciphertext); $i++) {
        $fence[$rail][$i] = '*';

        if ($rail == 0) {
            $direction = 1;
        } elseif ($rail == $rails - 1) {
            $direction = -1;
        }

        $rail += $direction;
    }

    $index = 0;
    for ($r = 0; $r < $rails; $r++) {
        for ($c = 0; $c < strlen($ciphertext); $c++) {
            if ($fence[$r][$c] === '*' && $index < strlen($ciphertext)) {
                $fence[$r][$c] = $ciphertext[$index++];
            }
        }
    }

    $plaintext = "";
    $rail = 0;
    $direction = 1;

    for ($i = 0; $i < strlen($ciphertext); $i++) {
        $plaintext .= $fence[$rail][$i];

        if ($rail == 0) {
            $direction = 1;
        } elseif ($rail == $rails - 1) {
            $direction = -1;
        }

        $rail += $direction;
    }
    return $plaintext;
}
