<?php

declare(strict_types=1);

namespace WRS\Crypto;

interface RandomDataInterface {
    public function get(int $bytes);
}
