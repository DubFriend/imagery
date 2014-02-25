#imagery

Image manipulation libary for php for easy cropping and scaling of photos.

Example usage
```php
try {
    $img = new imagery(array(
        'source' => $_FILES['tmp_name'],
        'destination' => '/images/blarg.jpg'
    ));
}
catch(imagery_exception $e) {
    $error = $e->getMesssage();
}

$img->cut_paste('new/image.jpg')
    ->scale('50%', '150px max')
    ->crop(function ($width, $height) {
        return array(
            array(0, 0), array($width / 2, $height / 2)
        );
    })
    ->crop(array(4, 3), array(50, 60))
    ->copy_paste('thumb/image.jpg')
    ->scale(75);
```