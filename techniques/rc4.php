<?php
function rc4Cipher($mode, $text, $key) {
    if ($mode === "encrypt") {
        $cipher = rc4($key, $text);
        return bin2hex($cipher); // Return hex representation
    } elseif ($mode === "decrypt") {
        $binary = hex2bin($text);
        if ($binary === false) {
            return "Invalid ciphertext: not a valid hex string.";
        }
        return rc4($key, $binary);
    } else {
        return "Invalid mode. Use 'encrypt' or 'decrypt'.";
    }
}


function rc4($key, $data) {
    $keyLength = strlen($key);
    $dataLength = strlen($data);

    // Key Scheduling Algorithm (KSA)
    $S = range(0, 255); //An array $S is initialized with the numbers 0 through 255.
    $j = 0; // loops through $s  using the key  to modify the order of 𝑆

    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $S[$i] + ord($key[$i % $keyLength])) % 256;
        [$S[$i], $S[$j]] = [$S[$j], $S[$i]]; // Swap
    }

    // Pseudo-Random Generation Algorithm (PRGA)
    // The PRGA portion resets 𝑖 i and 𝑗 j and then iterates for each byte of the input data,
    // performing a swap in the 𝑆 S array and generating a keystream byte from the updated S.
    // Each keystream byte is then XORed with the corresponding plaintext byte to produce the output byte.
    $i = $j = 0;
    $output = '';

    for ($n = 0; $n < $dataLength; $n++) {
        $i = ($i + 1) % 256;
        $j = ($j + $S[$i]) % 256;
        [$S[$i], $S[$j]] = [$S[$j], $S[$i]]; // Swap

        $K = $S[($S[$i] + $S[$j]) % 256];
        $output .= chr(ord($data[$n]) ^ $K);
    }

    return $output;
}
