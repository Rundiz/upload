<?php


namespace Rundiz\Upload\Tests\PHP70;


class ThrowErrorTest extends \PHPUnit\Framework\TestCase
{


    public function __destruct()
    {
        if (empty($this->temp_folder) || stripos($this->temp_folder, DIRECTORY_SEPARATOR . 'temp') === false) {
            // on error, the temp folder property will not set, do nothing here.
            return ;
        }

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
    }// __destruct


    private $asset_folder;
    private $temp_folder;
    private $file_text;


    public function setUp()
    {
        if (
            class_exists('\PHPUnit_Runner_Version') && 
            method_exists('\PHPUnit_Runner_Version', 'id') &&
            version_compare(\PHPUnit_Runner_Version::id(), '6.0', '<')
        ) {
            $this->markTestSkipped('Require PHPUnit v6.0.x');
        } elseif (
            class_exists('\PHPUnit\Runner\Version') && 
            method_exists('\PHPUnit\Runner\Version', 'id') &&
            version_compare(\PHPUnit\Runner\Version::id(), '6.0', '<')
        ) {
            $this->markTestSkipped('Require PHPUnit v6.0.x');
        }

        $this->asset_folder = \Rundiz\Upload\Tests\CommonConfig::getAssetsDir();
        $this->temp_folder = \Rundiz\Upload\Tests\CommonConfig::getTempDir();

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
     * @expectedException PHPUnit\Framework\Error
     */
    public function testMoveUploadedToError()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $_FILES = $this->file_text;

        $Upload = new \Rundiz\Upload\Upload('filename');
        $Upload->move_uploaded_to = null;
        $Upload->upload();

        unset($Upload);
    }// testMoveUploadedToError


}