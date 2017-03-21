<?php

require "lib/chacha.php";

$cipher = new ChaCha20Block();
$cipher->set_key(hex2bin("000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f"));
$cipher->set_nonce(hex2bin("000000090000004a00000000"));
$cipher->set_counter(1);

echo "init\n";
echo $cipher;

$cipher->compute_block();

echo "final\n";
echo $cipher;
