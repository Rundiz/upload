<?php
/**
 * Rundiz Upload component.
 * 
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\Upload;

/**
 * PHP upload class that is able to validate requirements and limitations, real file's mime type check, detect the errors and report.
 *
 * @package Upload
 * @version 2.0.4
 * @author Vee W.
 */
class Upload
{


    /**
     * @var array Allowed file extensions. Example: array('jpg', 'gif', 'png').
     */
    public $allowed_file_extensions;

    /**
     * @var array|null The array set of file extensions and its valid mime types for check when process the uploaded files.<br>
     * Example:
     * <pre>
     * array(
     *     'jpg' => array('image/jpeg', 'image/pjpeg'),
     *     'txt' => array('text/plain'),
     * );
     * </pre>
     * If you don't want to validate mime type, set this property to an empty array. Example: `$Upload->file_extensions_mime_types = array();`
     * Set to NULL to use default mime types in `setupFileExtensionsMimeTypesForValidation()` method.
     */
    public $file_extensions_mime_types;

    /**
     * @var array Contain all values from $_FILES['input_file_name'] for works with upload process. This will be very useful when upload multiple files.<br>
     * Example:<br>
     * <code>$Upload->files['input_file_name'];</code> is same as <code>$_FILES['input_file_name']</code>
     */
    protected $files = array();

    /**
     * @var string The input file name. ($_FILES['input_file_name']).
     */
    protected $input_file_name;

    /**
     * @var integer Set max file size to upload. This file size unit is in bytes only.
     */
    public $max_file_size;

    /**
     * @var array The array of max image width and height. The value must be array of width, height by order and the number must be integer. Example: `array(500, 300)` mean width 500 pixels and height 300 pixels. Set to empty array for not validate.
     */
    public $max_image_dimensions = array();

    /**
     * @var array The queue for move uploaded file(s). This is very useful when upload multiple files.
     */
    protected $move_uploaded_queue = array();

    /**
     * @var string Path to store files that was uploaded to move to. Do not end with trailing slash.
     */
    public $move_uploaded_to = '.';

    /**
     * @var string Set new file name, just set the file name only. No extension.<br>
     * Important! This property is not recommend to set it if you upload multiple files with same input file name. It is recommended to leave this as null and set overwrite property to true or false.<br>
     * If you want to set the name while upload multiple files, it is recommended that you set overwrite property to false.
     */
    public $new_file_name;

    /**
     * @var boolean To overwrite the uploaded file set it to true, otherwise set it to false.
     */
    public $overwrite = false;

    /**
     * @var boolean To rename uploaded file name to safe for web set it to true, otherwise set it to false.<br>
     * The safe for web file name is English and number chacters, no space (replaced with dash), no special characters, allowed dash and underscore.
     */
    public $web_safe_file_name = true;

    /**
     * @var boolean Set to true to enable security scan such as php open tag (<?php). Set to false to not scan. This is optional security.
     */
    public $security_scan = false;

    /**
     * @var boolean If you upload multiple files and there is at least one file that did not pass the validation, do you want it to stop?<br>
     * Set to true to stop and delete all uploaded files (all uploaded files must pass validation).<br>
     * Set to false to skip the error files (failed validation files are report as error, success validation files continue the process).
     */
    public $stop_on_failed_upload_multiple = true;

    /**
     * Contain error codes.
     * 
     * The array format will be:
     * <pre>
     * array(
     *     index => array(// the `index` will be the same as it is in error_messages property.
     *         'code' => 'RDU_1',// this will be error code, start with RDU_ and follow with number or short error message without space. it is easy for check and replace with your translation.
     *         'errorAttributes' => 'string',// this key contain error attributes as string 
     *             // for example: 9MB > 2MB in case that limit file size to 2MB but uploaded 9MB, or showing file name that have problem.
     *             // this key may contain empty string so check it before use.
     *         'errorFileName' => 'filename.ext',// the file name with extension that cause error message.
     *             // this key may contain empty string.
     *         'errorFileSize' => '12345',// the file size in bytes.
     *             // this key may contain empty string.
     *         'errorFileMime' => 'mime/type',// the file mime type.
     *             // this key may contain empty string.
     *     )
     * )
     * </pre>
     * 
     * The error codes and description.
     * 
     * RDU_MOVE_UPLOADED_FAILED = Failed to move uploaded file.<br>
     * RDU_SEC_ERR_PHP = Security error! Found PHP embedded in the uploaded file.<br>
     * RDU_SEC_ERR_CGI = Security error! Found CGI/Pearl embedded in the uploaded file.<br>
     * RDU_MOVE_UPLOADED_TO_NOT_DIR = The target upload location is not folder.<br>
     * RDU_MOVE_UPLOADED_TO_NOT_WRITABLE = The target upload location is not writable. Please check folder permission.<br>
     * RDU_UNABLE_VALIDATE_EXT = Unable to validate file extension.<br>
     * RDU_NOT_ALLOW_EXT = The uploaded file is not in allowed extensions.<br>
     * RDU_UNABLE_VALIDATE_EXT_AND_MIME = Unable to validate file extension and mime type.<br>
     * RDU_INVALID_MIME = The uploaded file has invalid mime type.<br>
     * RDU_UNABLE_VALIDATE_MIME = Unable to validate mime type.<br>
     * RDU_IMG_DIMENSION_OVER_MAX = The uploaded image file dimensions are larger than max dimensions allowed.<br>
     * RDU_IMG_NO_OR_MULTIPLE_IMAGES = The uploaded file may contain no image or multiple image. Reference: ( http://php.net/getimagesize ).
     * 
     * For the RDU_1 to RDU_8 use PHP upload errors. ( http://php.net/manual/en/features.file-upload.errors.php ).<br>
     * RDU_1 = The uploaded file exceeds the max file size directive.<br>
     * RDU_2 = The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.<br>
     * RDU_3 = The uploaded file was only partially uploaded.<br>
     * RDU_4 = No file was uploaded.<br>
     * RDU_6 = Missing a temporary folder.<br>
     * RDU_7 = Failed to write file to disk.<br>
     * RDU_8 = A PHP extension stopped the file upload.<br>
     * 
     * @since 2.0.1
     * @var array If there is at least one error, it will be set to here.
     */
    public $error_codes = array();

