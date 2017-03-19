<?php

function do_client() {
    echo "client\n";
}

function do_server() {
    echo "server\n";
}

if (php_sapi_name() === 'cli' or defined('STDIN')) {
    do_client();
} else {
    do_server();
}
