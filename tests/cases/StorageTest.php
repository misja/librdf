<?php
namespace librdf\tests\cases;

// test the Storage class

use librdf\Storage;

class StorageTest extends \lithium\test\Unit
{
    public function setUp()
    {
        $this->storage = new Storage();
    }

    public function testConstructor()
    {
        $this->assertTrue($this->storage instanceof \librdf\Storage);
    }

    // most storage backends don't support cloning, skipping that test

    public function testGetStorage()
    {
        $this->assertTrue(is_resource($this->storage->getStorage()));
    }
}