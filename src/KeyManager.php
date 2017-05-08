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

    public function load() {
        $this->logger->debug(__METHOD__);
        // load master key/salt
        $res = @include($this->get_secret_path());
        if ($res === FALSE) {
            throw new \Exception("Cannot load master key/salt");
        }
        var_dump($res);
        if (!isset($res["key"])) {
            throw new \Exception("Missing key in master key/salt file");
        }
        $this->set_master_key($res["key"]);
        if (!isset($res["salt"])) {
            throw new \Exception("Missing salt in master key/salt file");
        }
        $this->set_master_salt($res["salt"]);
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

    public function set_master_key(string $hex_key) {
        $this->logger->debug(__METHOD__.":".join(" ", func_get_args()));
        $res = preg_match(
            sprintf("/^[[:xdigit:]]{%d}$/", KeyManager::MASTER_KEY_LENGTH_BYTES * 2),
            $hex_key);
        if ($res === FALSE) {
            throw new \Exception("Could not match master key with validation regex");
        } elseif ($res === 0) {
            throw new \Exception("Master key is invalid");
        }
        $this->master_key = $hex_key;
    }

    public function set_master_salt(string $hex_salt) {
        $this->logger->debug(__METHOD__.":".join(" ", func_get_args()));
        $res = preg_match(
            sprintf("/^[[:xdigit:]]{%d}$/", KeyManager::MASTER_SALT_LENGTH_BYTES * 2),
            $hex_salt);
        if ($res === FALSE) {
            throw new \Exception("Could not match master salt with validation regex");
        } elseif ($res === 0) {
            throw new \Exception("Master salt is invalid");
        }
        $this->master_salt = $hex_salt;
    }

    public function get_master_key() {
        return $this->master_key;
    }

    public function get_master_salt() {
        return $this->master_salt;
    }

    public function __construct(string $base_path) {
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->logger->debug(__METHOD__.":".join(" ", func_get_args()));
        $this->base_path = $base_path;
        $this->master_key = NULL;
        $this->master_salt = NULL;
    }
}
