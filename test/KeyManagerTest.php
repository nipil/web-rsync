<?php

declare(strict_types=1);

namespace WRS;

use PHPUnit\Framework\TestCase;

use WRS\KeyManager;

use org\bovigo\vfs\vfsStream, org\bovigo\vfs\vfsStreamWrapper, org\bovigo\vfs\vfsStreamDirectory;

/*
 * see: https://github.com/mikey179/vfsStream/wiki/Known-Issues
 *
 * override "realpath" by creating a function
 * in the "current" namespace which will be called
 * instead of the global one : that way, realpath
 * doesn't complain about vfsStream pathes
 */
function realpath($path) {
    return $path;
}

class KeyManagerTest extends TestCase
{
    const SAMPLE_MASTER_KEY = "8ff59950984a23510d5942d8f7c39a0af501d4"
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

    const SAMPLE_MASTER_SALT = "04e1d85929547ab6";

    const SAMPLE_OUTPUT_FILE = "<?php" . PHP_EOL
        . "return array (" . PHP_EOL
        . "  'key' => '" . self::SAMPLE_MASTER_KEY . "'," . PHP_EOL
        . "  'salt' => '" . self::SAMPLE_MASTER_SALT . "'," . PHP_EOL
        . ");" . PHP_EOL;

    const SAMPLE_INPUT_NULL = "<?php" . PHP_EOL
        . "return array (" . PHP_EOL
        . "  'key' => null," . PHP_EOL
        . "  'salt' => null," . PHP_EOL
        . ");" . PHP_EOL;

    const SAMPLE_INPUT_MISSING = "<?php" . PHP_EOL
        . "return array (" . PHP_EOL
        . "  'missing' => null," . PHP_EOL
        . "  'salt' => null," . PHP_EOL
        . ");" . PHP_EOL;

    const SAMPLE_INPUT_NOT_AN_ARRAY = "<?php" . PHP_EOL
        . "return TRUE;" . PHP_EOL;

    public function setUp()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('baseDirectory'));
    }

    public function testMasterKeyCreation() {
        $km = new KeyManager(dirname(__FILE__));
        $this->assertNull($km->get_master_key(), "invalid master key initialization");
        $this->assertNull($km->get_master_salt(), "invalid master salt initialization");
        $km->create_master();
        $this->assertRegExp(
            sprintf("/^[[:xdigit:]]{%d}$/", KeyManager::MASTER_KEY_LENGTH_BYTES * 2),
            $km->get_master_key(),
            "invalid master key");
        $this->assertRegExp(
            sprintf("/^[[:xdigit:]]{%d}$/", KeyManager::MASTER_SALT_LENGTH_BYTES * 2),
            $km->get_master_salt(),
            "invalid master salt");
    }

    public function testMasterSave() {
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->set_master_key(self::SAMPLE_MASTER_KEY);
        $km->set_master_salt(self::SAMPLE_MASTER_SALT);
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file already present");
        $km->save();
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file is absent");
        $content = file_get_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . KeyManager::MASTER_SECRET_FILE));
        $this->assertNotSame(FALSE, $content, "content is FALSE");
        $this->assertEquals(self::SAMPLE_OUTPUT_FILE, $content, "output differ");
    }

    public function testMasterLoad() {
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file already present");
        file_put_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . KeyManager::MASTER_SECRET_FILE),
            self::SAMPLE_OUTPUT_FILE);
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file is absent");

        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->load();

        $this->assertEquals(
            $km->get_master_key(),
            self::SAMPLE_MASTER_KEY);

        $this->assertEquals(
            $km->get_master_salt(),
            self::SAMPLE_MASTER_SALT);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Cannot load master key/salt
     */
    public function testMasterLoadFailedMissingFile() {
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file already present");
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->load();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Missing key in master key/salt file
     */
    public function testMasterLoadFailedNull() {
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file already present");
        file_put_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . KeyManager::MASTER_SECRET_FILE),
            self::SAMPLE_INPUT_NULL);
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file is absent");
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->load();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Missing key in master key/salt file
     */
    public function testMasterLoadFailedMissingKey() {
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file already present");
        file_put_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . KeyManager::MASTER_SECRET_FILE),
            self::SAMPLE_INPUT_MISSING);
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file is absent");
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->load();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Missing key in master key/salt file
     */
    public function testMasterLoadFailedNotAnArray() {
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file already present");
        file_put_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . KeyManager::MASTER_SECRET_FILE),
            self::SAMPLE_INPUT_NOT_AN_ARRAY);
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file is absent");
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->load();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Missing key in master key/salt file
     */
    public function testMasterLoadFailedEmpty() {
        $this->assertFalse(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file already present");
        file_put_contents(
            vfsStream::url(
                'baseDirectory'
                . DIRECTORY_SEPARATOR
                . KeyManager::MASTER_SECRET_FILE),
            "");
        $this->assertTrue(
            vfsStreamWrapper::getRoot()->hasChild(KeyManager::MASTER_SECRET_FILE),
            "master key file is absent");
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->load();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid length requested for derived key
     */
    public function testMasterKeyDerivationFailed() {
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->set_master_key(self::SAMPLE_MASTER_KEY);
        $km->set_master_salt(self::SAMPLE_MASTER_SALT);
        $km->derive_key(0);
    }

    public function testMasterKeyDerivation() {
        $km = new KeyManager(vfsStream::url('baseDirectory'));
        $km->set_master_key(self::SAMPLE_MASTER_KEY);
        $km->set_master_salt(self::SAMPLE_MASTER_SALT);

        $mac = hash_hmac(KeyManager::HASH_FUNCTION, "", "", TRUE);
        $len = strlen(hash_hmac(KeyManager::HASH_FUNCTION, "", "", TRUE));

        for ($i = 0; $i < 4; $i ++) {
            for ($j = -3; $j < 4; $j++) {
                $req_len = $len * $i + $j;
                if ($req_len <= 0) {
                    continue;
                }
                $additional_text = sprintf("derived_test_%d", $req_len);
                $key = $km->derive_key($req_len, $additional_text);
                $hex_key = bin2hex($key);
                $res = preg_match(
                    sprintf("/^[[:xdigit:]]{%d}$/", $req_len * 2),
                    $hex_key);
                $this->assertEquals($res, 1, "Derived key in hex form does not have requested length for " . $additional_text);
            }
        }
    }
}
