<?php
require_once 'file_access.php';

class file_access_Test extends PHPUnit_Framework_TestCase {
    private $file_access;
    function setUp() {
        $this->file_access = new file_access();
    }

    function test_is_temp_pass() {
        $this->assertTrue($this->file_access->is_temp('/tmp/foo'));
        $this->assertTrue($this->file_access->is_temp('tmp/foo'));
    }

    function test_is_temp_fail() {
        $this->assertFalse($this->file_access->is_temp('not/temp'));
        $this->assertFalse($this->file_access->is_temp('tmpnot'));
    }
}
?>