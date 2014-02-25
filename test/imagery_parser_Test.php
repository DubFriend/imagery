<?php
require 'imagery_parser.php';

class imagery_parser_Test extends PHPUnit_Framework_TestCase {
    function setUp() {
        $this->parser = new imagery_parser();
    }

    function test_null() {
        $this->assertEquals(
            array(array('type' => 'null')),
            $this->parser->parse(null)
        );
    }

    function test_number() {
        $this->assertEquals(
            array(array('type' => 'pixel', 'value' => 45)),
            $this->parser->parse(45)
        );
        $this->assertEquals(
            array(array('type' => 'pixel', 'value' => 45)),
            $this->parser->parse('45')
        );
        $this->assertEquals(
            array(array('type' => 'pixel', 'value' => 45.2)),
            $this->parser->parse(45.2)
        );
        $this->assertEquals(
            array(array('type' => 'pixel', 'value' => 45.2)),
            $this->parser->parse('45.2')
        );
    }

    function test_negative_number() {
        $this->assertEquals(
            array(array('type' => 'pixel', 'value' => -45.2)),
            $this->parser->parse(-45.2)
        );
    }

    function test_negative_number_pixel() {
        $this->assertEquals(
            array(array('type' => 'pixel', 'value' => -45.2)),
            $this->parser->parse('-45.2px')
        );
    }

    function test_negative_number_percent() {
        $this->assertEquals(
            array(array('type' => 'percent', 'value' => -45.2)),
            $this->parser->parse('-45.2%')
        );
    }

    function test_string_one_token_pixel() {
        $this->assertEquals(
            array(array('type' => 'pixel', 'value' => 45.2)),
            $this->parser->parse('45.2px')
        );
    }

    function test_string_one_token_percent() {
        $this->assertEquals(
            array(array('type' => 'percent', 'value' => 45.2)),
            $this->parser->parse('45.2%')
        );
    }

    function test_multiple_tokens() {
        $this->assertEquals(
            array(
                array('type' => 'percent', 'value' => 45.2),
                array('type' => 'pixel', 'value' => 15),
                array('type' => 'pixel', 'value' => 15),
                array('type' => 'maximum'),
                array('type' => 'minimum')
            ),
            $this->parser->parse('45.2% 15px 15 max min')
        );
    }

}
?>