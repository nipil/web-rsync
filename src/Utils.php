<?php

declare(strict_types=1);

namespace WRS;

class Utils {

    public static function StringToInt(string $input) {
        $valid = preg_match('/^-?[[:digit:]]+$/', $input);
        if ($valid === 0) {
            throw new \Exception(sprintf(
                "Invalid integer %s",
                $input));
        }
        $result = sscanf($input, "%d", $value);
        if ($result === 0) {
            throw new \Exception("Cannot convert %s to an integer");
        }
        return $value;
    }
}
