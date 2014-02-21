<?php
class file_access {
    // http://indrek.it/bulletproof-image-upload-security-guide-for-developers/
    // http://stackoverflow.com/questions/4166762/php-image-upload-security-check-list
    function image_upload($source, $destination) {
        $extension_whitelist = array(
            'gif', 'jpeg', 'jpg', 'jif', 'jfif', 'png'
        );

        $error_message = 'upload failed';
        if(in_array(self::get_extension($destination), $extension_whitelist)) {
            if(getimagesize($source)) {
                if(move_uploaded_file($source, $destination)) {
                    if(self::reprocess_image($destination)) {
                        $error_message = null;
                    }
                    else {
                        unlink($destination);
                        $error_message = 'invalid image';
                    }
                }
                else {
                    $error_message = 'could not upload image';
                }
            }
            else {
                $error_message = 'not an image';
            }
        }
        else {
            $error_message = 'invalid file type';
        }

        return $error_message;
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

    // reprocess the image to remove possible embedded code etc.
    private function reprocess_image($file_path) {
        switch(self::get_image_type($file_path)) {
            case 'jpg':
                $image = @imagecreatefromjpeg($file_path);
                if($image) {
                    imagejpeg($image, $file_path);
                }
                else {
                    return false;
                }
                break;
            case 'gif':
                $image = @imagecreatefromgif($file_path);
                if($image) {
                    imagegif($image, $file_path);
                }
                else {
                    return false;
                }
                break;
            case 'png':
                $image = @imagecreatefrompng($file_path);
                if($image) {
                    imagepng($image, $file_path);
                }
                else {
                    return false;
                }
                break;
            default:
                throw new Exception('invalid image extension');
        }
        imagedestroy($image);
        return true;
    }
}
?>