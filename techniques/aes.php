<?php

/**
 * aesCipher($action, $plaintext, $key)
 *
 * - $action: "encrypt" or "decrypt"
 * - $plaintext: string to encrypt/decrypt (padded to 16 bytes)
 * - $key: any string (truncated/padded to 16 bytes = AES-128)
 *
 * Returns:
 *  - encrypt => hex ciphertext
 *  - decrypt => original plaintext (strip zero-padding)
 *
 * This is a pure-PHP AES demonstration. For real security, prefer a library.
 */

function aesCipher($action, $plaintext, $key) {
    // 1) Make sure the key is exactly 16 bytes
    $finalKey = normalizeAesKey($key);

    // 2) Zero-pad the plaintext to multiples of 16
    while (strlen($plaintext) % 16 !== 0) {
        $plaintext .= "\0";
    }

    // Expand the key (AES key schedule) -> 176 bytes
    $expandedKey = aesKeyExpand($finalKey);

    // Encryption vs Decryption
    if ($action === 'encrypt') {
        $blocks = str_split($plaintext, 16);
        $cipherOut = '';

        foreach ($blocks as $block16) {
            // Encrypt one 16-byte block
            $encrypted = aesEncryptBlock($block16, $expandedKey);
            $cipherOut .= $encrypted;
        }
        // Return hex
        return bin2hex($cipherOut);

    } elseif ($action === 'decrypt') {
        // Convert hex to raw binary
        $rawCipher = hex2bin($plaintext);
        if ($rawCipher === false) {
            throw new Exception("Invalid hex input for ciphertext.");
        }

        $blocks = str_split($rawCipher, 16);
        $plainOut = '';

        foreach ($blocks as $block16) {
            // Decrypt one 16-byte block
            $decrypted = aesDecryptBlock($block16, $expandedKey);
            $plainOut .= $decrypted;
        }

        // Strip null padding
        return rtrim($plainOut, "\0");
    }

    throw new Exception("Invalid action: $action (use 'encrypt' or 'decrypt').");
}

/**
 * normalizeAesKey($key):
 *   If >16 bytes, truncate.
 *   If <16 bytes, pad with nulls up to 16.
 */
function normalizeAesKey($key) {
    $len = strlen($key);
    if ($len > 16) {
        return substr($key, 0, 16);
    } elseif ($len < 16) {
        return str_pad($key, 16, "\0");
    }
    return $key; // exactly 16
}

/**
 * aesKeyExpand($key16):
 *   - AES-128: Expand 16-byte key into 44 words (4 words initial + 40 derived)
 *   - Each word = 4 bytes => total 176 bytes
 */
function aesKeyExpand($key16) {
    $keyWords = []; // each word is 4 bytes

    // Start with the original key => 4 words
    for ($i=0; $i<4; $i++) {
        $keyWords[$i] = [
            ord($key16[$i*4 + 0]),
            ord($key16[$i*4 + 1]),
            ord($key16[$i*4 + 2]),
            ord($key16[$i*4 + 3])
        ];
    }

    // Generate 40 more words => total 44
    for ($i=4; $i<44; $i++) {
        $temp = $keyWords[$i - 1];
        if ($i % 4 === 0) {
            $temp = keyScheduleCore($temp, $i / 4);
        }
        // XOR with word 4 positions back
        $keyWords[$i] = xorWord($keyWords[$i-4], $temp);
    }

    // Flatten into 176 bytes
    $expanded = [];
    foreach ($keyWords as $word) {
        foreach ($word as $b) {
            $expanded[] = $b;
        }
    }
    return $expanded; // array of 176 bytes
}

/**
 * keyScheduleCore($word, $round):
 *  - rotate left by 1 byte
 *  - sub each byte via S-box
 *  - Rcon XOR with first byte
 */
function keyScheduleCore($word, $round) {
    // rotate left 1 byte
    $temp = array_shift($word);
    $word[] = $temp;

    // sub each byte
    for ($i=0; $i<4; $i++) {
        $word[$i] = AES_SBOX[$word[$i]];
    }

    // XOR first byte with Rcon
    $word[0] ^= AES_RCON[$round];
    return $word;
}

/**
 * aesEncryptBlock($block16, $expandedKey):
 *  - Encrypt one 16-byte block with AES-128 in ECB.
 */
