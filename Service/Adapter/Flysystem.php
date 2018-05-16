<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

use Kitpages\FileSystemBundle\Service\Adapter\AdapterInterface as CmsAdapterInterface;
use League\Flysystem\AdapterInterface as FlysystemAdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

use Kitpages\FileSystemBundle\Model\AdapterFileInterface;
use Kitpages\UtilBundle\Service\Util;
use Techyah\Flysystem\OVH\OVHAdapter;
use Terradona\Infrastructure\FileBundle\Adapter\OvhCustomAdapter;

class Flysystem implements CmsAdapterInterface
{

    private $filesystem;
    private $util;
    private $fileUriPrefix;

    public function __construct(
        Util $util,
        Filesystem $filesystem,
        $fileUriPrefix = null
    )
    {
        $this->util = $util;
        $this->filesystem = $filesystem;
        $this->fileUriPrefix = $fileUriPrefix;

    }

    /*********************/
    //action
    /*********************/
    public function copyTempToAdapter($tempPath, AdapterFileInterface $file)
    {
        $this->filesystem->write($file->getPath(), file_get_contents($tempPath));

    }

    public function copyAdapterToTemp(AdapterFileInterface $file, $tempPath)
    {

        $stream = $this->filesystem->readStream($file->getPath());

        $this->filesystem->writeStream($tempPath, $stream);
    }

    public function rename(AdapterFileInterface $tempFile, AdapterFileInterface $targetFile)
    {
        $this->filesystem->rename($tempFile->getPath(), $targetFile->getPath());
    }

    public function unlink(AdapterFileInterface $targetFile)
    {
        if($this->filesystem->has($targetFile->getPath())) {
            $this->filesystem->delete($targetFile->getPath());
        }
    }

    public function copy(AdapterFileInterface $targetFile, AdapterFileInterface $targetFileCopy)
    {
        $this->filesystem->copy($targetFile->getPath(), $targetFileCopy->getPath());
    }

    /*********************/
    // information
    /*********************/
    public function isFile(AdapterFileInterface $targetFile)
    {
        return $this->filesystem->has($targetFile->getPath());
    }

    public function sendFileToBrowser(AdapterFileInterface $targetFile, $name = null)
    {

        if (!$this->filesystem->has($targetFile->getPath())) {
            throw new \Exception(
                "Download Manager : file [" . $targetFile->getPath() . "] doesn't exist"
            );
        }
        $metadata = $this->filesystem->getMetadata($targetFile->getPath());
        if ($targetFile->getMimeType() != null) {
            $ctype = $targetFile->getMimeType();
        } else {
            $ctype = $metadata['mimetype'];
        }

        header('Cache-Control: public, max-age=0');
        header('Expires: ' . gmdate("D, d M Y H:i:s", time()) . " GMT");
        header('Pragma: cache');
        header('Content-type: ' . $ctype);
        header('Content-length: ' . $metadata['size']);
        if ($name != null) {
            header("Content-Disposition: attachment; filename=\"" . $name . "\"");
        }

        echo $this->filesystem->read($targetFile->getPath());

        //// Retrieve a read-stream
        //// Seems to lag with ovh
        //$res = $this->filesystem->readStream($targetFile->getPath());
        //echo stream_get_contents($res);
        //$status = fclose($res);

        exit();
    }

    public function getFileContent(AdapterFileInterface $targetFile)
    {
        return $this->filesystem->read($targetFile);
    }

    public function getFileLocation(AdapterFileInterface $targetFile)
    {
        return $this->fileUriPrefix . '/' . $targetFile->getPath();
    }

    public function rmdirr(AdapterFileInterface $directory)
    {
        $this->filesystem->deleteDir($directory->getPath());
    }

}
