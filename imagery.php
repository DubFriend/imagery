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
//     ->scale('50%', '150px max')
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

    private $source, $file_name, $file_access;

    function __construct(array $fig) {
        $this->file_access = $fig['file_access'] ?: new file_access();
        $this->source = $fig['source'];
        $destination = isset($fig['destination']) ? $fig['destination'] : $fig['source'];

        if($this->file_access->is_temp($this->source)) {

            $error_message = $this->file_access->image_upload($this->source, $destination);
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

    function scale(array $fig) {
        return $this;
    }

    function crop(array $fig) {
        return $this;
    }

    private function get_extension($filename) {
        return strtolower(end(explode('.', $filename)));
    }
}
?>