    /**
     * @var array If there is at least one error message it will be set to here.
     */
    public $error_messages = array();


    /**
     * Begins upload class.
     * 
     * @param string $input_file_name The name of input file.
     */
    public function __construct($input_file_name)
    {
        $this->clear();
        $this->setInputFileName($input_file_name);
    }// __construct


    /**
     * Class destructor. Works at end of class (unset class's variable).
     */
    public function __destruct()
    {
        $this->clear();
    }// __destruct


    /**
     * Placeholder method for language editor program like Poedit to lookup for the words that is using this method.<br>
     * This method does nothing but for who use program like Poedit to search/lookup the words that is using this method to create translation.<br>
     * Example:<br>
     * There is this code in generator class. <code>static::__('Hello');</code><br>
     * Use Poedit to search for __ function to update/retreive the source text and translate it.
     * 
     * @param string $string The message to use.
     * @return string Return the same string.
     */
    protected static function __($string)
    {
        return $string;
    }// __


    /**
     * Clear all properties to its default values.
     */
    public function clear()
    {
        $this->allowed_file_extensions = null;
        $this->error_messages = array();
        $this->file_extensions_mime_types = null;
        $this->files = array();
        $this->input_file_name = null;
        $this->max_file_size = null;
        $this->max_image_dimensions = array();
        $this->move_uploaded_queue = array();
        $this->move_uploaded_to = '.';
        $this->new_file_name = null;
        $this->overwrite = false;
        $this->web_safe_file_name = true;
        $this->security_scan = false;
        $this->stop_on_failed_upload_multiple = true;
    }// clear


    /**
     * Clear uploaded files at temp folder. (if it is able to write/delete).
     */
    protected function clearUploadedAtTemp()
    {
        foreach ($this->move_uploaded_queue as $key => $queue_item) {
            if (is_array($queue_item) && isset($queue_item['tmp_name'])) {
                if (is_file($queue_item['tmp_name']) && is_writable($queue_item['tmp_name'])) {
                    unlink($queue_item['tmp_name']);
                }
            }
        }// endforeach;
        unset($key, $queue_item);
        $this->move_uploaded_queue = array();
    }// clearUploadedAtTemp


    /**
     * Get the uploaded data.
     * 
     * @return array Return array set of successful uploaded files and its data.<br>
     * Example:
     * <pre>
     * $output = array(
     *     'input_file_name_key' => array(
     *         'name' => 'file_name_where_user_selected_in_the_upload_form.ext',
     *         'extension' => 'ext',
     *         'size' => 'file size in bytes.',
     *         'new_name' => 'new_file_name_that_was_set_while_upload_process.ext',
     *         'full_path_new_name' => '/full/move_uploaded_path/to/new_file_name_that_was_set_while_upload_process.ext',
     *         'mime' => 'The real file mime type',
     *         'md5_file' => 'The md5 file value.',
     *     ),
     *     'other_input_file_name_key' => array(
     *         'name' => '...',
     *         'extension' => '...',
     *         'size' => '...',
     *         'new_name' => '...',
     *         'full_path_new_name' => '...',
     *         'mime' => '...',
     *         'md5_file' => '...',
     *     ),
     * );
     * </pre>
     * If failed to upload, it will return empty array.
     */
    public function getUploadedData()
    {
        if (empty($this->move_uploaded_queue) || !is_array($this->move_uploaded_queue)) {
            return array();
        }

        $output = array();

        foreach ($this->move_uploaded_queue as $key => $queue_item) {
            if (
                is_array($queue_item) && 
                array_key_exists('name', $queue_item) &&
                array_key_exists('tmp_name', $queue_item) &&
                array_key_exists('new_name', $queue_item) &&
                array_key_exists('move_uploaded_to', $queue_item) &&
                array_key_exists('move_uploaded_status', $queue_item) && 
                $queue_item['move_uploaded_status'] === 'success'
            ) {
                // get file extension only
                $file_name_explode = explode('.', $queue_item['name']);
                $file_extension = (isset($file_name_explode[count($file_name_explode)-1]) ? $file_name_explode[count($file_name_explode)-1] : null);
                unset($file_name_explode);

                // get file info
                $Finfo = new \finfo();
                $mime = $Finfo->file($queue_item['move_uploaded_to'], FILEINFO_MIME_TYPE);
                unset($Finfo);

                $output[$key] = array();
                $output[$key]['name'] = $queue_item['name'];
                $output[$key]['extension'] = $file_extension;
                $output[$key]['size'] = (is_file($queue_item['move_uploaded_to']) ? filesize($queue_item['move_uploaded_to']) : 0);
                $output[$key]['new_name'] = $queue_item['new_name'];
                $output[$key]['full_path_new_name'] = $queue_item['move_uploaded_to'];
                $output[$key]['mime'] = $mime;
                $output[$key]['md5_file'] = (is_file($queue_item['move_uploaded_to']) ? md5_file($queue_item['move_uploaded_to']) : null);

                unset($file_extension, $mime);
            }
        }

        return $output;
    }// getUploadedData


