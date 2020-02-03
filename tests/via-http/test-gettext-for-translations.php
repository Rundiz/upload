<?php
require __DIR__.DIRECTORY_SEPARATOR.'includes.php';

$Upload = new \Rundiz\Upload\Upload('');
$predefinedErrorMessages = $Upload->predefinedErrorMessages;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test get text for translations.</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>Test get text for translations.</h1>
                    <p>Copy &amp; paste these codes in your PHP file and use translation program such as Poedit to grab and translate them.</p>
                    <pre><?php 
                    if (isset($predefinedErrorMessages) && is_array($predefinedErrorMessages)) {
                        foreach ($predefinedErrorMessages as $key => $errorMessage) {
                            echo 'noop__(\'' . $errorMessage . '\');' . PHP_EOL;
                        }// endforeach;
                        unset($key, $errorMessage);
                    }// endif; 
                    ?></pre>
                </div>
            </div>
        </div>
    </body>
</html>
<?php
unset($file_info, $Upload);