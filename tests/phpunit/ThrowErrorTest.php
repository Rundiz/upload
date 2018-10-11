<?php


namespace Rundiz\Upload\Tests;

class ThrowErrorTest extends \PHPUnit\Framework\TestCase
{


    public function __destruct()
    {
        $files = glob($this->temp_folder.'*.*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file) && is_writable($file) && strpos($file, '.gitkeep') === false) {
                    unlink($file);
                }
            }
            unset($file);
        }
        unset($files);
    }// tearDownAfterClass


    private $asset_folder;
    private $temp_folder;
    private $file_text;


    public function setUp()
    {
        $this->asset_folder = __DIR__.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR;
        $this->temp_folder = __DIR__.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;

        // copy files from assets folder to temp to prevent file deletion while set it to $_FILES.
        $files = glob($this->asset_folder.'*.*');
        if (is_array($files)) {
            foreach ($files as $file) {
                $destination = str_replace($this->asset_folder, $this->temp_folder, $file);
                copy($file, $destination);
                unset($destination);
            }
            unset($file);
        }
        unset($files);

        // setup file same as it is in $_FILES.
        $this->file_text['filename'] = array(
            'name' => 'text.txt',
            'type' => 'text/plain',
            'tmp_name' => $this->temp_folder.'text.txt',
            'error' => 0,
            'size' => filesize($this->temp_folder.'text.txt'),
        );
    }// setUp


    public function tearDown()
    {
        $this->file_text = null;
        $_FILES = array();
    }// tearDown


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidInputFileNameType()
    {
        $_FILES = $this->file_text;

        $Upload = new \Rundiz\Upload\Upload(array('filename'));
        $Upload->move_uploaded_to = $this->temp_folder;
        $Upload->upload();

        unset($Upload);
    }// testInvalidInputFileNameType


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidInputFileNameTypeForGetUploadedMimeType()
    {
        $_FILES = $this->file_text;

        $Upload = new \Rundiz\Upload\Upload('filename');
        $Upload->testGetUploadedMimetype(array('filename'));

        unset($Upload);
    }// testInvalidInputFileNameTypeForGetUploadedMimeType


    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testMoveUploadedToError()
    {
        $_FILES = $this->file_text;

        $Upload = new \Rundiz\Upload\Upload('filename');
        $Upload->move_uploaded_to = null;
        $Upload->upload();

        unset($Upload);
    }// testMoveUploadedToError


}