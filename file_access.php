<?php
class file_access {
    const ERROR_UPLOAD_IMAGE = 'upload failed';
    const ERROR_INVALID_EXTENSION = 'invalid file type';
    const ERROR_NOT_AN_IMAGE = 'not an image';
    const ERROR_MOVE_UPLOADED_FILE = 'could not upload image';
    const ERROR_REPROCESSING_IMAGE = 'invalid image';

    // http://indrek.it/bulletproof-image-upload-security-guide-for-developers/
    // http://stackoverflow.com/questions/4166762/php-image-upload-security-check-list
    function image_upload($source, $destination) {
        $extension_whitelist = array(
            'gif', 'jpeg', 'jpg', 'jif', 'jfif', 'png'
        );

        $error_message = self::ERROR_UPLOAD_IMAGE;

        if(
            in_array(self::get_extension($source), $extension_whitelist) &&
            in_array(self::get_extension($destination), $extension_whitelist)
        ) {
            if(@getimagesize($source)) {
                if(move_uploaded_file($source, $destination)) {
                    if(self::reprocess_image($destination)) {
                        $error_message = null;
                    }
                    else {
                        unlink($destination);
                        $error_message = self::ERROR_REPROCESSING_IMAGE;
                    }
                }
                else {
                    $error_message = self::ERROR_MOVE_UPLOADED_FILE;
                }
            }
            else {
                $error_message = self::ERROR_NOT_AN_IMAGE;
            }
        }
        else {
            $error_message = self::ERROR_INVALID_EXTENSION;
        }

        return $error_message;
    }

    function get_image_dimensions($source) {
        $info = getimagesize($source);
        return array('width' => $info[0], 'height' => $info[1]);
    }

    function scale_image($source, $new_width, $new_height) {
        $image = $this->read_image($source);
        $old_width = imagesx($image);
        $old_height = imagesy($image);
        $scaled_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled(
            $scaled_image, $image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $old_width, $old_height
        );
        $this->write_image($scaled_image, $source);
        imagedestroy($image);
        imagedestroy($scaled_image);
    }

    function crop_image($source, array $a, array $b) {
        $image = $this->read_image($source);
        $new_width = $b['x'] - $a['x'];
        $new_height = $b['y'] - $a['y'];
        $cropped_image = imagecreatetruecolor($new_width, $new_height);
        imagecopy(
            $cropped_image, $image,
            0, 0,
            $a['x'], $a['y'],
            $new_width, $new_height
        );
        $this->write_image($cropped_image, $source);
        imagedestroy($image);
        imagedestroy($cropped_image);
    }

    function is_temp($source) {
        return trim(sys_get_temp_dir(), '/') === explode('/', trim($source,'/'))[0];
    }

    function copy_paste($source, $destination) {
        copy($source, $destination);
    }

    function cut_paste($source, $destination) {
        rename($source, $destination);
    }

    function delete($source) {
        unlink($source);
    }

    function get_extension($file_name) {
        $pieces = explode('.', $file_name);
        return strtolower(end($pieces));
    }

    private function get_image_type($file_name) {
        $extension_map = array (
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'jif' => 'jpg',
            'jfif' => 'jpg',
            'gif' => 'gif',
            'png' => 'png'
        );
        return $extension_map[self::get_extension($file_name)];
    }

    private function read_image($file_path) {
        $image;
        switch(self::get_image_type($file_path)) {
            case 'jpg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'gif':
                $image = imagecreatefromgif($file_path);
                break;
            case 'png':
                $image = imagecreatefrompng($file_path);
                break;
            default:
                throw new Exception("invalid image type");
        }
        return $image;
    }

    private function write_image($image, $file_path) {
        if($image) {
            switch(self::get_image_type($file_path)) {
                case 'jpg':
                    imagejpeg($image, $file_path);
                    break;
                case 'gif':
                    imagegif($image, $file_path);
                    break;
                case 'png':
                    imagepng($image, $file_path);
                    break;
                default:
                    throw new Exception("invalid image type");
            }
            return true;
        }
        else {
            return false;
        }
    }

    // reprocess the image to remove possible embedded code etc.
    private function reprocess_image($file_path) {
        $image = $this->read_image($file_path);
        $this->write_image($image, $file_path);
        imagedestroy($image);
        return true;
    }
}
?>