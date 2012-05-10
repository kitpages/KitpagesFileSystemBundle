<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

use Kitpages\FileSystemBundle\ValueObject\AdapterFileInterface;

/**
 * Interface for the filesystem adapters
 *
 */
interface AdapterInterface
{
    /*********************/
    //action
    /*********************/
    function copyTempToAdapter($tempPath, AdapterFileInterface $file);

    function copyAdapterToTemp(AdapterFileInterface $file, $tempPath);

    function rename(AdapterFileInterface $tempFile, AdapterFileInterface $targetFile);

    function unlink(AdapterFileInterface $targetFile);

    function copy(AdapterFileInterface $targetFile, AdapterFileInterface $targetFileCopy);

    /*********************/
    // information
    /*********************/
    function isFile(AdapterFileInterface $targetFile);

    function sendFileToBrowser(AdapterFileInterface $targetFile, $name = null);

    function getFileContent(AdapterFileInterface $targetFile);

    function getFileLocation(AdapterFileInterface $targetFile);

    function rmdirr(AdapterFileInterface $directory);





}
