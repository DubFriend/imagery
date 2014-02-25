<?php
// try {
//     $img = new imagery(array(
//         'source' => $_FILES['tmp_name'],
//         'destination' => '/images/blarg.jpg'
//     ));
// }
// catch(imagery_exception $e) {
//     $error = $e->getMesssage();
// }



// $img->cut_paste('new/image.jpg')
//     ->scale('50%', '150% max 200px')
//     ->crop(function ($width, $height) {
//         return array(
//             array(0, 0), array($width / 2, $height / 2)
//         );
//     })
//     ->crop(array(4, 3), array(50, 60))
//     ->copy_paste('thumb/image.jpg')
//     ->scale(75);



class imagery_exception extends Exception {}
class imagery {

    private $source, $file_access, $parser;

    function __construct(array $fig) {
        $this->file_access = $fig['file_access'] ?: new file_access();
        $this->parser = new imagery_parser();
        $this->source = $fig['source'];
        $destination = isset($fig['destination']) ?
            $fig['destination'] : $fig['source'];

        if($this->file_access->is_temp($this->source)) {
            $error_message = $this->file_access->image_upload(
                $this->source, $destination
            );
            if($error_message) {
                throw new imagery_exception($error_message);
            }
            else {
                $this->source = $destination;
            }
        }
        else if($this->source !== $destination) {
            $this->copy_paste($destination);
        }
    }

    function get_source() {
        return $this->source;
    }

    function copy_paste($destination) {
        $this->file_access->copy_paste($this->source, $destination);
        $this->source = $destination;
        return $this;
    }

    function cut_paste($destination) {
        $this->file_access->cut_paste($this->source, $destination);
        $this->source = $destination;
        return $this;
    }

    function scale($a, $b) {
        $dim = $this->file_access->get_image_dimensions($this->source);
        if(is_callable($a)) {
            list($newWidth, $newHeight) = $a($dim['width'], $dim['height']);
        }
        else {
            list($newWidth, $newHeight) = $this->calculate_scale_dimensions(
                $this->parser->parse($a),
                $this->parser->parse($b)
            );
        }
        $this->file_access->scale_image($this->source, $newWidth, $newHeight);

        return $this;
    }

    private function calculate_scale_dimensions(array $width, array $height) {
        $currentDim = $this->file_access->get_image_dimensions($this->source);
        $rawWidth = $this->calculate_pixel_value($width[0], $currentDim['width']);
        $rawHeight = $this->calculate_pixel_value($height[0], $currentDim['height']);
        array_shift($width);
        array_shift($height);

        if(!is_null($rawWidth) && !is_null($rawHeight)) {
            $restrictedWidth = $this->concider_restrictions(
                $width, $rawWidth, $currentDim['width']
            );
            $restrictedHeight = $this->concider_restrictions(
                $height, $rawHeight, $currentDim['height']
            );
        }
        else if(is_null($rawWidth)) {

        }
        else if(is_null($rawHeight)) {

        }
        else {
            throw new Exception('insufficient configuration');
        }
    }

    private function concider_restrictions(array $restriction, $raw, $current) {
        if($restriction) {
            $restrictedValue = $this->calculate_pixel_value(
                $restriction[0], $current
            );
            if(isset($restriction[1])) {
                if($restriction[1]['type'] === 'maximum') {
                    if($raw > $restrictedValue) {
                        return $restrictedValue;
                    }
                    else {
                        return $raw;
                    }
                }
                else if($restriction[1]['type'] === 'minimum') {
                    if($raw < $restrictedValue) {
                        return $restrictedValue;
                    }
                    else {
                        return $raw;
                    }
                }
                else {
                    throw new Exception('qualifier must be "max" or "min"');
                }
            }
            else {
                throw new Exception('must specify if restriction is a max or min');
            }
        }
        else {
            return $raw;
        }
    }

    private function calculate_pixel_value(array $value, $comparedValue) {
        if($value['type'] === 'pixel') {
            return $value['value'];
        }
        else if($value['type'] === 'percent') {
            return $value['value'] / 100 * $comparedValue;
        }
        else if($value['type'] === 'null') {
            return null;
        }
        else {
            throw new Exception('invalid type');
        }
    }
}
?>