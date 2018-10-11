<?php
/**
 * This file contain extended Rundiz\Upload class but just for test only.<br>
 * Many methods in the main upload class is protected and cannot access directly because of the "files" property must be set via upload() method but while you running test it cannot set via upload() method.<br>
 * So, I have to create this extended class to have setFilesPropertyForCheck() to manually set "files" property and extended those protected methods to be public just for test.
 * 
 * @author Vee W.
 */


namespace Rundiz\Upload\Tests;

class ExtendedUploadForTest extends \Rundiz\Upload\Upload
{


    public function securityScan()
    {
        return parent::securityScan();
    }// securityScan


    public function setFilesPropertyForCheck()
    {
        if (isset($_FILES[$this->input_file_name]['name']) && is_array($_FILES[$this->input_file_name]['name'])) {
            
        } else {
            $this->files[$this->input_file_name] = $_FILES[$this->input_file_name];
            $this->files[$this->input_file_name]['input_file_key'] = 0;
        }
    }// setFilesPropertyForCheck


    public function setNewFileName()
    {
        return parent::setNewFileName();
    }// setNewFileName


    public function setWebSafeFileName()
    {
        return parent::setWebSafeFileName();
    }// setWebSafeFileName


    /**
     * For test upload without move file.
     * @return boolean
     */
    public function upload()
    {
        // validate that all options properties was properly set to correct type.
        $this->validateOptionsProperties();
        // setup file extensions and mime types for validation. (in case that it was not set).
        $this->setupFileExtensionsMimeTypesForValidation();

        // verify that location where the uploaded file(s) will be moved to is writable.
        if (!is_dir($this->move_uploaded_to)) {
            $this->error_messages = array_merge($this->error_messages, array(static::__('The target location where the uploaded file(s) will be moved to is not folder or directory.')));
            return false;
        } elseif (is_dir($this->move_uploaded_to) && !is_writable($this->move_uploaded_to)) {
            $this->error_messages = array_merge($this->error_messages, array(static::__('The target location where the uploaded file(s) will be moved to is not writable. Please check the folder permission.')));
            return false;
        } else {
            // solve the move uploaded to as a real path.
            $this->move_uploaded_to = realpath($this->move_uploaded_to);
        }

        if (isset($_FILES[$this->input_file_name]['name']) && is_array($_FILES[$this->input_file_name]['name'])) {
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

        return true;
    }// upload


    public function validateExtensionAndMimeType()
    {
        return parent::validateExtensionAndMimeType();
    }// validateExtensionAndMimeType


    public function validateFileSize()
    {
        return parent::validateFileSize();
    }// validateFileSize


    public function validateImageDimension()
    {
        return parent::validateImageDimension();
    }// validateImageDimension


    public function validateOptionsProperties()
    {
        return parent::validateOptionsProperties();
    }// validateOptionsProperties


}
