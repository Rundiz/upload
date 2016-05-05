<?php
require __DIR__.DIRECTORY_SEPARATOR.'includes.php';


if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    $Upload = new \Rundiz\Upload\Upload('file_get_mime');
    $file_info = $Upload->testGetUploadedMimetype('file_get_mime');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test get uploaded file's mime type.</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>Test get uploaded file's mime type.</h1>
                    <?php if (isset($file_info) && !empty($file_info)) { ?> 
                    <div class="alert alert-warning">
                        <?php echo $file_info; ?> 
                    </div>
                    <?php }// endif; ?> 
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="file_get_mime">
                        <button type="submit" class="btn btn-primary">Get file's mime type.</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
<?php
unset($file_info, $Upload);