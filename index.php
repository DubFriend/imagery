<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'file_access.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file_access = new file_access();
    $file_access->image_upload(
        $_FILES['file']['tmp_name'],
        'image.' . $file_access->get_extension($_FILES['file']['name'])
    );
}
?>
<html>
<head>
    <title></title>
</head>
<body>
    <form enctype="multipart/form-data" method="POST" action="">
        <input type="file" name="file"/>
        <input type="submit" value="upload"/>
    </form>
</body>
</html>
