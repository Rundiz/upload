<?php
require __DIR__.DIRECTORY_SEPARATOR.'includes.php';

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    $Upload = new \Rundiz\Upload\Upload('filename');
    $Upload->move_uploaded_to = __DIR__.DIRECTORY_SEPARATOR.'uploaded-files';

    foreach ($_POST as $key => $value) {
        $$key = $value;
    }
    unset($key, $value);

    // Set options via properties.
    $Upload->allowed_file_extensions = array('gif', 'jpg', 'jpeg', 'png');
    if (isset($_POST['upload_max_file_size']) && $_POST['upload_max_file_size'] != null) {
        $Upload->max_file_size = intval($_POST['upload_max_file_size']);
    }
    if (isset($max_image_dimensions) && !empty($max_image_dimensions)) {
        $exp_max_image_dimensions = explode(',', $max_image_dimensions);
        if (is_array($exp_max_image_dimensions) && count($exp_max_image_dimensions) >= 2 && is_numeric($exp_max_image_dimensions[0]) && is_numeric($exp_max_image_dimensions[1])) {
            $Upload->max_image_dimensions = array(intval($exp_max_image_dimensions[0]), intval($exp_max_image_dimensions[1]));
        }
        unset($exp_max_image_dimensions);
    }
    if (isset($_POST['new_file_name'])) {
        $Upload->new_file_name = htmlspecialchars_decode(trim($_POST['new_file_name']));
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
    if (isset($_POST['stop_on_failed_upload_multiple'])) {
        $Upload->stop_on_failed_upload_multiple = ($_POST['stop_on_failed_upload_multiple'] === 'true' ? true : false);
    }

    // Begins upload process.
    $upload_result = $Upload->upload();
    $uploaded_data = $Upload->getUploadedData();
}// endif; method post.


