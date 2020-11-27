<?php


namespace Rundiz\Upload\Tests;


class CommonConfig
{


    public static function getAssetsDir()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR;
    }// getAssetsDir


    public static function getRundizUploadClassDir()
    {
        return dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'Rundiz'.DIRECTORY_SEPARATOR.'Upload'.DIRECTORY_SEPARATOR;
    }// getRundizUploadClassDir


    public static function getTempDir()
    {
        return __DIR__.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    }// getTempDir


}