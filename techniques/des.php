<?php

/**
 * DES encryption & decryption in pure PHP (ECB mode, 16 rounds).
 *
 *  - Use 'encrypt' to get a hex string.
 *  - Use 'decrypt' with that hex string to get original plaintext.
 *  - Key must be exactly 8 bytes (64 bits).
 *  - Plaintext is zero-padded to multiples of 8 bytes.
 *  - For real-world use, consider AES or 3DES instead.
 */

function desCipher($action, $plaintext, $key) {
    // Key must be exactly 8 bytes
    if (strlen($key) !== 8) {
        throw new Exception("DES key must be exactly 8 bytes.");
    }

    if ($action === 'encrypt') {
        // 1) Zero-pad plaintext to 8-byte multiples
        while (strlen($plaintext) % 8 !== 0) {
            $plaintext .= "\0";
        }

        // 2) Process each block
        $blocks = str_split($plaintext, 8);
        $result = '';

        // Generate subkeys (48 bits each)
        $roundKeys = generateRoundKeys(stringToBits($key));

        // Encrypt each 8-byte block
        foreach ($blocks as $block) {
            $cipherBlock = desBlockEncrypt($block, $roundKeys);
            $result .= $cipherBlock; // raw binary
        }

        // Return hex for portability
        return bin2hex($result);

    } elseif ($action === 'decrypt') {
        // 1) Convert hex ciphertext to raw binary
        $rawCipher = hex2bin($plaintext);
        if ($rawCipher === false) {
            throw new Exception("Invalid hex string for ciphertext.");
        }

        // 2) Process each 8-byte block
        $blocks = str_split($rawCipher, 8);
        $result = '';

        // Generate subkeys in normal order, then reverse for decryption
        $roundKeys = generateRoundKeys(stringToBits($key));
        $roundKeys = array_reverse($roundKeys);

        // Decrypt each block
        foreach ($blocks as $block) {
            $plainBlock = desBlockEncrypt($block, $roundKeys);
            $result    .= $plainBlock; // raw binary
        }

        // Strip null padding
        return rtrim($result, "\0");
    }

    throw new Exception("Invalid action: use 'encrypt' or 'decrypt'.");
}

/**
 * Encrypt/Decrypt a single 8-byte block with DES subkeys (48-bit).
 * If subkeys are reversed, this effectively decrypts.
 */
function desBlockEncrypt($block8, $roundKeys) {
    // Convert block to bits
    $bits64 = stringToBits($block8); // 64 bits

    // Initial Permutation
    $bits64 = permute($bits64, IP);

    // Split into L and R (32 bits each)
    [$L, $R] = array_chunk($bits64, 32);

    // 16 rounds
    foreach ($roundKeys as $subKey48) {
        $temp = $R;
        $R    = xorBits($L, feistel($R, $subKey48));
        $L    = $temp;
    }

    // Final swap, then Final Permutation
    $preOutput = array_merge($R, $L);
    $outBits   = permute($preOutput, FP);

    // Convert 64 bits -> 8 bytes
    return bitsToString($outBits);
}

/**
 * Feistel function: R -> E expansion -> XOR subKey -> S-boxes -> P permutation
 */
function feistel($R32, $subKey48) {
    // Expand 32 -> 48
    $expanded = permute($R32, E);

    // XOR with subkey
    $xored = xorBits($expanded, $subKey48);

    // S-box => 32 bits
    $sboxOut = [];
    for ($i = 0; $i < 8; $i++) {
        // each chunk is 6 bits
        $chunk6 = array_slice($xored, $i * 6, 6);
        $sboxOut = array_merge($sboxOut, sBoxSubstitution($chunk6, $i));
    }

    // P-permutation => 32 bits
    return permute($sboxOut, P);
}

/**
 * S-box substitution: from 6 bits -> 4 bits
 */
