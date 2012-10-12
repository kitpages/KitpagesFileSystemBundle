<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

// external service
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Ftp implements AdapterInterface{
    ////
    // dependency injection
    ////
    protected $directory = null;


    public function __construct(
        $directory
    )
    {
        $this->directory = $directory;
    }
    /*********************/
    //action
    /*********************/
    function copyTempToAdapter($tempPath, AdapterFileInterface $file){}

    function copyAdapterToTemp(AdapterFileInterface $file, $tempPath){}

    function rename(AdapterFileInterface $tempFile, AdapterFileInterface $targetFile){}

    function unlink(AdapterFileInterface $targetFile){}

    function copy(AdapterFileInterface $targetFile, AdapterFileInterface $targetFileCopy){}

    /*********************/
    // information
    /*********************/
    function isFile(AdapterFileInterface $targetFile){}

    function sendFileToBrowser(AdapterFileInterface $targetFile, $name = null){}

    function getFileContent(AdapterFileInterface $targetFile){}

    function getFileLocation(AdapterFileInterface $targetFile){}

    function rmdirr(AdapterFileInterface $directory){}
}
