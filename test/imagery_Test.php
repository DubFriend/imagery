<?php
require_once 'file_access.php';
require_once 'imagery.php';

class file_access_mock extends file_access {

    public $image_upload_parameters,
           $image_upload_return = null,
           $get_image_dimensions_parameters,
           $get_image_dimensions_return = array('width' => 300, 'height' => 400),
           $is_temp_parameters,
           $is_temp_return = true,
           $copy_paste_parameters,
           $cut_paste_parameters,
           $delete_parameters,
           $get_extension_parameters,
           $get_extension_return = 'jpg';

    function image_upload($source, $destination) {
        $this->image_upload_parameters = array(
            'source' => $source,
            'destination' => $destination
        );
        return $this->image_upload_return;
    }

    function get_image_dimensions($source) {
        $this->get_image_dimensions_parameters = array('source' => $source);
        return $this->get_image_dimensions_return;
    }

    function is_temp($source) {
        $this->is_temp_parameters = array('source' => $source);
        return $this->is_temp_return;
    }

    function copy_paste($source, $destination) {
        $this->copy_paste_parameters = array(
            'source' => $source,
            'destination' => $destination
        );
    }

    function cut_paste($source, $destination) {
        $this->cut_paste_parameters = array(
            'source' => $source,
            'destination' => $destination
        );
    }

    function delete($source) {
        $this->delete_parameters = array('source' => $source);
    }

    function get_extension($file_name) {
        $this->get_extension_parameters = array('file_name' => $file_name);
        return $this->get_extension_return;
    }
}

class imagery_Test extends PHPUnit_Framework_TestCase {

    function setUp() {
        $this->setup_imagery();
    }

    function setup_imagery(array $override = array()) {
        $this->file_access = new file_access_mock();
        $this->imagery_config = array(
            'file_access' => $this->file_access,
            'source' => 'source.jpg',
            'destination' => 'destination.jpg'
        );
        $this->imagery = new imagery(array_merge(
            $this->imagery_config, $override
        ));
    }

    function test_instantiation_source_uploaded_to_destination() {
        $this->assertEquals('destination.jpg', $this->imagery->get_source());
        $this->assertEquals(
            array(
                'source' => 'source.jpg',
                'destination' => 'destination.jpg'
            ),
            $this->file_access->image_upload_parameters
        );
    }

    function test_instantiation_not_temp_copy_pasted_to_destination() {
        $file_access = new file_access_mock();
        $file_access->is_temp_return = false;
        $this->setup_imagery(array('file_access' => $file_access));
        $this->assertEquals('destination.jpg', $this->imagery->get_source());
        $this->assertEquals(
            array(
                'source' => 'source.jpg',
                'destination' => 'destination.jpg'
            ),
            $file_access->copy_paste_parameters
        );
    }

    function test_destination_defaults_to_source() {
        $this->setup_imagery(array('destination' => null));
        $this->assertEquals('source.jpg', $this->imagery->get_source());
    }

    function test_copy_paste() {
        $this->imagery->copy_paste('new_destination');
        $this->assertEquals('new_destination', $this->imagery->get_source());
        $this->assertEquals(
            array('source' => 'destination.jpg', 'destination' => 'new_destination'),
            $this->file_access->copy_paste_parameters
        );
    }

    function test_cut_paste() {
        $this->imagery->cut_paste('new_destination');
        $this->assertEquals('new_destination', $this->imagery->get_source());
        $this->assertEquals(
            array('source' => 'destination.jpg', 'destination' => 'new_destination'),
            $this->file_access->cut_paste_parameters
        );
    }

}
?>
