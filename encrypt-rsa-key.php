<?php
$pTKeyE= $_POST["e"];
$pTKeyN= $_POST["n"];

$cTKey =
    ["e" => $pTKeyE,
        "n" => $pTKeyN,
    ];

echo json_encode ($cTKey);
