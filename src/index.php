<?php

require "lib/chacha.php";

$cipher = new ChaCha20Block();
echo "constructor\n".$cipher;
$cipher->set_key(hex2bin("000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f"));
$cipher->set_nonce(hex2bin("303132333435363738393a3b"));
$cipher->set_counter(0x50515253);
echo "setup\n".$cipher;
$cipher->inc_counter(-3);
echo "inc_ctr\n".$cipher;