function aesEncryptBlock($block16, $expandedKey) {
    // Convert 16-byte block -> 4x4 state
    $state = blockToMatrix($block16);

    // Round 0 => addRoundKey (first 16 bytes)
    addRoundKey($state, $expandedKey, 0);

    // Rounds 1..9
    for ($round=1; $round<10; $round++) {
        subBytes($state);
        shiftRows($state);
        mixColumns($state);
        addRoundKey($state, $expandedKey, $round);
    }

    // Round 10 (no mixColumns)
    subBytes($state);
    shiftRows($state);
    addRoundKey($state, $expandedKey, 10);

    // Convert back to 16-byte block
    return matrixToBlock($state);
}

/**
 * aesDecryptBlock($block16, $expandedKey):
 *  - Decrypt one 16-byte block
 */
function aesDecryptBlock($block16, $expandedKey) {
    $state = blockToMatrix($block16);

    // Round 10
    addRoundKey($state, $expandedKey, 10);
    invShiftRows($state);
    invSubBytes($state);

    // Rounds 9..1
    for ($round=9; $round>=1; $round--) {
        addRoundKey($state, $expandedKey, $round);
        invMixColumns($state);
        invShiftRows($state);
        invSubBytes($state);
    }

    // Round 0
    addRoundKey($state, $expandedKey, 0);

    return matrixToBlock($state);
}

// ------------------ AES Core Functions ------------------

/**
 * addRoundKey($state, $expandedKey, $round)
 */
function addRoundKey(&$state, $expandedKey, $round) {
    // each round uses 16 bytes from expandedKey
    $start = $round * 16;
    for ($c=0; $c<4; $c++) {
        for ($r=0; $r<4; $r++) {
            $state[$r][$c] ^= $expandedKey[$start + $c*4 + $r];
        }
    }
}

function subBytes(&$state) {
    for ($r=0; $r<4; $r++) {
        for ($c=0; $c<4; $c++) {
            $state[$r][$c] = AES_SBOX[$state[$r][$c]];
        }
    }
}
function invSubBytes(&$state) {
    for ($r=0; $r<4; $r++) {
        for ($c=0; $c<4; $c++) {
            $state[$r][$c] = AES_INV_SBOX[$state[$r][$c]];
        }
    }
}

function shiftRows(&$state) {
    // row r => shift left by r
    for ($r=1; $r<4; $r++) {
        $state[$r] = array_merge(array_slice($state[$r], $r), array_slice($state[$r], 0, $r));
    }
}
function invShiftRows(&$state) {
    // row r => shift right by r
    for ($r=1; $r<4; $r++) {
        $state[$r] = array_merge(
            array_slice($state[$r], -$r),
            array_slice($state[$r], 0, 4-$r)
        );
    }
}

function mixColumns(&$state) {
    for ($c=0; $c<4; $c++) {
        $a0 = $state[0][$c];
        $a1 = $state[1][$c];
        $a2 = $state[2][$c];
        $a3 = $state[3][$c];

        $state[0][$c] = gfMul($a0,2) ^ gfMul($a1,3) ^ gfMul($a2,1) ^ gfMul($a3,1);
        $state[1][$c] = gfMul($a0,1) ^ gfMul($a1,2) ^ gfMul($a2,3) ^ gfMul($a3,1);
        $state[2][$c] = gfMul($a0,1) ^ gfMul($a1,1) ^ gfMul($a2,2) ^ gfMul($a3,3);
        $state[3][$c] = gfMul($a0,3) ^ gfMul($a1,1) ^ gfMul($a2,1) ^ gfMul($a3,2);
    }
}
function invMixColumns(&$state) {
    for ($c=0; $c<4; $c++) {
        $a0 = $state[0][$c];
        $a1 = $state[1][$c];
        $a2 = $state[2][$c];
        $a3 = $state[3][$c];

        $state[0][$c] = gfMul($a0,0x0e) ^ gfMul($a1,0x0b) ^ gfMul($a2,0x0d) ^ gfMul($a3,0x09);
        $state[1][$c] = gfMul($a0,0x09) ^ gfMul($a1,0x0e) ^ gfMul($a2,0x0b) ^ gfMul($a3,0x0d);
        $state[2][$c] = gfMul($a0,0x0d) ^ gfMul($a1,0x09) ^ gfMul($a2,0x0e) ^ gfMul($a3,0x0b);
        $state[3][$c] = gfMul($a0,0x0b) ^ gfMul($a1,0x0d) ^ gfMul($a2,0x09) ^ gfMul($a3,0x0e);
    }
}

