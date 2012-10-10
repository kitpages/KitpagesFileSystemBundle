<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

// external service
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Kitpages\UtilBundle\Service\Util;

use Kitpages\FileSystemBundle\Model\AdapterFileInterface;
use Kitpages\FileSystemBundle\FileSystemException;

class Local implements AdapterInterface {
    ////
    // dependency injection
    ////
    protected $directory = null;
    protected $baseUrl = null;

    public function __construct(
        Util $util,
        $directoryPublic,
        $directoryPrivate,
        $baseUrl,
        $idService
    )
    {
        $this->util = $util;

        $idService = str_replace('kitpages_file_system.file_system.', '', $idService);
        $this->directoryPublic = $directoryPublic.'/data/bundle/kitpagesFileSystem/'.$idService;
        $this->directoryPrivate = $directoryPrivate.'/data/bundle/kitpagesFileSystem/'.$idService;
        $this->baseUrl = $baseUrl.'/data/bundle/kitpagesFileSystem/'.$idService.'/';

    }

    /**
     * @return Util
     */
    public function getUtil()
    {
        return $this->util;
    }

    private function getPath(AdapterFileInterface $file)
    {
        if ($file->getIsPrivate()) {
            return $this->directoryPrivate.'/'.$file->getPath();
        } else {
            return $this->directoryPublic.'/'.$file->getPath();
        }
    }

    // action
    public function rename(AdapterFileInterface $tempFile, AdapterFileInterface $targetFile)
    {
        $tempFilePath = $this->getPath($tempFile);
        $targetFilePath = $this->getPath($targetFile);

        $this->getUtil()->mkdirr(dirname($targetFilePath));
        return rename($tempFilePath, $targetFilePath);
    }

    public function unlink(AdapterFileInterface $targetFile)
    {
        if ($this->isFile($targetFile)) {
            $targetFilePath = $this->getPath($targetFile);
            if ($targetFile->getIsPrivate()) {
                return unlink($targetFilePath);
            } else {
                $this->getUtil()->rmdirr(dirname($targetFilePath));
            }
        }
        return false;
    }

    public function copy(AdapterFileInterface $targetFile, AdapterFileInterface $targetFileCopy)
    {
        $targetFilePath = $this->getPath($targetFile);
        $targetFileCopyPath = $this->getPath($targetFileCopy);

        $this->getUtil()->mkdirr(dirname($targetFileCopyPath));

        if ($this->isFile($targetFile)) {
            return copy($targetFilePath, $targetFileCopyPath) ;
        }
        return false;
    }

    function copyTempToAdapter($tempPath, AdapterFileInterface $file)
    {
        $targetFilePath = $tempPath;
        $targetFileCopyPath = $this->getPath($file);

        $this->getUtil()->mkdirr(dirname($targetFileCopyPath));
        if (!is_file($tempPath)) {
            throw new FileSystemException("Temp file $tempPath does not exist.");
        }

        if ( ! copy($targetFilePath, $targetFileCopyPath) ) {
            throw new FileSystemException("local copy failed.");
        }
    }

    function copyAdapterToTemp(AdapterFileInterface $file, $tempPath)
    {
        $targetFilePath = $this->getPath($file);

        $this->getUtil()->mkdirr(dirname($tempPath));

        if (!is_file($targetFilePath)) {
            throw new FileSystemException("Source file $targetFilePath does not exists");
        }
        if (! copy($targetFilePath, $tempPath) ) {
            throw new FileSystemException("Copy of $targetFilePath to temp failed");
        }
    }

    public function rmdirr(AdapterFileInterface $directory)
    {
        $directoryPath = $this->getPath($directory);
        if (is_dir($directoryPath)) {
            return $this->getUtil()->rmdirr($directoryPath);
        }
        return false;
    }

    // information
    public function isFile(AdapterFileInterface $targetFile)
    {
        $targetFilePath = $this->getPath($targetFile);
        return is_file($targetFilePath);
    }

    public function sendFileToBrowser(AdapterFileInterface $targetFile, $name = null)
    {
        $targetFilePath = $this->getPath($targetFile);
        $this->getUtil()->getFile($targetFilePath, 0, null, $name);
    }

    public function getFileContent(AdapterFileInterface $targetFile)
    {
        $targetFilePath = $this->getPath($targetFile);
        return file_get_contents($targetFilePath);
    }

    public function getFileLocation(AdapterFileInterface $targetFile)
    {
        return $this->baseUrl.$targetFile->getPath();
    }
}
