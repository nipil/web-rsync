<?php

declare(strict_types=1);

namespace WRS\Tests;

use PHPUnit\Framework\TestCase;

use WRS\Crypto\KeyManager,
    WRS\Storage\FileStorage,
    WRS\KeyValue\StoredKeyValue;

use org\bovigo\vfs\vfsStream,
    org\bovigo\vfs\vfsStreamWrapper,
    org\bovigo\vfs\vfsStreamDirectory;

class KeyManagerTest extends TestCase
{
    const SAMPLE_KEY = "8ff59950984a23510d5942d8f7c39a0af501d4"
        ."b2866c5ff6cfe35cce9473d3da20eaec20757f6c84e4252522d48906e07a"
        ."b5de9d15527f458903d137e50609ce14760ce09c41ab3b18a3c0abced10b"
        ."14a01890b2cf9691e136f75c63b92985b0a3615608fe362f144043a752bc"
        ."d8a100f18f37ac7ac003845e542804730646521e00d72efbf17f32594bb9"
        ."84361967a0acdec68917696568fb02093863dd762db4a621b9d42fdd4c4c"
        ."f20ca2266a2c1fae8f5713dd66c43d0574cda3531d4a97cfd3c8cf3ae054"
        ."f46b604ff46336bbaa7fbcd4331c64d82bb960e91c4482f002df7c4556cc"
        ."a499eabfd11c449819f7fed0a658128d04eeac30fdcaaa06ea23a735e346"
        ."57b7fc82cc0a336094723b02563ba9a334b21c8e590950fe57597cbfc670"
        ."9d4befd1054c6723d39bc3213871ad191692e158324d551fa0a643d2bee1"
        ."1192257255fadf15e066ce8ee7ba3fd4e8c006124e8a2139c91754eea403"
        ."9e9e42dcc9bd591fbb62827a95c5901ab56a58b652cd7579195478fff5d6"
        ."cd40af8c9567b74d278aa46198c62dc26f92df8eb33cf58b4beb1ccd84b6"
        ."bc15b71b50aa94a58a7593cf74ef11dcaefbf600e6d14b9dbd3cd6f2f1e2"
        ."d36b3e8bdcbc86570ce007d2983965be1cff3c4d724b911d1f20dfb80e5e"
        ."50debf0f036be2f3171b707a96dfa53b93cfea5df1bc720d7d588575f91d"
        ."3d3e15e11fa86be327001d22e3";

    const SAMPLE_SALT = "04e1d85929547ab6";

    const DIR = "baseDirectory";

    protected $key_manager;

    protected function setUp() {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory(self::DIR));
        $this->key_manager = new KeyManager(
            new StoredKeyValue(
                new FileStorage(
                    vfsStream::url(self::DIR))));
    }

    public function providerInvalidHex() {
        return array(
            ["", NULL],
            ["toto", NULL],
            ["1234", NULL],
            ["1", NULL],
        );
    }

    /**
     * @dataProvider providerInvalidHex
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /Invalid length for master-key : \d+/
     */
    public function testSetKeyInvalidHex(string $candidate, $null) {
        $this->key_manager->set_key($candidate);
    }

    /**
     * @dataProvider providerInvalidHex
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /Invalid length for master-salt : \d+/
     */
    public function testSetSaltInvalidHex(string $candidate, $null) {
        $this->key_manager->set_salt($candidate);
    }

    /**
     * @dataProvider providerInvalidHex
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #Invalid (length for|hex string) master-key : .*#
     */
    public function testInvalidGetKey(string $candidate, $null) {
        $file = vfsStream::url(self::DIR . DIRECTORY_SEPARATOR . KeyManager::CONFIG_NAME_KEY);
        file_put_contents($file, $candidate);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(KeyManager::CONFIG_NAME_KEY), "File absent");
        $this->assertSame(file_get_contents($file), $candidate, "Wrong content");
        $this->key_manager->get_key();
    }

    /**
     * @dataProvider providerInvalidHex
     * @expectedException Exception
     * @expectedExceptionMessageRegExp #Invalid (length for|hex string) master-salt : .*#
     */
    public function testInvalidGetSalt(string $candidate, $null) {
        $file = vfsStream::url(self::DIR . DIRECTORY_SEPARATOR . KeyManager::CONFIG_NAME_SALT);
        file_put_contents($file, $candidate);
        $this->assertTrue(vfsStreamWrapper::getRoot()->hasChild(KeyManager::CONFIG_NAME_SALT), "File absent");
        $this->assertSame(file_get_contents($file), $candidate, "Wrong content");
        $this->key_manager->get_salt();
    }

    public function testCreateMaster() {
        $this->key_manager->create_master();
        $this->assertSame(
            KeyManager::MASTER_KEY_LENGTH_BYTES,
            strlen($this->key_manager->get_key()));
        $this->assertSame(
            KeyManager::MASTER_SALT_LENGTH_BYTES,
            strlen($this->key_manager->get_salt()));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid length requested for derived key
     */
    public function testMasterKeyDerivationFailed() {
        $this->key_manager->derive_key(0);
    }

    public function testMasterKeyDerivation() {
        $this->key_manager->set_key(hex2bin(self::SAMPLE_KEY));
        $this->key_manager->set_salt(hex2bin(self::SAMPLE_SALT));

        $len = strlen(hash_hmac(KeyManager::HASH_FUNCTION, "", "", TRUE));

        for ($i = 0; $i < 4; $i ++) {
            for ($j = -3; $j < 4; $j++) {
                $req_len = $len * $i + $j;
                if ($req_len <= 0) {
                    continue;
                }
                $additional_text = sprintf("derived_test_%d", $req_len);
                $key = $this->key_manager->derive_key($req_len, $additional_text);
                $this->assertSame($req_len, strlen($key), "wrong length for derived key");
            }
        }
    }
}