/**
 * gfMul($a, $b): multiply two bytes in GF(2^8) with AES polynomial 0x11b
 */
function gfMul($a, $b) {
    $r = 0;
    for ($i=0; $i<8; $i++) {
        if (($b & 1) !== 0) {
            $r ^= $a;
        }
        $hi = ($a & 0x80);
        $a = ($a << 1) & 0xFF;
        if ($hi) {
            $a ^= 0x1b;
        }
        $b >>= 1;
    }
    return $r;
}

// ------------------ Helper: block <-> matrix ------------------

function blockToMatrix($block16) {
    $state = [[0,0,0,0],[0,0,0,0],[0,0,0,0],[0,0,0,0]];
    for ($i=0; $i<16; $i++) {
        $state[$i % 4][(int)($i/4)] = ord($block16[$i]);
    }
    return $state;
}
function matrixToBlock($state) {
    $block = '';
    for ($c=0; $c<4; $c++) {
        for ($r=0; $r<4; $r++) {
            $block .= chr($state[$r][$c]);
        }
    }
    return $block;
}

function xorWord($w1, $w2) {
    return [
        $w1[0]^$w2[0],
        $w1[1]^$w2[1],
        $w1[2]^$w2[2],
        $w1[3]^$w2[3]
    ];
}

// ------------------ AES Tables ------------------

const AES_SBOX = [
    0x63,0x7c,0x77,0x7b,0xf2,0x6b,0x6f,0xc5,0x30,0x01,0x67,0x2b,0xfe,0xd7,0xab,0x76,
    0xca,0x82,0xc9,0x7d,0xfa,0x59,0x47,0xf0,0xad,0xd4,0xa2,0xaf,0x9c,0xa4,0x72,0xc0,
    0xb7,0xfd,0x93,0x26,0x36,0x3f,0xf7,0xcc,0x34,0xa5,0xe5,0xf1,0x71,0xd8,0x31,0x15,
    0x04,0xc7,0x23,0xc3,0x18,0x96,0x05,0x9a,0x07,0x12,0x80,0xe2,0xeb,0x27,0xb2,0x75,
    0x09,0x83,0x2c,0x1a,0x1b,0x6e,0x5a,0xa0,0x52,0x3b,0xd6,0xb3,0x29,0xe3,0x2f,0x84,
    0x53,0xd1,0x00,0xed,0x20,0xfc,0xb1,0x5b,0x6a,0xcb,0xbe,0x39,0x4a,0x4c,0x58,0xcf,
    0xd0,0xef,0xaa,0xfb,0x43,0x4d,0x33,0x85,0x45,0xf9,0x02,0x7f,0x50,0x3c,0x9f,0xa8,
    0x51,0xa3,0x40,0x8f,0x92,0x9d,0x38,0xf5,0xbc,0xb6,0xda,0x21,0x10,0xff,0xf3,0xd2,
    0xcd,0x0c,0x13,0xec,0x5f,0x97,0x44,0x17,0xc4,0xa7,0x7e,0x3d,0x64,0x5d,0x19,0x73,
    0x60,0x81,0x4f,0xdc,0x22,0x2a,0x90,0x88,0x46,0xee,0xb8,0x14,0xde,0x5e,0x0b,0xdb,
    0xe0,0x32,0x3a,0x0a,0x49,0x06,0x24,0x5c,0xc2,0xd3,0xac,0x62,0x91,0x95,0xe4,0x79,
    0xe7,0xc8,0x37,0x6d,0x8d,0xd5,0x4e,0xa9,0x6c,0x56,0xf4,0xea,0x65,0x7a,0xae,0x08,
    0xba,0x78,0x25,0x2e,0x1c,0xa6,0xb4,0xc6,0xe8,0xdd,0x74,0x1f,0x4b,0xbd,0x8b,0x8a,
    0x70,0x3e,0xb5,0x66,0x48,0x03,0xf6,0x0e,0x61,0x35,0x57,0xb9,0x86,0xc1,0x1d,0x9e,
    0xe1,0xf8,0x98,0x11,0x69,0xd9,0x8e,0x94,0x9b,0x1e,0x87,0xe9,0xce,0x55,0x28,0xdf,
    0x8c,0xa1,0x89,0x0d,0xbf,0xe6,0x42,0x68,0x41,0x99,0x2d,0x0f,0xb0,0x54,0xbb,0x16
];