function sBoxSubstitution($bits6, $sboxIndex) {
    $row = ($bits6[0] << 1) | $bits6[5];
    $col = ($bits6[1] << 3) | ($bits6[2] << 2) | ($bits6[3] << 1) | $bits6[4];

    $val = SBOXES[$sboxIndex][$row][$col]; // 0..15

    // Convert to 4 bits
    $bin = str_pad(decbin($val), 4, '0', STR_PAD_LEFT);
    return array_map('intval', str_split($bin));
}

/**
 * Generate 16 subkeys (each 48 bits) from the original 64-bit key
 */
function generateRoundKeys($key64) {
    // PC1 => 56 bits
    $key56 = permute($key64, PC1);

    // Split into C, D
    [$C, $D] = array_chunk($key56, 28);

    $roundKeys = [];
    // 16 rounds
    foreach (SHIFT_SCHEDULE as $shift) {
        $C = leftRotate($C, $shift);
        $D = leftRotate($D, $shift);

        // PC2 => 48 bits
        $subKey48 = permute(array_merge($C, $D), PC2);
        $roundKeys[] = $subKey48;
    }

    return $roundKeys;
}

// ================= HELPER FUNCTIONS =================

function stringToBits($str) {
    $bits = [];
    for ($i = 0; $i < strlen($str); $i++) {
        $byteVal = ord($str[$i]);
        $byteBin = str_pad(decbin($byteVal), 8, '0', STR_PAD_LEFT);
        foreach (str_split($byteBin) as $bit) {
            $bits[] = (int)$bit;
        }
    }
    return $bits; // array of 0/1, length = 8 * strlen($str)
}

function bitsToString($bits) {
    $res = '';
    for ($i = 0; $i < count($bits); $i += 8) {
        $byteBits = array_slice($bits, $i, 8);
        $byteVal  = bindec(implode('', $byteBits));
        $res     .= chr($byteVal);
    }
    return $res;
}

function permute($inBits, $table) {
    $out = [];
    foreach ($table as $tPos) {
        // $table is 1-based indexing
        $out[] = $inBits[$tPos - 1];
    }
    return $out;
}

function leftRotate($array, $count) {
    return array_merge(array_slice($array, $count), array_slice($array, 0, $count));
}

function xorBits($a, $b) {
    $res = [];
    for ($i=0; $i < count($a); $i++) {
        $res[] = $a[$i] ^ $b[$i];
    }
    return $res;
}

// =============== DES TABLES ===============

const SHIFT_SCHEDULE = [1,1,2,2,2,2,2,2,1,2,2,2,2,2,2,1];

const PC1 = [
    57,49,41,33,25,17,9,
    1,58,50,42,34,26,18,
    10,2,59,51,43,35,27,
    19,11,3,60,52,44,36,
    63,55,47,39,31,23,15,
    7,62,54,46,38,30,22,
    14,6,61,53,45,37,29,
    21,13,5,28,20,12,4
];

const PC2 = [
    14,17,11,24,1,5,3,28,
    15,6,21,10,23,19,12,4,
    26,8,16,7,27,20,13,2,
    41,52,31,37,47,55,30,40,
    51,45,33,48,44,49,39,56,
    34,53,46,42,50,36,29,32
];

const IP = [
    58,50,42,34,26,18,10,2,
    60,52,44,36,28,20,12,4,
    62,54,46,38,30,22,14,6,
    64,56,48,40,32,24,16,8,
    57,49,41,33,25,17,9,1,
    59,51,43,35,27,19,11,3,
    61,53,45,37,29,21,13,5,
    63,55,47,39,31,23,15,7
];

const FP = [
    40,8,48,16,56,24,64,32,
    39,7,47,15,55,23,63,31,
    38,6,46,14,54,22,62,30,
    37,5,45,13,53,21,61,29,
    36,4,44,12,52,20,60,28,
    35,3,43,11,51,19,59,27,
    34,2,42,10,50,18,58,26,
    33,1,41,9,49,17,57,25
];

// Expand 32-bit block to 48 bits
const E = [
    32,1,2,3,4,5,
    4,5,6,7,8,9,
    8,9,10,11,12,13,
    12,13,14,15,16,17,
    16,17,18,19,20,21,
    20,21,22,23,24,25,
    24,25,26,27,28,29,
    28,29,30,31,32,1
];

