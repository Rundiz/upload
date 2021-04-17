<?php
require __DIR__.DIRECTORY_SEPARATOR.'includes.php';
if (is_file('../../vendor/autoload.php')) {
    require_once '../../vendor/autoload.php';
}

if (!class_exists('Appwrite\ClamAV\Network')) {
    die('ClamAV class is not installed. Please follow instruction on <a href="https://github.com/appwrite/php-clamav" target="_blank">https://github.com/appwrite/php-clamav</a>');
}

use \Appwrite\ClamAV\Network;

class MyAVS
{
    public function doScan($UploadClass, $fileUploadedPath, $fileContents, $fileOriginalName)
    {
        try {
            $clam = new Network('localhost', 3310);
            $scanResult = $clam->fileScan($fileUploadedPath);
            if (is_bool($scanResult) && true === $scanResult) {
                return true;
            }

            $UploadClass->externalSecurityScanResultMessage = 'Virus scan failed. (' . $fileOriginalName . ')';
            return false;
        } catch (\Exception $ex) {
            $UploadClass->externalSecurityScanResultMessage = $ex->getMessage();
            return false;
        }
    }
}
$MyAVS = new MyAVS();

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    $Upload = new \Rundiz\Upload\Upload('filename');
    $Upload->move_uploaded_to = __DIR__ . DIRECTORY_SEPARATOR . 'uploaded-files';

    // Set options via properties.
    if (isset($_POST['allowed_extension'])) {
        $Upload->allowed_file_extensions = array('gif', 'jpg', 'jpeg', 'png', 'txt');
    }
    if (isset($_POST['new_file_name'])) {
        $Upload->new_file_name = htmlspecialchars_decode(trim($_POST['new_file_name']));
    }
    if (isset($_POST['upload_max_file_size'])) {
        $Upload->max_file_size = intval($_POST['upload_max_file_size']);
    }
    if (isset($_POST['overwrite'])) {
        $Upload->overwrite = ($_POST['overwrite'] === 'true' ? true : false);
    }
    if (isset($_POST['web_safe_file_name'])) {
        $Upload->web_safe_file_name = ($_POST['web_safe_file_name'] === 'true' ? true : false);
    }
    if (isset($_POST['security_scan'])) {
        $Upload->security_scan = ($_POST['security_scan'] === 'true' ? true : false);
    }

    $Upload->externalSecurityScan = array($MyAVS, 'doScan');

    // Begins upload process.
    $upload_result = $Upload->upload();
    $uploaded_data = $Upload->getUploadedData();
}// endif; method post.

unset($MyAVS);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test security scan using ClamAV.</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>Test security scan using ClamAV.</h1>
                    <?php if (isset($upload_result) && $upload_result === true) { ?> 
                    <div class="alert alert-success">Upload successfully.</div>
                    <?php }// endif upload result ?> 
                    <?php if (isset($uploaded_data) && !empty($uploaded_data)) { ?> 
                    <div class="alert alert-info">
                        <h3>Uploaded files data:</h3>
                        <pre><?php echo htmlspecialchars(stripslashes(var_export($uploaded_data, true))); ?></pre>
                    </div>
                    <?php }// endif; ?> 
                    <?php if (!empty($Upload->error_messages) && is_array($Upload->error_messages)) { ?> 
                    <div class="alert alert-danger">
                        <?php 
                        foreach ($Upload->error_messages as $error_message) {
                            echo '<p>'.$error_message.'</p>'."\n";
                        }// endforeach;
                        unset($error_message);
                        ?> 
                    </div>
                    <?php }// endif show errors ?> 
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 col-sm-offset-4">
                    <form method="post" enctype="multipart/form-data">
                        <?php $new_file_name = uniqid().' '.rand(0, 999).'-= !@#$%^&*()_+ []\\ {}| ;\' :" ,./ <>? `~'; ?> 
                        <input type="hidden" name="new_file_name" value="<?php echo htmlspecialchars($new_file_name); ?>">
                        <input type="hidden" name="overwrite" value="false">
                        <input type="hidden" name="web_safe_file_name" value="true">
                        <input type="hidden" name="security_scan" value="true">
                        <input type="file" name="filename">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                    <dl class="dl-horizontal">
                        <dt>Allowed extension</dt>
                        <dd>GIF, JPG, JPEG, PNG, TXT</dd>
                        <dt>New file name</dt>
                        <dd><?php echo htmlspecialchars($new_file_name); ?></dd>
                        <dt>Overwrite</dt>
                        <dd>false</dd>
                        <dt>Web safe file name</dt>
                        <dd>true</dd>
                        <dt>Security scan</dt>
                        <dd>true</dd>
                    </dl>
                </div>
            <div class="row">
                <div class="col-xs-12">
                    <a href="clear-uploaded-files.php" class="btn btn-danger" onclick="return confirm('Are you sure to delete all uploaded files?');">Clear all uploaded files.</a>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <h2>Debug:</h2>
                    <p>
                        <strong>upload_max_filesize</strong> limit is <?php echo ini_get('upload_max_filesize'); ?><br>
                        <strong>post_max_size</strong> limit is <?php echo ini_get('post_max_size'); ?> 
                    </p>
                    <?php if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && isset($Upload)) { ?> 
                    <div class="alert alert-info">
                        <h3>Upload debug:</h3>
                        <pre><?php echo htmlspecialchars(stripslashes(var_export($Upload, true))); ?></pre>
                    </div>
                    <?php }// endif; method post and isset $Upload. ?> 
                </div>
            </div>
        </div>
    </body>
</html>
<?php
unset($new_file_name, $Upload);