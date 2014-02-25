<?php
require_once 'file_access.php';
require_once 'imagery.php';

class file_access_mock {

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
           $get_extension_return = 'jpg',
           $scale_parameters;

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

    function scale_image($source, $width, $height) {
        $this->scale_parameters = array(
            'source' => $source,
            'width' => $width,
            'height' => $height
        );

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

    function test_scale_by_pixels() {
        $this->imagery->scale(5, 6);
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 5, 'height' => 6),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_by_pixels_string() {
        $this->imagery->scale('5', '6px');
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 5, 'height' => 6),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_by_percent() {
        $this->imagery->scale('50%', '10%');
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 150, 'height' => 40),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_by_percent_value_to_integer() {
        $this->imagery->scale('51.5%', '10.3%');
        $this->assertEquals(
            // instead of 154.5 and 41.2
            array('source' => 'destination.jpg', 'width' => 154, 'height' => 41),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_conciders_restrictions_percent_to_pixel() {
        $this->imagery->scale('30% 180px min', '200% 500px max');
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 180, 'height' => 500),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_conciders_restrictions_pixel_to_percent() {
        $this->imagery->scale('30px 50% min', '900px 200% max');
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 150, 'height' => 800),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_conciders_restrictions_not_restricted() {
        $this->imagery->scale('50% 5px min', '200% 5000px max');
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 150, 'height' => 800),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_with_callback() {
        $this->imagery->scale(function ($width, $height) {
            return array($width / 3, $height * 3);
        });
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 100, 'height' => 1200),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_with_callback_to_integer() {
        $this->imagery->scale(function ($width, $height) {
            return array($width / 7, $height * 1.123);
        });
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 42, 'height' => 449),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_no_height_parameter_scales_in_proportion_to_width() {
        $this->imagery->scale(100);
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 100, 'height' => 133),
            $this->file_access->scale_parameters
        );
    }

    function test_scale_no_height_parameter_scales_in_proportion_to_height() {
        $this->imagery->scale(null, 100);
        $this->assertEquals(
            array('source' => 'destination.jpg', 'width' => 75, 'height' => 100),
            $this->file_access->scale_parameters
        );
    }

}
?>
