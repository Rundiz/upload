# Upload Component

PHP Upload.<br>
Upload single or multiple files with validations. (allowed file extensions, matched mime type, max file size, max image dimensions, security scan, reserved file name, safe for web file name).

[![Latest Stable Version](https://poser.pugx.org/rundiz/upload/v/stable)](https://packagist.org/packages/rundiz/upload)
[![License](https://poser.pugx.org/rundiz/upload/license)](https://packagist.org/packages/rundiz/upload)
[![Total Downloads](https://poser.pugx.org/rundiz/upload/downloads)](https://packagist.org/packages/rundiz/upload)

## Example:

upload.php

```php
// You have to include/require files if you did not install it via Composer.
require_once __DIR__.DIRECTORY_SEPARATOR.'Rundiz'.DIRECTORY_SEPARATOR.'Upload'.DIRECTORY_SEPARATOR.'Upload.php';

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    $Upload = new \Rundiz\Upload\Upload('filename');
    $Upload->move_uploaded_to = '/path/to/your/uploaded-files';
    // Allowed for gif, jpg, png
    $Upload->allowed_file_extensions = array('gif', 'jpg', 'jpeg', 'png');
    // Max file size is 900KB.
    $Upload->max_file_size = 900000;
    // Max image dimensions (width, height) in pixels. The array values must be integer only.
    // Please note that this cannot check all uploaded files correctly. For example: You allowed to upload txt and jpg, the txt file will be pass validated for max dimension. To make it more precise, please check it again file by file after move uploaded files are completed done.
    $Upload->max_image_dimensions = array(1280, 720);
    // You can name the uploaded file to new name or leave this to use its default name. Do not included extension into it.
    $Upload->new_file_name = 'new-uploaded-name';
    // Overwrite existing file? true = yes, false = no
    $Upload->overwrite = false;
    // Web safe file name is English, number, dash, underscore.
    $Upload->web_safe_file_name = true;
    // Scan for embedded php or perl language?
    $Upload->security_scan = true;
    // If you upload multiple files, do you want it to be stopped if error occur? (Set to false will skip the error files).
    $Upload->stop_on_failed_upload_multiple = false;

    // Begins upload
    $upload_result = $Upload->upload();
    // Get the uploaded file's data.
    $uploaded_data = $Upload->getUploadedData();

    if ($upload_result === true) {
        echo '<p>Upload successfully.</p>';
    }
    if (is_array($uploaded_data) && !empty($uploaded_data)) {
        echo '<pre>'.htmlspecialchars(stripslashes(var_export($uploaded_data, true))).'</pre>';
    }

    // To check for the errors.
    if (is_array($Upload->error_messages) && !empty($Upload->error_messages)) {
        echo '<h3>Error!</h3>';
        foreach ($Upload->error_messages as $error_message) {
            echo '<p>'.$error_message.'</p>'."\n";
        }// endforeach;
    }

    // To check for the errors and use your own text. (new in 2.0.1).
    if (is_array($Upload->error_codes) && !empty($Upload->error_codes)) {
       foreach ($Upload->error_codes as $errIndex => $errItem) {
           if (isset($errItem['code'])) {
               switch ($errItem['code']) {
                   case 'RDU_1':
                       echo 'You have uploaded the file that is larger than limit.';
                       break;
                   case 'RDU_xxx':
                       // See more in error_codes property to see its array format and all available error codes.
                       break;
               }// endswitch;
           }
       }// endforeach;
   }
}
```

test-upload-form.php

```html
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test file upload.</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <form method="post" enctype="multipart/form-data" action="upload.php">
            <input type="file" name="filename[]" multiple>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        <!--
        If you want to upload single file, use this input form
        <input type="file" name="filename">
        -->
    </body>
</html>
```

### Example results of `getUploadData()`
The uploaded files and moved successfully will be result in array with these key => value format.

```php
array (
    0 => 
    array (
        'name' => '2016-01-23_00001.jpg',
        'extension' => 'jpg',
        'size' => 599923,
        'new_name' => '2016-01-23_00001.jpg',
        'full_path_new_name' => '/path/to/your/uploaded-files/2016-01-23_00001.jpg',
        'mime' => 'image/jpeg',
        'md5_file' => 'c18b22a64cc71e1b1dfc930009e5f970',
    ),
    1 => 
    array (
        'name' => '2016-01-24_00001.jpg',
        'extension' => 'jpg',
        'size' => 260488,
        'new_name' => '2016-01-24_00001.jpg',
        'full_path_new_name' => '/path/to/your/uploaded-files/2016-01-24_00001.jpg',
        'mime' => 'image/jpeg',
        'md5_file' => 'a1b2ac1f19949d22ad02c37545d5285f',
    ),
)
```

More example is in tests folder.