// 32-bit permutation in Feistel
const P = [
    16,7,20,21,
    29,12,28,17,
    1,15,23,26,
    5,18,31,10,
    2,8,24,14,
    32,27,3,9,
    19,13,30,6,
    22,11,4,25
];

// 8 standard DES S-boxes
const SBOXES = [
    [
        [14,4,13,1,2,15,11,8,3,10,6,12,5,9,0,7],
        [0,15,7,4,14,2,13,1,10,6,12,11,9,5,3,8],
        [4,1,14,8,13,6,2,11,15,12,9,7,3,10,5,0],
        [15,12,8,2,4,9,1,7,5,11,3,14,10,0,6,13]
    ],
    [
        [15,1,8,14,6,11,3,4,9,7,2,13,12,0,5,10],
        [3,13,4,7,15,2,8,14,12,0,1,10,6,9,11,5],
        [0,14,7,11,10,4,13,1,5,8,12,6,9,3,2,15],
        [13,8,10,1,3,15,4,2,11,6,7,12,0,5,14,9]
    ],
    [
        [10,0,9,14,6,3,15,5,1,13,12,7,11,4,2,8],
        [13,7,0,9,3,4,6,10,2,8,5,14,12,11,15,1],
        [13,6,4,9,8,15,3,0,11,1,2,12,5,10,14,7],
        [1,10,13,0,6,9,8,7,4,15,14,3,11,5,2,12]
    ],
    [
        [7,13,14,3,0,6,9,10,1,2,8,5,11,12,4,15],
        [13,8,11,5,6,15,0,3,4,7,2,12,1,10,14,9],
        [10,6,9,0,12,11,7,13,15,1,3,14,5,2,8,4],
        [3,15,0,6,10,1,13,8,9,4,5,11,12,7,2,14]
    ],
    [
        [2,12,4,1,7,10,11,6,8,5,3,15,13,0,14,9],
        [14,11,2,12,4,7,13,1,5,0,15,10,3,9,8,6],
        [4,2,1,11,10,13,7,8,15,9,12,5,6,3,0,14],
        [11,8,12,7,1,14,2,13,6,15,0,9,10,4,5,3]
    ],
    [
        [12,1,10,15,9,2,6,8,0,13,3,4,14,7,5,11],
        [10,15,4,2,7,12,9,5,6,1,13,14,0,11,3,8],
        [9,14,15,5,2,8,12,3,7,0,4,10,1,13,11,6],
        [4,3,2,12,9,5,15,10,11,14,1,7,6,0,8,13]
    ],
    [
        [4,11,2,14,15,0,8,13,3,12,9,7,5,10,6,1],
        [13,0,11,7,4,9,1,10,14,3,5,12,2,15,8,6],
        [1,4,11,13,12,3,7,14,10,15,6,8,0,5,9,2],
        [6,11,13,8,1,4,10,7,9,5,0,15,14,2,3,12]
    ],
    [
        [13,2,8,4,6,15,11,1,10,9,3,14,5,0,12,7],
        [1,15,13,8,10,3,7,4,12,5,6,11,0,14,9,2],
        [7,11,4,1,9,12,14,2,0,6,10,13,15,3,5,8],
        [2,1,14,7,4,10,8,13,15,12,9,0,3,5,6,11]
    ]
];

//// ====================== TEST CODE ======================
//if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
//    $plaintext = \"Sheldon\";
//    $key       = \"Mykeyred\"; // 8 bytes
//    echo \"Plaintext: $plaintext\\nKey: $key\\n\\n\";
//
//    // Encrypt -> hex
//    $encryptedHex = desCipher('encrypt', $plaintext, $key);
//    echo \"Encrypted (hex): $encryptedHex\\n\";
//
//    // Decrypt from hex -> original
//    $decrypted = desCipher('decrypt', $encryptedHex, $key);
//    echo \"Decrypted: $decrypted\\n\";
//}
//?>
//
//