    /**
     * Move the uploaded file(s).
     * 
     * @return boolean Return true on success, false on failure.
     */
    protected function moveUploadedFiles()
    {
        $i = 0;
        if (is_array($this->move_uploaded_queue)) {
            foreach ($this->move_uploaded_queue as $key => $queue_item) {
                if (is_array($queue_item) && isset($queue_item['name']) && isset($queue_item['tmp_name']) && isset($queue_item['new_name'])) {
                    $destination_name = $queue_item['new_name'];

                    if ($this->overwrite === false) {
                        // verify file exists and set new name.
                        $destination_name = $this->renameDuplicateFile($destination_name);
                    }

                    $move_result = move_uploaded_file($queue_item['tmp_name'], $this->move_uploaded_to.DIRECTORY_SEPARATOR.$destination_name);
                    if ($move_result === true) {
                        // move uploaded file success. add status and some data to array.
                        $this->move_uploaded_queue[$key] = array_merge(
                            $this->move_uploaded_queue[$key], 
                            array(
                                'new_name' => $destination_name,
                                'move_uploaded_status' => 'success',
                                'move_uploaded_to' => $this->move_uploaded_to.DIRECTORY_SEPARATOR.$destination_name,
                            )
                        );
                        $i++;
                    } else {
                        $this->setErrorMessage(
                            sprintf(static::__('Unable to move uploaded file. (%s =&gt; %s)'), $queue_item['name'], $this->move_uploaded_to . DIRECTORY_SEPARATOR . $destination_name),
                            'RDU_MOVE_UPLOADED_FAILED',
                            $queue_item['name'] . '=&gt; ' . $this->move_uploaded_to . DIRECTORY_SEPARATOR . $destination_name
                        );
                    }

                    unset($destination_name, $move_result);
                }
            }// endforeach;
            unset($key, $queue_item);
        }

        if ($i == count($this->move_uploaded_queue) && $i > 0) {
            return true;
        } else {
            return false;
        }
    }// moveUploadedFiles


    /**
     * Rename the file where it is duplicate with existing file.
     * 
     * @param string $file_name File name to check
     * @return string Return renamed file that will not duplicate the existing file.
     */
    protected function renameDuplicateFile($file_name, $loop_count = 1)
    {
        if (!file_exists($this->move_uploaded_to.DIRECTORY_SEPARATOR.$file_name)) {
            return $file_name;
        } else {
            $file_name_explode = explode('.', $file_name);
            $file_extension = (isset($file_name_explode[count($file_name_explode)-1]) ? $file_name_explode[count($file_name_explode)-1] : null);
            unset($file_name_explode[count($file_name_explode)-1]);
            $file_name_only = implode('.', $file_name_explode);
            unset($file_name_explode);

            $i = 1;
            $found = true;
            do {
                $new_file_name = $file_name_only.'_'.$i.'.'.$file_extension;
                if (file_exists($this->move_uploaded_to.DIRECTORY_SEPARATOR.$new_file_name)) {
                    $found = true;
                    if ($i > 1000) {
                        // too many loop
                        $file_name = uniqid().'-'.str_replace('.', '', microtime(true));
                        $found = false;
                    }
                } else {
                    $file_name = $new_file_name;
                    $found = false;
                }
                $i++;
            } while ($found === true);

            unset($file_extension, $file_name_only, $new_file_name);
            return $file_name;
        }
    }// renameDuplicateFile


