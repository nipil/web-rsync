<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WRS\KeyManager;

class KeyManagerTest extends TestCase
{
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
}