const AES_INV_SBOX = [
    0x52,0x09,0x6a,0xd5,0x30,0x36,0xa5,0x38,0xbf,0x40,0xa3,0x9e,0x81,0xf3,0xd7,0xfb,
    0x7c,0xe3,0x39,0x82,0x9b,0x2f,0xff,0x87,0x34,0x8e,0x43,0x44,0xc4,0xde,0xe9,0xcb,
    0x54,0x7b,0x94,0x32,0xa6,0xc2,0x23,0x3d,0xee,0x4c,0x95,0x0b,0x42,0xfa,0xc3,0x4e,
    0x08,0x2e,0xa1,0x66,0x28,0xd9,0x24,0xb2,0x76,0x5b,0xa2,0x49,0x6d,0x8b,0xd1,0x25,
    0x72,0xf8,0xf6,0x64,0x86,0x68,0x98,0x16,0xd4,0xa4,0x5c,0xcc,0x5d,0x65,0xb6,0x92,
    0x6c,0x70,0x48,0x50,0xfd,0xed,0xb9,0xda,0x5e,0x15,0x46,0x57,0xa7,0x8d,0x9d,0x84,
    0x90,0xd8,0xab,0x00,0x8c,0xbc,0xd3,0x0a,0xf7,0xe4,0x58,0x05,0xb8,0xb3,0x45,0x06,
    0xd0,0x2c,0x1e,0x8f,0xca,0x3f,0x0f,0x02,0xc1,0xaf,0xbd,0x03,0x01,0x13,0x8a,0x6b,
    0x3a,0x91,0x11,0x41,0x4f,0x67,0xdc,0xea,0x97,0xf2,0xcf,0xce,0xf0,0xb4,0xe6,0x73,
    0x96,0xac,0x74,0x22,0xe7,0xad,0x35,0x85,0xe2,0xf9,0x37,0xe8,0x1c,0x75,0xdf,0x6e,
    0x47,0xf1,0x1a,0x71,0x1d,0x29,0xc5,0x89,0x6f,0xb7,0x62,0x0e,0xaa,0x18,0xbe,0x1b,
    0xfc,0x56,0x3e,0x4b,0xc6,0xd2,0x79,0x20,0x9a,0xdb,0xc0,0xfe,0x78,0xcd,0x5a,0xf4,
    0x1f,0xdd,0xa8,0x33,0x88,0x07,0xc7,0x31,0xb1,0x12,0x10,0x59,0x27,0x80,0xec,0x5f,
    0x60,0x51,0x7f,0xa9,0x19,0xb5,0x4a,0x0d,0x2d,0xe5,0x7a,0x9f,0x93,0xc9,0x9c,0xef,
    0xa0,0xe0,0x3b,0x4d,0xae,0x2a,0xf5,0xb0,0xc8,0xeb,0xbb,0x3c,0x83,0x53,0x99,0x61,
    0x17,0x2b,0x04,0x7e,0xba,0x77,0xd6,0x26,0xe1,0x69,0x14,0x63,0x55,0x21,0x0c,0x7d
];

const AES_RCON = [
    0x00, // not used
    0x01,0x02,0x04,0x08,0x10,0x20,0x40,0x80,0x1b,0x36
];
//
//// =============== DEMO USAGE ===============
//if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
//    $plaintext = "Sheldon";
//    $key       = "tech";  // 4 bytes -> will be padded to 16
//
//    echo "Plaintext: $plaintext\nKey input: $key\n\n";
//
//    // Encrypt
//    $encHex = aesCipher('encrypt', $plaintext, $key);
//    echo "Encrypted (hex): $encHex\n";
//
//    // Decrypt
//    $dec = aesCipher('decrypt', $encHex, $key);
//    echo "Decrypted: $dec\n";
//}
//?>
<!---->