if (!isset($overwrite)) {
    $overwrite = 'false';
}
if (!isset($web_safe_file_name)) {
    $web_safe_file_name = 'true';
}
if (!isset($security_scan)) {
    $security_scan = 'false';
}
if (!isset($stop_on_failed_upload_multiple)) {
    $stop_on_failed_upload_multiple = 'true';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test full customization file upload.</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>Test full customization file upload.</h1>
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
                <div class="col-sm-6">
                    <h2>Upload single file</h2>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Select file</label>
                            <input type="file" name="filename">
                            <p class="help-block">Allowed extensions: GIF, JPG, PNG</p>
                        </div>
                        <div class="form-group has-feedback">
                            <label>Max file size</label>
                            <div class="input-group">
                                <input type="text" name="upload_max_file_size" value="<?php if (isset($upload_max_file_size)) {echo htmlspecialchars($upload_max_file_size);} ?>" class="form-control">
                                <span class="input-group-addon">Bytes</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Max image dimensions</label>
                            <input type="text" name="max_image_dimensions" value="<?php if (isset($max_image_dimensions)) {echo htmlspecialchars($max_image_dimensions);} ?>" placeholder="width,height" class="form-control">
                            <p class="help-block">Max image dimensions must be 2 numbers separate by comma. Example: 500,300 is width 500 and height 300 pixels.</p>
                        </div>
                        <div class="form-group">
                            <label>New file name (without file .extension)</label>
                            <input type="text" name="new_file_name" value="<?php if (isset($new_file_name)) {echo htmlspecialchars($new_file_name);} ?>" class="form-control">
                            <p class="help-block">For file name limitation test, I suggest you to name this to <strong><?php echo htmlspecialchars('TEST -= !@#$%^&*()_+ []\\ {}| ;\' :" ,./ <>? `~'); ?></strong></p>
                        </div>
                        <div class="form-group">
                            <label>Overwrite existing file</label>
                            <select name="overwrite" class="form-control">
                                <option value="true"<?php if (isset($overwrite) && $overwrite === 'true') { ?> selected<?php } ?>>Yes</option>
                                <option value="false"<?php if (isset($overwrite) && $overwrite === 'false') { ?> selected<?php } ?>>No</option>
                            </select>
                            <p class="help-block">If you set new file name and upload multiple file, please set this to no.</p>
                        </div>
                        <div class="form-group">
                            <label>Web safe file name</label>
                            <select name="web_safe_file_name" class="form-control">
                                <option value="true"<?php if (isset($web_safe_file_name) && $web_safe_file_name === 'true') { ?> selected<?php } ?>>Yes</option>
                                <option value="false"<?php if (isset($web_safe_file_name) && $web_safe_file_name === 'false') { ?> selected<?php } ?>>No</option>
                            </select>
                            <p class="help-block">English and number chacters, no space (replaced with dash), no special characters, allowed dash and underscore.</p>
                        </div>
                        <div class="form-group">
                            <label>Security scan</label>
                            <select name="security_scan" class="form-control">
                                <option value="true"<?php if (isset($security_scan) && $security_scan === 'true') { ?> selected<?php } ?>>Yes</option>
                                <option value="false"<?php if (isset($security_scan) && $security_scan === 'false') { ?> selected<?php } ?>>No</option>
                            </select>
                            <p class="help-block">Scan for PHP and perl code in the file.</p>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
                <div class="col-sm-6">
                    <h2>Upload multiple file</h2>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Select file</label>
                            <input type="file" name="filename[]" multiple>
                            <p class="help-block">Allowed extensions: GIF, JPG, PNG</p>
                        </div>
                        <div class="form-group has-feedback">
                            <label>Max file size</label>
                            <div class="input-group">
                                <input type="text" name="upload_max_file_size" value="<?php if (isset($upload_max_file_size)) {echo htmlspecialchars($upload_max_file_size);} ?>" class="form-control">
                                <span class="input-group-addon">Bytes</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Max image dimensions</label>
                            <input type="text" name="max_image_dimensions" value="<?php if (isset($max_image_dimensions)) {echo htmlspecialchars($max_image_dimensions);} ?>" placeholder="width,height" class="form-control">
                            <p class="help-block">Max image dimensions must be 2 numbers separate by comma. Example: 500,300 is width 500 and height 300 pixels.</p>
                        </div>
                        <div class="form-group">
                            <label>New file name (without file .extension)</label>
                            <input type="text" name="new_file_name" value="<?php if (isset($new_file_name)) {echo htmlspecialchars($new_file_name);} ?>" class="form-control">
                            <p class="help-block">For file name limitation test, I suggest you to name this to <strong><?php echo htmlspecialchars('TEST -= !@#$%^&*()_+ []\\ {}| ;\' :" ,./ <>? `~'); ?></strong></p>
                        </div>
                        <div class="form-group">
                            <label>Overwrite existing file</label>
                            <select name="overwrite" class="form-control">
                                <option value="true"<?php if (isset($overwrite) && $overwrite === 'true') { ?> selected<?php } ?>>Yes</option>
                                <option value="false"<?php if (isset($overwrite) && $overwrite === 'false') { ?> selected<?php } ?>>No</option>
                            </select>
                            <p class="help-block">If you set new file name and upload multiple file, please set this to no.</p>
                        </div>
                        <div class="form-group">
                            <label>Web safe file name</label>
                            <select name="web_safe_file_name" class="form-control">
                                <option value="true"<?php if (isset($web_safe_file_name) && $web_safe_file_name === 'true') { ?> selected<?php } ?>>Yes</option>
                                <option value="false"<?php if (isset($web_safe_file_name) && $web_safe_file_name === 'false') { ?> selected<?php } ?>>No</option>
                            </select>
                            <p class="help-block">English and number chacters, no space (replaced with dash), no special characters, allowed dash and underscore.</p>
                        </div>
                        <div class="form-group">
                            <label>Security scan</label>
                            <select name="security_scan" class="form-control">
                                <option value="true"<?php if (isset($security_scan) && $security_scan === 'true') { ?> selected<?php } ?>>Yes</option>
                                <option value="false"<?php if (isset($security_scan) && $security_scan === 'false') { ?> selected<?php } ?>>No</option>
                            </select>
                            <p class="help-block">Scan for PHP and perl code in the file.</p>
                        </div>
                        <div class="form-group">
                            <label>Stop on failed upload occur</label>
                            <select name="stop_on_failed_upload_multiple" class="form-control">
                                <option value="true"<?php if (isset($stop_on_failed_upload_multiple) && $stop_on_failed_upload_multiple === 'true') { ?> selected<?php } ?>>Yes</option>
                                <option value="false"<?php if (isset($stop_on_failed_upload_multiple) && $stop_on_failed_upload_multiple === 'false') { ?> selected<?php } ?>>No</option>
                            </select>
                            <p class="help-block">If you upload multiple file and there is at least one error, do you want it to stop or not?.</p>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <hr>
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
                        <pre><?php 
                        echo htmlspecialchars(stripslashes(var_export($Upload, true))); 
                        echo "\n\n";
                        echo htmlspecialchars(print_r($_FILES, true));
                        ?></pre>
                    </div>
                    <?php }// endif; method post and isset $Upload. ?> 
                </div>
            </div>
        </div>
    </body>
</html>
<?php
unset($new_file_name, $Upload);