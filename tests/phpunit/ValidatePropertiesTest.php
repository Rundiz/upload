<?php


namespace Rundiz\Upload\Tests;

class ValidatePropertiesTest extends \PHPUnit\Framework\TestCase
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
    private $file_51kbimage;


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
        $this->file_51kbimage['filename'] = array(
            'name' => '51KB-image.JPG',
            'type' => 'image/jpeg',
            'tmp_name' => $this->temp_folder.'51KB-image.JPG',
            'error' => 0,
            'size' => filesize($this->temp_folder.'51KB-image.JPG'),
        );
    }// setUp


    public function testAllowedFileExtension()
    {
        $_FILES = $this->file_text;

        $Upload = new ExtendedUploadForTest('filename');

        $Upload->allowed_file_extensions = 'invalidType';
        $Upload->validateOptionsProperties();
        $this->assertEquals(array('invalidType'), $Upload->allowed_file_extensions);

        $Upload->allowed_file_extensions = null;
        $Upload->validateOptionsProperties();
        $this->assertNull($Upload->allowed_file_extensions);

        unset($Upload);
    }// testAllowedFileExtension


    public function testFileExtensionsMimeTypes()
    {
        $_FILES = $this->file_text;

        $Upload = new ExtendedUploadForTest('filename');

        $Upload->file_extensions_mime_types = 'invalidType';
        $Upload->validateOptionsProperties();
        $this->assertNull($Upload->file_extensions_mime_types);

        $Upload->file_extensions_mime_types = null;
        $Upload->validateOptionsProperties();
        $this->assertNull($Upload->file_extensions_mime_types);

        unset($Upload);
    }// testFileExtensionsMimeTypes


    public function testMaxFileSize()
    {
        $_FILES = $this->file_text;

        $Upload = new ExtendedUploadForTest('filename');

        $Upload->max_file_size = '2.5';
        $Upload->validateOptionsProperties();
        $this->assertEquals(2, $Upload->max_file_size);

        $Upload->max_file_size = 'invalidType';
        $Upload->validateOptionsProperties();
        $this->assertNull($Upload->max_file_size);

        unset($Upload);
    }// testMaxFileSize


    public function testMaxImageDimension()
    {
        $_FILES = $this->file_text;

        $Upload = new ExtendedUploadForTest('filename');

        $Upload->max_image_dimensions = 'invalidType';
        $Upload->validateOptionsProperties();
        $this->assertEquals(array(), $Upload->max_image_dimensions);

        $Upload->max_image_dimensions = array(500);
        $Upload->validateOptionsProperties();
        $this->assertEquals(array(), $Upload->max_image_dimensions);

        $Upload->max_image_dimensions = array(500, 300);
        $Upload->validateOptionsProperties();
        $this->assertEquals(array(500, 300), $Upload->max_image_dimensions);

        $Upload->max_image_dimensions = array(500, 300, 200);
        $Upload->validateOptionsProperties();
        $this->assertEquals(array(), $Upload->max_image_dimensions);

        $Upload->max_image_dimensions = array('string', 300);
        $Upload->validateOptionsProperties();
        $this->assertEquals(array(), $Upload->max_image_dimensions);

        $Upload->max_image_dimensions = array(500, 300, array(200, 100));
        $Upload->validateOptionsProperties();
        $this->assertEquals(array(), $Upload->max_image_dimensions);

        unset($Upload);
    }// testMaxImageDimension


    public function testNewFileName()
    {
        $_FILES = $this->file_text;

        $Upload = new ExtendedUploadForTest('filename');

        $Upload->new_file_name = array('newFileName');
        $Upload->validateOptionsProperties();
        $this->assertNull($Upload->new_file_name);

        $Upload->new_file_name = 'newFileName';
        $Upload->validateOptionsProperties();
        $this->assertEquals('newFileName', $Upload->new_file_name);

        unset($Upload);
    }// testNewFileName


}