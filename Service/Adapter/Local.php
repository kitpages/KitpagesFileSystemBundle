<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

// external service
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;

use Kitpages\UtilBundle\Service\Util;

use Kitpages\FileSystemBundle\ValueObject\AdapterFileInterface;

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
        $baseUrl
    )
    {
        $this->util = $util;
        $this->directoryPublic = $directoryPublic;
        $this->directoryPrivate = $directoryPrivate;
        $this->baseUrl = $baseUrl;

        // test
        $pathFileLocal = dirname(__FILE__).'/../../Tests/tmp/testAmazon.txt';
        $fileAmazon = new \Kitpages\FileSystemBundle\ValueObject\AdapterFile('data/bundle/kitpagesfile/testAmazon.txt', true);
        $fNew = fopen($pathFileLocal, 'w');
        fputs ($fNew, 'fichier de test');
        fclose($fNew);
        $this->moveTempToAdapter($pathFileLocal, $fileAmazon);

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

        $this->mkdirr(dirname($targetFilePath), false);
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

        $targetDir = dirname($targetFileCopyPath);
        $this->mkdirr($targetDir, true);

        if ($this->isFile($targetFile)) {
            return copy($targetFilePath, $targetFileCopyPath) ;
        }
        return false;
    }

    function moveTempToAdapter($tempPath, AdapterFileInterface $file)
    {
        $targetFilePath = $tempPath;
        $targetFileCopyPath = $this->getPath($file);

        $targetDir = dirname($targetFileCopyPath);
        $this->mkdirr($targetDir, true);

        if (is_file($tempPath)) {
            return copy($targetFilePath, $targetFileCopyPath) ;
        }
        return false;
    }

    function moveAdapterToTemp(AdapterFileInterface $file, $tempPath)
    {
        $targetFileCopyPath = $tempPath;
        $targetFilePath = $this->getPath($file);

        $targetDir = dirname($targetFileCopyPath);
        $this->mkdirr($targetDir, true);

        if (is_file($tempPath)) {
            return copy($targetFilePath, $targetFileCopyPath) ;
        }
        return false;
    }

    public function mkdirr($targetDir, $overwrite = false)
    {
        if ($overwrite) {
            $this->getUtil()->rmdirr($targetDir);
        }
        return $this->getUtil()->mkdirr($targetDir);
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
        return $this->baseUrl.'/'.$targetFile->getPath();
    }
}