    /**
     * Security scan. Scan for such as embedded php code in the uploaded file.
     * 
     * @return boolean Return true on safety, return false for otherwise.
     */
    protected function securityScan()
    {
        if (
            is_array($this->files[$this->input_file_name]) && 
            array_key_exists('name', $this->files[$this->input_file_name]) && 
            array_key_exists('tmp_name', $this->files[$this->input_file_name]) && 
            $this->files[$this->input_file_name]['tmp_name'] != null
        ) {
            // there is an uploaded file.
            if (is_file($this->files[$this->input_file_name]['tmp_name'])) {
                $file_content = file_get_contents($this->files[$this->input_file_name]['tmp_name']);

                // scan php open tag
                if (stripos($file_content, '<?php') !== false || stripos($file_content, '<?=') !== false) {
                    // found php open tag. (<?php).
                    $this->setErrorMessage(
                        sprintf(static::__('Error! Found php embedded in the uploaded file. (%s).'), $this->files[$this->input_file_name]['name']),
                        'RDU_SEC_ERR_PHP',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                }

                // scan cgi/perl
                if (stripos($file_content, '#!/') !== false && stripos($file_content, '/perl') !== false) {
                    // found cgi/perl header.
                    $this->setErrorMessage(
                        sprintf(static::__('Error! Found cgi/perl embedded in the uploaded file. (%s).'), $this->files[$this->input_file_name]['name']),
                        'RDU_SEC_ERR_CGI',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                }

                // scan shell script
                // reference: https://en.wikipedia.org/wiki/Shell_script 
                // https://stackoverflow.com/questions/10591086/shell-script-headers-bin-sh-vs-bin-csh
                // https://www.shellscript.sh/
                if (
                    stripos($file_content, '#!/') !== false && 
                    (
                        stripos($file_content, '/bin/sh') !== false ||
                        stripos($file_content, '/bin/bash') !== false ||
                        stripos($file_content, '/bin/csh') !== false ||
                        stripos($file_content, '/bin/tcsh') !== false
                    )
                ) {
                    // found shell script.
                    $this->setErrorMessage(
                        sprintf(static::__('Error! Found shell script embedded in the uploaded file. (%s).'), $this->files[$this->input_file_name]['name']),
                        'RDU_SEC_ERR_CGI',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                }

                unset($file_content);
            }
        }

        return true;
    }// securityScan


    /**
     * Set the error message into error_messages and error_codes properties.
     * 
     * @since 2.0.1
     * @param string $error_messages The error message.
     * @param string $code The error code, start with RDU_ and follow with number or short error message without space.
     * @param string $errorAttributes Error attributes. For example: 9MB > 2MB in case that limit file size to 2MB but uploaded 9MB, or showing file name that have problem.
     * @param string $errorFileName The file name with extension.
     * @param string $errorFileSize The file size in bytes.
     * @param string $errorFileMime The file mime type.
     */
    protected function setErrorMessage(
        $error_messages, 
        $code, 
        $errorAttributes = '', 
        $errorFileName = '', 
        $errorFileSize = '', 
        $errorFileMime = ''
    ) {
        $arg_list = func_get_args();
        $numargs = func_num_args();
        for ($i = 0; $i < $numargs; $i++) {
            if (is_array($arg_list) && array_key_exists($i, $arg_list) && !is_scalar($arg_list[$i])) {
                return false;
            } elseif ($arg_list === false) {
                return false;
            }
        }
        unset($arg_list, $i, $numargs);

        $this->error_messages[] = $error_messages;
        $this->error_codes[] = array(
            'code' => $code,
            'errorAttributes' => $errorAttributes,
            'errorFileName' => $errorFileName,
            'errorFileSize' => $errorFileSize,
            'errorFileMime' => $errorFileMime,
        );
    }// setErrorMessage


    /**
     * Set input file name.<br>
     * If you begins new class object then you don't have to call this method. You must call this method after called to the clear() method.<br>
     * Or you can call this method in case that you want to process the other uploaded file next to previous one.
     * 
     * @param string $input_file_name The name of input file.
     */
    public function setInputFileName($input_file_name)
    {
        if (!is_scalar($input_file_name)) {
            throw new \InvalidArgumentException(static::__('The input file name must be string.'));
        }

        $this->input_file_name = $input_file_name;
    }// setInputFileName


    /**
     * Set the new file name if it was not set, check for reserved file name and removed those characters.
     * 
     * @link http://windows.microsoft.com/en-us/windows/file-names-extensions-faq#1TC=windows-7 Windows file name FAQ.
     * @link https://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx?f=255&MSPPError=-2147217396 Windows reserved file name.
     * @link https://en.wikipedia.org/wiki/Filename Global reserved file name.
     */
    protected function setNewFileName()
    {
        $this->new_file_name = trim($this->new_file_name);

        if ($this->new_file_name == null) {
            // if new file name was not set, set it from uploaded file name.
            if (is_array($this->files[$this->input_file_name]) && array_key_exists('name', $this->files[$this->input_file_name])) {
                $file_name_explode = explode('.', $this->files[$this->input_file_name]['name']);
                unset($file_name_explode[count($file_name_explode)-1]);
                $this->new_file_name = implode('.', $file_name_explode);
                unset($file_name_explode);
            } else {
                $this->setNewFileNameToRandom();
            }
        }

        // do not allow name that contain one of these characters.
        $reserved_characters = array('\\', '/', '?', '%', '*', ':', '|', '"', '<', '>', '!', '@');
        $this->new_file_name = str_replace($reserved_characters, '', $this->new_file_name);
        unset($reserved_characters);

        if (preg_match('#[^\.]+#iu', $this->new_file_name) == 0) {
            // found the name is only dots. example ., .., ..., ....
            $this->setNewFileNameToRandom();
        }

        // reserved words or reserved names. do not allow if new name is set to one of these words or names.
        // make it case in-sensitive.
        $reserved_words = array(
            'CON', 'PRN', 'AUX', 'CLOCK$', 'NUL', 
            'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 
            'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9',
            'LST', 'KEYBD$', 'SCREEN$', '$IDLE$', 'CONFIG$',
            '$Mft', '$MftMirr', '$LogFile', '$Volume', '$AttrDef', '$Bitmap', '$Boot', '$BadClus', '$Secure',
            '$Upcase', '$Extend', '$Quota', '$ObjId', '$Reparse',
        );
        foreach ($reserved_words as $reserved_word) {
            if (strtolower($reserved_word) == strtolower($this->new_file_name)) {
                $this->setNewFileNameToRandom();
            }
        }
        unset($reserved_word, $reserved_words);

        // in the end if it is still left new name as null... set random name to it.
        if ($this->new_file_name == null) {
            $this->setNewFileNameToRandom();
        }
    }// setNewFileName


    /**
     * Set the new file name to random. (unique id and microtime).
     */
    protected function setNewFileNameToRandom()
    {
        $this->new_file_name = uniqid().'-'.str_replace('.', '', microtime(true));
    }// setNewFileNameToRandom


    /**
     * Setup file extensions mime types for validation. (In case that it was not set).
     */
    protected function setupFileExtensionsMimeTypesForValidation()
    {
        if (!is_array($this->file_extensions_mime_types) && $this->file_extensions_mime_types == null) {
            // extensions mime types was not set and not set to NOT validate (empty array).
            $default_mime_types_file = 'file-extensions-mime-types.php';
            if (is_file(__DIR__.DIRECTORY_SEPARATOR.$default_mime_types_file)) {
                $this->file_extensions_mime_types = include __DIR__.DIRECTORY_SEPARATOR.$default_mime_types_file;
            }
            unset($default_mime_types_file);
        }

        if (is_array($this->file_extensions_mime_types)) {
            // if mime types was set, change the keys to lower case.
            $this->file_extensions_mime_types = array_change_key_case($this->file_extensions_mime_types, CASE_LOWER);
        }

        if (is_array($this->allowed_file_extensions)) {
            // if allowed extensions was set, change the values to lower case.
            $this->allowed_file_extensions = array_map('strtolower', $this->allowed_file_extensions);
        }
    }// setupFileExtensionsMimeTypesForValidation


    /**
     * Set the file name that is safe for web.<br>
     * The safe for web file name is English and number chacters, no space (replaced with dash), no special characters, allowed dash and underscore.
     */
    protected function setWebSafeFileName()
    {
        if ($this->new_file_name == null) {
            $this->setNewFileName();
        }

        // replace multiple spaces to one space.
        $this->new_file_name = preg_replace('#\s+#iu', ' ', $this->new_file_name);
        // replace space to dash.
        $this->new_file_name = str_replace(' ', '-', $this->new_file_name);
        // replace non alpha-numeric to nothing.
        $this->new_file_name = preg_replace('#[^\da-z\-_]#iu', '', $this->new_file_name);
        // replace multiple dashes to one dash.
        $this->new_file_name = preg_replace('#-{2,}#', '-', $this->new_file_name);
    }// setWebSafeFileName


    /**
     * Test get the real file's mime type using finfo_file.<br>
     * This is very useful when you want to add new file extension and mime type to validate uploaded files.
     * 
     * @link http://php.net/manual/en/function.finfo-file.php More info about finfo_file() function.
     * @param string $input_file_name The input file name. This support only one file upload.
     * @return string Return file's mime type or error message.
     */
    public function testGetUploadedMimetype($input_file_name = null)
    {
        if (!is_scalar($input_file_name)) {
            throw new \InvalidArgumentException(static::__('The input file name must be string.'));
        }

        if ($input_file_name == null) {
            $input_file_name = $this->input_file_name;
        }

        if (
            !isset($_FILES[$input_file_name]['name']) || 
            (isset($_FILES[$input_file_name]['name']) && $_FILES[$input_file_name]['name'] == null) || 
            !isset($_FILES[$input_file_name]['tmp_name']) || 
            (isset($_FILES[$input_file_name]['tmp_name']) && $_FILES[$input_file_name]['tmp_name'] == null)
        ) {
            return static::__('You did not upload any file, please upload a file to get info.');
        }

        if (!function_exists('finfo_open') || !function_exists('finfo_file')) {
            return static::__('There is no finfo_open() function or finfo_file() function to get file\'s info. Please verify PHP installation.');
        }

        $output = sprintf(static::__('File name: %s'), $_FILES[$input_file_name]['name']).'<br>'."\n";
        $file_name_exp = explode('.', $_FILES[$input_file_name]['name']);
        $file_extension = $file_name_exp[count($file_name_exp)-1];
        unset($file_name_exp);
        $output .= sprintf(static::__('File extension: %s'), $file_extension).'<br>'."\n";

        $Finfo = new \finfo();
        $file_mimetype = $Finfo->file($_FILES[$input_file_name]['tmp_name'], FILEINFO_MIME_TYPE);
        $output .= sprintf(static::__('Mime type: %s'), $file_mimetype).'<br>'."\n";
        $output .= '<br>'."\n";
        $output .= static::__('The array for use with extension-mime types validation.').'<br>'."\n";
        $output .= 'array(<br>'."\n";
        $output .= '&nbsp; &nbsp; \''.$file_extension.'\' =&gt; array(\''.$file_mimetype.'\'),<br>'."\n";
        $output .= ');'."\n";
        unset($Finfo);

        if (is_writable($_FILES[$input_file_name]['tmp_name'])) {
            unlink($_FILES[$input_file_name]['tmp_name']);
        }

        unset($file_extension, $file_mimetype);
        return $output;
    }// testGetUploadedMimetype


    /**
     * Start the upload and move uploaded files process.
     * 
     * @return boolean Return true on success, false for otherwise. If upload multiple file and there is error only one it return false.
     */
    public function upload()
    {
        // validate that all options properties was properly set to correct type.
        $this->validateOptionsProperties();
        // setup file extensions and mime types for validation. (in case that it was not set).
        $this->setupFileExtensionsMimeTypesForValidation();

        // verify that location where the uploaded file(s) will be moved to is writable.
        if (!is_dir($this->move_uploaded_to)) {
            $this->setErrorMessage(
                static::__('The target location where the uploaded file(s) will be moved to is not folder or directory.'),
                'RDU_MOVE_UPLOADED_TO_NOT_DIR',
                $this->move_uploaded_to
            );
            return false;
        } elseif (is_dir($this->move_uploaded_to) && !is_writable($this->move_uploaded_to)) {
            $this->setErrorMessage(
                static::__('The target location where the uploaded file(s) will be moved to is not writable. Please check the folder permission.'),
                'RDU_MOVE_UPLOADED_TO_NOT_WRITABLE',
                $this->move_uploaded_to
            );
            return false;
        } else {
            // solve the move uploaded to as a real path.
            $this->move_uploaded_to = realpath($this->move_uploaded_to);
        }

        if (isset($_FILES[$this->input_file_name]['name']) && is_array($_FILES[$this->input_file_name]['name'])) {
            // if multiple file upload.
            foreach ($_FILES[$this->input_file_name]['name'] as $key => $value) {
                $this->files[$this->input_file_name]['input_file_key'] = $key;
                $this->files[$this->input_file_name]['name'] = $_FILES[$this->input_file_name]['name'][$key];
                $this->files[$this->input_file_name]['type'] = (isset($_FILES[$this->input_file_name]['type'][$key]) ? $_FILES[$this->input_file_name]['type'][$key] : null);
                $this->files[$this->input_file_name]['tmp_name'] = (isset($_FILES[$this->input_file_name]['tmp_name'][$key]) ? $_FILES[$this->input_file_name]['tmp_name'][$key] : null);
                $this->files[$this->input_file_name]['error'] = (isset($_FILES[$this->input_file_name]['error'][$key]) ? $_FILES[$this->input_file_name]['error'][$key] : 4);
                $this->files[$this->input_file_name]['size'] = (isset($_FILES[$this->input_file_name]['size'][$key]) ? $_FILES[$this->input_file_name]['size'][$key] : 0);

                $result = $this->uploadSingleFile();

                if ($result == false && $this->stop_on_failed_upload_multiple === true) {
                    // it was set to sop on failed to upload multiple file. return false.
                    unset($result);
                    return false;
                }
            }// endforeach;
            unset($key, $value);
        } else {
            // if single file upload.
            $this->files[$this->input_file_name] = $_FILES[$this->input_file_name];
            $this->files[$this->input_file_name]['input_file_key'] = 0;

            $result = $this->uploadSingleFile();
        }

        if (isset($result) && $result == false && $this->stop_on_failed_upload_multiple === true) {
            // there is at lease one upload error and it was set to stop on error.
            unset($result);
            $this->clearUploadedAtTemp();
            return false;
        } elseif (count($this->error_messages) > 0 && $this->stop_on_failed_upload_multiple === true) {
            // there is at lease one upload error and it was set to stop on error.
            unset($result);
            $this->clearUploadedAtTemp();
            return false;
        }

        return $this->moveUploadedFiles();
    }// upload


    /**
     * Start upload process for single file.<br>
     * Even upload multiple file will call to this method because it will be re-format the uploaded files property to become a single file and then call this.
     * 
     * @return boolean Return true on success, false for otherwise.
     */
    protected function uploadSingleFile()
    {
        // check if there is error while uploading from error array key.
        if (is_array($this->files[$this->input_file_name]) && array_key_exists('error', $this->files[$this->input_file_name]) && $this->files[$this->input_file_name]['error'] != 0) {
            switch ($this->files[$this->input_file_name]['error']) {
                case 1:
                    $this->setErrorMessage(
                        sprintf(static::__('The uploaded file exceeds the max file size directive. (%s &gt; %s).'), $this->files[$this->input_file_name]['size'], ini_get('upload_max_filesize')),
                        'RDU_' . $this->files[$this->input_file_name]['error'],
                        $this->files[$this->input_file_name]['size'] . ' &gt; ' . ini_get('upload_max_filesize'),
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                case 2:
                    $this->setErrorMessage(
                        static::__('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.'),
                        'RDU_' . $this->files[$this->input_file_name]['error'],
                        '',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                case 3:
                    $this->setErrorMessage(
                        static::__('The uploaded file was only partially uploaded.'),
                        'RDU_' . $this->files[$this->input_file_name]['error'],
                        '',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                case 4:
                    $this->setErrorMessage(
                        static::__('You did not upload the file.'),
                        'RDU_' . $this->files[$this->input_file_name]['error'],
                        '',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                case 6:
                    $this->setErrorMessage(
                        static::__('Missing a temporary folder.'),
                        'RDU_' . $this->files[$this->input_file_name]['error'],
                        '',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                case 7:
                    $this->setErrorMessage(
                        static::__('Failed to write file to disk.'),
                        'RDU_' . $this->files[$this->input_file_name]['error'],
                        '',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                case 8:
                    $this->setErrorMessage(
                        static::__('A PHP extension stopped the file upload.'),
                        'RDU_' . $this->files[$this->input_file_name]['error'],
                        '',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
            }
        }

        // validate that there is file upload.
        if (
            empty($this->files[$this->input_file_name]) || 
            (
                is_array($this->files[$this->input_file_name]) && 
                array_key_exists('name', $this->files[$this->input_file_name]) && 
                $this->files[$this->input_file_name]['name'] == null
            ) ||
            (
                is_array($this->files[$this->input_file_name]) && 
                array_key_exists('tmp_name', $this->files[$this->input_file_name]) && 
                $this->files[$this->input_file_name]['tmp_name'] == null
            )
        ) {
            $this->setErrorMessage(
                static::__('You did not upload the file.'),
                'RDU_4',
                '',
                $this->files[$this->input_file_name]['name'],
                $this->files[$this->input_file_name]['size'],
                $this->files[$this->input_file_name]['type']
            );
            return false;
        }

        // validate allowed extension and its mime types.
        $result = $this->validateExtensionAndMimeType();
        if ($result !== true) {
            return false;
        }
        unset($result);

        // validate max file size.
        $result = $this->validateFileSize();
        if ($result !== true) {
            return false;
        }
        unset($result);

        // validate max image dimension.
        $result = $this->validateImageDimension();
        if ($result !== true) {
            return false;
        }
        unset($result);

        // security scan.
        if ($this->security_scan === true) {
            $result = $this->securityScan();
            if ($result !== true) {
                return false;
            }
            unset($result);
        }

        // set new file name (in case that it was not set) and check for reserved file name.
        $tmp_new_file_name = $this->new_file_name;
        $this->setNewFileName();

        // check for safe web file name if this option was set to true.
        if ($this->web_safe_file_name === true) {
            $this->setWebSafeFileName();
        }

        // now, it should all passed validation. add the uploaded file to move uploaded queue in case that it is upload multiple file and has option to stop on error.
        // get the uploaded file extension.
        $file_name_explode = explode('.', $this->files[$this->input_file_name]['name']);
        $file_extension = null;
        if (is_array($file_name_explode)) {
            $file_extension = '.'.$file_name_explode[count($file_name_explode)-1];
        }
        unset($file_name_explode);
        // add to queue.
        $this->move_uploaded_queue = array_merge(
            $this->move_uploaded_queue, 
            array(
                $this->files[$this->input_file_name]['input_file_key'] => array(
                    'name' => $this->files[$this->input_file_name]['name'],
                    'tmp_name' => $this->files[$this->input_file_name]['tmp_name'],
                    'new_name' => $this->new_file_name.$file_extension,
                )
            )
        );
        // restore temp of new file name to ready for next loop of upload multiple.
        $this->new_file_name = $tmp_new_file_name;
        unset($file_extension, $tmp_new_file_name);

        // done.
        return true;
    }// uploadSingleFile


    /**
     * Validate allowed extension and its mime types (if all of these was set).
     * 
     * @return boolean Return true on success, false on failure.
     */
    protected function validateExtensionAndMimeType()
    {
        if ($this->allowed_file_extensions == null && ($this->file_extensions_mime_types == null || empty($this->file_extensions_mime_types))) {
            // it is not set to limit uploaded file extensions and mime types.
            return true;
        }

        // get only file extension of uploaded file.
        $file_name_explode = explode('.', $this->files[$this->input_file_name]['name']);
        if (!is_array($file_name_explode)) {
            unset($file_name_explode);
            $this->setErrorMessage(
                sprintf(static::__('Unable to validate extension for the file %s.'), $this->files[$this->input_file_name]['name']),
                'RDU_UNABLE_VALIDATE_EXT',
                $this->files[$this->input_file_name]['name'],
                $this->files[$this->input_file_name]['name'],
                $this->files[$this->input_file_name]['size'],
                $this->files[$this->input_file_name]['type']
            );
            return false;
        }
        $file_extension = strtolower($file_name_explode[count($file_name_explode)-1]);
        unset($file_name_explode);

        // validate allowed extensions.
        if (is_array($this->allowed_file_extensions) && !in_array($file_extension, $this->allowed_file_extensions)) {
            unset($file_extension);
            $this->setErrorMessage(
                sprintf(static::__('You have uploaded the file that is not allowed extension. (%s)'), $this->files[$this->input_file_name]['name']),
                'RDU_NOT_ALLOW_EXT',
                $this->files[$this->input_file_name]['name'],
                $this->files[$this->input_file_name]['name'],
                $this->files[$this->input_file_name]['size'],
                $this->files[$this->input_file_name]['type']
            );
            return false;
        }

        // validate allowed mime types that match uploaded file's extension.
        if (is_array($this->file_extensions_mime_types) && !empty($this->file_extensions_mime_types)) {
            if (!array_key_exists($file_extension, $this->file_extensions_mime_types)) {
                unset($file_extension);
                $this->setErrorMessage(
                    sprintf(static::__('Unable to validate the file extension and mime type. (%s). This file extension was not set in the &quot;file_extensions_mime_types&quot; property.'), $this->files[$this->input_file_name]['name']),
                    'RDU_UNABLE_VALIDATE_EXT_AND_MIME',
                    $this->files[$this->input_file_name]['name'],
                    $this->files[$this->input_file_name]['name'],
                    $this->files[$this->input_file_name]['size'],
                    $this->files[$this->input_file_name]['type']
                );
                return false;
            } else {
                $Finfo = new \finfo();
                $file_mimetype = $Finfo->file($this->files[$this->input_file_name]['tmp_name'], FILEINFO_MIME_TYPE);
                if (is_array($this->file_extensions_mime_types[$file_extension]) && !in_array(strtolower($file_mimetype), array_map('strtolower', $this->file_extensions_mime_types[$file_extension]))) {
                    unset($file_extension, $Finfo);
                    $this->setErrorMessage(
                        sprintf(static::__('The uploaded file has invalid mime type. (%s : %s).'), $this->files[$this->input_file_name]['name'], $file_mimetype),
                        'RDU_INVALID_MIME',
                        $this->files[$this->input_file_name]['name'] . ' : ' . $file_mimetype,
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $file_mimetype
                    );
                    unset($file_mimetype);
                    return false;
                } elseif (!is_array($this->file_extensions_mime_types[$file_extension])) {
                    unset($file_extension, $file_mimetype, $Finfo);
                    $this->setErrorMessage(
                        static::__('Unable to validate mime type. The format of &quot;file_extensions_mime_types&quot; property is incorrect.'),
                        'RDU_UNABLE_VALIDATE_MIME',
                        '',
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                }
                unset($file_mimetype, $Finfo);
            }
        }

        unset($file_extension);
        return true;
    }// validateExtensionAndMimeType


    /**
     * Validate uploaded file must not exceed max file size limit. (if max file size limit was set).
     * 
     * @return boolean Return true on success, false on failure.
     */
    protected function validateFileSize()
    {
        if (!is_numeric($this->max_file_size) && !is_int($this->max_file_size)) {
            // it is not set max file size limitation.
            return true;
        }

        if (is_array($this->files[$this->input_file_name]) && array_key_exists('size', $this->files[$this->input_file_name]) && $this->files[$this->input_file_name]['size'] > $this->max_file_size) {
            $this->setErrorMessage(
                sprintf(static::__('The uploaded file exceeds the max file size directive. (%s &gt; %s).'), $this->files[$this->input_file_name]['size'], $this->max_file_size),
                'RDU_1',
                $this->files[$this->input_file_name]['size'] . ' &gt; ' . $this->max_file_size,
                $this->files[$this->input_file_name]['name'],
                $this->files[$this->input_file_name]['size'],
                $this->files[$this->input_file_name]['type']
            );
            return false;
        } else {
            if (is_array($this->files[$this->input_file_name]) && array_key_exists('tmp_name', $this->files[$this->input_file_name]) && function_exists('filesize')) {
                if (filesize($this->files[$this->input_file_name]['tmp_name']) > $this->max_file_size) {
                    $this->setErrorMessage(
                        sprintf(static::__('The uploaded file exceeds the max file size directive. (%s &gt; %s).'), $this->files[$this->input_file_name]['size'], $this->max_file_size),
                        'RDU_1',
                        $this->files[$this->input_file_name]['size'] . ' &gt; ' . $this->max_file_size,
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                }
            }
        }

        return true;
    }// validateFileSize


    /**
     * Validate image dimension if uploaded file is image and size is smaller than specified `max_image_dimensions`.
     * 
     * @return boolean Return true on success, false on failure. 
     * Also return true on these conditions.
     * - No `max_image_dimensions` property set or it was set to empty array.
     * - The `getimagesize()` function return `false`. It means that this uploaded file is NOT an image. The developers need to validate it again one by one from uploaded files.
     * - Unable to find upload temp file. This is for make the upload progress passed and ready to move uploaded file. The developers need to validate it again one by one from uploaded files.
     * Also return false on this condition.
     * - The `getimagesize()` function return 0 in width and height as noted in this page ( http://php.net/getimagesize ).
     */
    protected function validateImageDimension()
    {
        if (empty($this->max_image_dimensions)) {
            return true;
        }

        if (
            is_array($this->files[$this->input_file_name]) && 
            array_key_exists('tmp_name', $this->files[$this->input_file_name]) &&
            is_file($this->files[$this->input_file_name]['tmp_name'])
        ) {
            $image = getimagesize($this->files[$this->input_file_name]['tmp_name']);
            if ($image === false) {
                // this uploaded file is NOT an image. It is possible that user upload mixed file types such as text with jpeg.
                return true;
            } elseif (is_array($image) && count($image) >= 2) {
                if (
                    $image[0] <= $this->max_image_dimensions[0] &&
                    $image[1] <= $this->max_image_dimensions[1]
                ) {
                    // if image dimensions are smaller or equal to max.
                    return true;
                } elseif (
                    $image[0] <= 0 ||
                    $image[1] <= 0
                ) {
                    // Some formats may contain no image or may contain multiple images. 
                    // In these cases, getimagesize() might not be able to properly determine the image size. 
                    // getimagesize() will return zero for width and height in these cases.
                    // Reference: http://php.net/getimagesize
                    $this->setErrorMessage(
                        sprintf(static::__('The uploaded image contain no image or multiple images. (%s).'), $image[0] . 'x' . $image[1]),
                        'RDU_IMG_NO_OR_MULTIPLE_IMAGES',
                        $image[0] . 'x' . $image[1],
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                } else {
                    // if image dimensions are larger than max.
                    $this->setErrorMessage(
                        sprintf(static::__('The uploaded image dimensions are larger than allowed max dimensions. (%s &gt; %s).'), $image[0] . 'x' . $image[1], $this->max_image_dimensions[0] . 'x' . $this->max_image_dimensions[1]),
                        'RDU_IMG_DIMENSION_OVER_MAX',
                        $image[0] . 'x' . $image[1] . ' &gt; ' . $this->max_image_dimensions[0] . 'x' . $this->max_image_dimensions[1],
                        $this->files[$this->input_file_name]['name'],
                        $this->files[$this->input_file_name]['size'],
                        $this->files[$this->input_file_name]['type']
                    );
                    return false;
                }
            } else {
                // this uploaded file is NOT an image (return array does not meet requirement). It is possible that user upload mixed file types such as text with jpeg.
                return true;
            }
        }

        return true;
    }// validateImageDimension


    /**
     * Validate that these options properties has properly set in the correct type.
     * 
     * @throws Exception Throw errors on invalid property type.
     */
    protected function validateOptionsProperties()
    {
        if (!is_array($this->allowed_file_extensions) && $this->allowed_file_extensions != null) {
            $this->allowed_file_extensions = array($this->allowed_file_extensions);
        }

        if (!is_array($this->file_extensions_mime_types) && $this->file_extensions_mime_types != null) {
            $this->file_extensions_mime_types = null;
        }

        if (is_numeric($this->max_file_size) && !is_int($this->max_file_size)) {
            $this->max_file_size = intval($this->max_file_size);
        } elseif (!is_int($this->max_file_size) && $this->max_file_size != null) {
            $this->max_file_size = null;
        }

        if (
            !is_array($this->max_image_dimensions) || 
            (
                is_array($this->max_image_dimensions) && 
                (
                    count($this->max_image_dimensions) != 2 ||
                    (
                        count($this->max_image_dimensions) == 2 &&
                        count($this->max_image_dimensions) != count($this->max_image_dimensions, COUNT_RECURSIVE)
                    )
                )
            )
        ) {
            $this->max_image_dimensions = array();
        } else {
            if (!is_int($this->max_image_dimensions[0]) || !is_int($this->max_image_dimensions[1])) {
                $this->max_image_dimensions = array();
            }
        }

        if (empty($this->move_uploaded_to)) {
            trigger_error(static::__('The move_uploaded_to property was not set'), E_USER_ERROR);
        }

        if (!is_string($this->new_file_name) && $this->new_file_name != null) {
            $this->new_file_name = null;
        }

        if (!is_bool($this->overwrite)) {
            $this->overwrite = false;
        }

        if (!is_bool($this->web_safe_file_name)) {
            $this->web_safe_file_name = true;
        }

        if (!is_bool($this->security_scan)) {
            $this->security_scan = false;
        }

        if (!is_bool($this->stop_on_failed_upload_multiple)) {
            $this->stop_on_failed_upload_multiple = true;
        }
    }// validateOptionsProperties


}
