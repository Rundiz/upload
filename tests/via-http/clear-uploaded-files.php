<?php
/**
 * Recursively remove files and folders.
 * 
 * @link http://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir Source.
 * @param string $dir Path to folder to delete.
 */
function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    if (is_file($dir . DIRECTORY_SEPARATOR . $object) && is_writable($dir . DIRECTORY_SEPARATOR . $object) && $object != '.gitignore' && $object != '.gitkeep') {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
        }

        if (is_dir($dir) && is_writable($dir) && $dir != __DIR__ . DIRECTORY_SEPARATOR . 'uploaded-files' && $dir != __DIR__ . DIRECTORY_SEPARATOR . 'uploaded-files' . DIRECTORY_SEPARATOR) {
            rmdir($dir);
        }
    }
}


rrmdir(__DIR__.DIRECTORY_SEPARATOR.'uploaded-files');


echo 'All cleared, please go back.';