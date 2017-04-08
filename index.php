<?php

require_once __DIR__.'/vendor/autoload.php';

use ChaCha20\ChaCha20Block;

$cipher = new ChaCha20Block();
$cipher->set_key(hex2bin("000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f"));
$cipher->set_nonce(hex2bin("000000090000004a00000000"));
$cipher->set_counter(1);

printf("BEG: %s\n", bin2hex(
    $cipher->serialize_state(ChaCha20Block::STATE_INITIAL)));

$cipher->compute_block();

printf("END: %s\n", bin2hex(
    $cipher->serialize_state(ChaCha20Block::STATE_FINAL)));
