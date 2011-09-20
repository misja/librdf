<?php
namespace librdf\tests\cases;

use librdf\exception\Error;

class ErrorTest extends \lithium\test\Unit
{
    public function testConstructor() {
        $error = new Error();
        $this->assertEqual($error instanceof \librdf\exception\Error, true);

        $error = new Error("Message");
        $this->assertEqual($error instanceof \librdf\exception\Error, true);
    }

    public function testMessage() {
        $error = new Error("test message");
        $this->assertEqual("test message", $error->getMessage());
    }

    public function testIsThrowable() {
        $exp = new Error();
        try {
            throw $exp;
            $this->fail("Unable to throw exception");
        } catch (Error $e) {
            $this->assertTrue(true);
        }
    }
}
