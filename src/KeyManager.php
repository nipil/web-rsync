<?php

declare(strict_types=1);

namespace WRS;

class KeyManager {

    const MASTER_KEY_LENGTH_BITS = 1 << 12;
    const MASTER_KEY_LENGTH_BYTES = self::MASTER_KEY_LENGTH_BITS >> 3;
    const MASTER_SALT_LENGTH_BYTES = 1 << 3;
    const MASTER_SECRET_FILE = "wrs_secret.php";
    const HASH_FUNCTION = "sha512";

    private $logger;
    private $base_path;
    private $master_key;
    private $master_salt;

    public function get_secret_path() {
        $this->logger->debug(__METHOD__);
        $path = realpath($this->base_path);
        if ($path === FALSE) {
            throw new \Exception(sprintf(
                "%s is not a valid path",
                $path));
        }
        return $path . DIRECTORY_SEPARATOR . self::MASTER_SECRET_FILE;
    }

    public function create_master() {
        $this->logger->debug(__METHOD__);
        $this->master_key = bin2hex(random_bytes(self::MASTER_KEY_LENGTH_BYTES));
        $this->master_salt = bin2hex(random_bytes(self::MASTER_SALT_LENGTH_BYTES));
    }

    public function derive_key(int $req_len, string $additionnal_info = "") {
        $this->logger->debug(__METHOD__.":".join(" ", func_get_args()));
        if ($req_len <= 0) {
            throw new \Exception("Invalid length");
        }

        // extract phase (with 2.1 note : 'IKM' is used as the HMAC input, not as the HMAC key)
        $prk = hash_hmac(self::HASH_FUNCTION, $this->master_key, $this->master_salt, TRUE);

        // handles different hashing functions
        $len = strlen($prk);
        $n_iter = ceil($req_len / $len);

        // expand phase RFC 5869
        $final_output = "";
        $iteration_output = "";
        for ($i = 1; $i <= $n_iter; $i++) {
            $iteration_input = $iteration_output . $additionnal_info . $i;
            $iteration_output = hash_hmac(self::HASH_FUNCTION, $iteration_input, $prk, TRUE);
            $final_output .= $iteration_output;
        }

        // retain only requested length byte
        return substr($final_output, 0, $req_len);
    }

    public function save() {
        $this->logger->debug(__METHOD__);
        // build data
        $data = array();
        $data["key"] = $this->master_key;
        $data["salt"] = $this->master_salt;
        // generate text
        $txt = '<?php'.PHP_EOL.'return '.var_export($data, true).';'.PHP_EOL;
        // save key configuration
        $res = file_put_contents($this->get_secret_path(), $txt);
        if ($res === FALSE) {
            throw new \Exception();
        }
    }

    public function __construct(string $base_path) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__.":".join(" ", func_get_args()));
        $this->base_path = $base_path;
        $this->master_key = NULL;
        $this->master_salt = NULL;
    }
}
