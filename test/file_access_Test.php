<?php
require_once 'file_access.php';

class file_access_Test extends PHPUnit_Framework_TestCase {
    private $file_access,
            $fileGif = 'test/files/test.gif',
            $fileJpg = 'test/files/test.jpg',
            $fileWrongExtension = 'test/files/test.wrong',
            $fileNotAnImage = 'test/files/not_an_image.jpg',
            $fileTempGif = 'test/files/temp.test.gif',
            $fileTempWrongExtension = 'test/files/temp.test.wrong',
            $tempStorage = 'test/files/temporary';

    function setUp() {
        $this->file_access = new file_access();
    }

    function tearDown() {
        if(file_exists($this->fileTempGif)) {
            unlink($this->fileTempGif);
        }
        if(file_exists($this->fileTempWrongExtension)) {
            unlink($this->fileTempWrongExtension);
        }
        if(file_exists($this->tempStorage)) {
            unlink($this->tempStorage);
        }
    }

    function test_get_image_dimensions() {
        $this->assertEquals(
            array('width' => 480, 'height' => 340),
            $this->file_access->get_image_dimensions($this->fileJpg)
        );
    }

    function test_is_temp_pass() {
        $this->assertTrue($this->file_access->is_temp('/tmp/foo'));
        $this->assertTrue($this->file_access->is_temp('tmp/foo'));
    }

    function test_is_temp_fail() {
        $this->assertFalse($this->file_access->is_temp('not/temp'));
        $this->assertFalse($this->file_access->is_temp('tmpnot'));
    }

    function test_get_extension() {
        $this->assertEquals(
            'foo', $this->file_access->get_extension('test.foo')
        );
    }

    function test_image_upload_pass() {
        $this->assertEquals(
            file_access::ERROR_MOVE_UPLOADED_FILE,
            $this->file_access->image_upload($this->fileGif, $this->fileTempGif)
        );
        $this->assertFalse(file_exists($this->fileTempGif));
    }

    function test_image_upload_fail_invalid_extension_new_name() {
        $this->assertEquals(
            file_access::ERROR_INVALID_EXTENSION,
            $this->file_access->image_upload(
                $this->fileGif, $this->fileTempWrongExtension
            )
        );
        $this->assertFalse(file_exists($this->fileTempWrongExtension));
    }

    function test_image_upload_fail_invalid_extension_old_name() {
        $this->assertEquals(
            file_access::ERROR_INVALID_EXTENSION,
            $this->file_access->image_upload(
                $this->fileWrongExtension, $this->fileTempGif
            )
        );
        $this->assertFalse(file_exists($this->fileTempGif));
    }

    function test_image_upload_fail_not_an_image() {
        $this->assertEquals(
            file_access::ERROR_NOT_AN_IMAGE,
            $this->file_access->image_upload(
                $this->fileNotAnImage, $this->fileTempGif
            )
        );
        $this->assertFalse(file_exists($this->fileTempGif));
    }

    function test_copy_paste() {
        $this->file_access->copy_paste($this->fileGif, $this->fileTempGif);
        $this->assertTrue(file_exists($this->fileGif));
        $this->assertTrue(file_exists($this->fileTempGif));
    }

    function test_cut_paste() {
        copy($this->fileGif, $this->tempStorage);
        $this->file_access->cut_paste($this->fileGif, $this->fileTempGif);
        $this->assertFalse(file_exists($this->fileGif));
        $this->assertTrue(file_exists($this->fileTempGif));
        copy($this->tempStorage, $this->fileGif);
    }

    function test_delete() {
        copy($this->fileGif, $this->tempStorage);
        $this->file_access->delete($this->fileGif);
        $this->assertFalse(file_exists($this->fileGif));
        copy($this->tempStorage, $this->fileGif);
    }

    function test_scale_image() {
        copy($this->fileGif, $this->fileTempGif);
        $this->file_access->scale_image($this->fileTempGif, 20, 30);
        list($width, $height) = getimagesize($this->fileTempGif);
        $this->assertEquals($width, 20, 'correct width');
        $this->assertEquals($height, 30, 'correct height');
    }

    function test_crop_image() {
        copy($this->fileGif, $this->fileTempGif);
        $this->file_access->crop_image(
            $this->fileTempGif,
            array('x' => 5, 'y' => 20),
            array('x' => 30, 'y' => 50)
        );
    }
}
?>
