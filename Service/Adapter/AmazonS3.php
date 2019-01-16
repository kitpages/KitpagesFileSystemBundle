<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

// external service
use Kitpages\FileSystemBundle\Util\Util;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Kitpages\FileSystemBundle\KitpagesFileSystemEvents;

use Kitpages\FileSystemBundle\Model\AdapterFileInterface;
use Kitpages\FileSystemBundle\Event\AdapterFileEvent;

class AmazonS3 implements AdapterInterface{
    ////
    // dependency injection
    ////
    protected $key = null;
    protected $secretKey = null;
    protected $bucketName = null;
    protected $s3 = null;
    protected $idService = null;
    protected $protocol = null;
    protected $util = null;
    protected $dispatcher = null;

    public function __construct(
        Util $util,
        EventDispatcherInterface $dispatcher,
        $bucketName,
        $key,
        $secretKey,
        $idService
    )
    {
        $this->util = $util;
        $this->dispatcher = $dispatcher;
        $protocolList = stream_get_wrappers();
        $countProtocolAmazon = 1;
        $protocolFree = false;

        while(!$protocolFree) {
            $protocolAmazon = "amazon".$countProtocolAmazon;
            if (!in_array($protocolAmazon, $protocolList)) {
                $protocolFree = true;
                $this->protocol = $protocolAmazon;
            }
            $countProtocolAmazon++;
        }

        $this->bucketName = $bucketName;
        $this->idService = str_replace('kitpages_file_system.file_system.', '', $idService);
        $this->s3 = new \AmazonS3(array('key' => $key, 'secret' => $secretKey));
        $this->s3->register_stream_wrapper($this->protocol);

    }

    /**
     * @return Util
     */
    public function getUtil()
    {
        return $this->util;
    }

    public function getBucket()
    {
        return $this->protocol.'://'.$this->bucketName;
    }

    public function getPathFull(AdapterFileInterface $file)
    {
        return $this->getBucket().'/'.$this->getPath($file);
    }

    public function getPath(AdapterFileInterface $file)
    {
        if ($file->getIsPrivate()) {
            return 'bundle/kitpagesFileSystem/'.$this->idService.'/private/'.$file->getPath();
        } else {
            return 'bundle/kitpagesFileSystem/'.$this->idService.'/public/'.$file->getPath();
        }
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    //action
    public function fileSetAclAndContentType(AdapterFileInterface $file, $contentType)
    {

        if ($file->getIsPrivate()) {
            $acl = \AmazonS3::ACL_PRIVATE;
        } else {
            $acl = \AmazonS3::ACL_PUBLIC;
        }

        $opt = array(
            'acl' => $acl,
            'headers' => array('Content-Type'=>$contentType),
            'metadataDirective' => 'REPLACE'
        );

        $result = $this->s3->copy_object(
			array('bucket' => $this->bucketName, 'filename' => $this->getPath($file)),
			array('bucket' => $this->bucketName, 'filename' => $this->getPath($file)),
			$opt
		);
        return $result->isOK();

    }

    function copyTempToAdapter($tempPath, AdapterFileInterface $file)
    {
        $targetFileCopyPath = $this->getPathFull($file);
        $resTargetFile = fopen($tempPath, 'r');
        $resTargetFileCopy = fopen($targetFileCopyPath, 'w');
        $resultCopy = stream_copy_to_stream($resTargetFile, $resTargetFileCopy);
        fclose($resTargetFile);
        fclose($resTargetFileCopy);

        if ($file->getMimeType() == null) {
            $file->setMimeType($this->getMimeContentType($tempPath, $file->getPath()));
        }

        $this->fileSetAclAndContentType($file, $file->getMimeType());

        //return $resultCopy;
    }

    function copyAdapterToTemp(AdapterFileInterface $file, $tempPath)
    {
        $targetFilePath = $this->getPathFull($file);
        $resTargetFileCopy = fopen($tempPath, 'w');
        $resTargetFile = fopen($targetFilePath, 'r');
        $resultCopy = stream_copy_to_stream($resTargetFile, $resTargetFileCopy);

        //return $resultCopy;
    }

    public function rename(AdapterFileInterface $tempFile, AdapterFileInterface $targetFile)
    {
        $tempFilePath = $this->getPathFull($tempFile);
        $targetFilePath = $this->getPathFull($targetFile);
        return rename($tempFilePath, $targetFilePath);
    }

    public function unlink(AdapterFileInterface $targetFile)
    {
        $targetFilePath = $this->getPathFull($targetFile);
        if ($this->isFile($targetFile)){
            return unlink($targetFilePath);
        }
        return false;
    }

    public function copy(AdapterFileInterface $targetFile, AdapterFileInterface $targetFileCopy)
    {
        $source = array(
            'bucket' =>$this->bucketName,
            'filename' => $this->getPath($targetFile)
        );
        $dest = array(
            'bucket' =>$this->bucketName,
            'filename' => $this->getPath($targetFileCopy)
        );
        $opt = array();
        if (!$targetFileCopy->getIsPrivate()) {
            $opt['acl'] = \AmazonS3::ACL_PUBLIC;
        }
        $response = $this->s3->copy_object($source, $dest, $opt);
        return $response->isOK();
    }

    public function rmdirr(AdapterFileInterface $directory)
    {
        $list = $this->s3->get_object_list($this->bucketName, array('prefix' => $this->getPath($directory)));
        $listDelete = array_map(function($v){return array("key"=>$v);}, $list);
        $result = $this->s3->delete_objects($this->bucketName, array('objects' => $listDelete));
        return $result->isOK();
//        return $this->s3->delete_object($this->bucketName, $this->getPath($directory));
    }

    // information
    public function isFile(AdapterFileInterface $targetFile)
    {
        $exist = $this->s3->if_object_exists($this->bucketName, $this->getPath($targetFile));
        if ($exist) {
            return true;
        } else {
            return false;
        }
    }

    public function sendFileToBrowser(AdapterFileInterface $targetFile, $name = null)
    {
        //First, see if the file exists
        if (!$this->isFile($targetFile)) {
            throw new \Exception(
                "Download Manager : file [".$targetFile->getPath()." doesn't exist"
            );
        }

        // ENO modif, required for IE
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        // throw on event
        $event = new AdapterFileEvent($this->idService, $targetFile);
        $event->set('nameFile', $name);
        $this->dispatcher->dispatch(KitpagesFileSystemEvents::onSendFileToBrowser, $event);

        // preventable action
        if (!$event->isDefaultPrevented()) {
            $targetFile = $event->getAdapterFile();


            $headers = $this->s3->get_object_headers($this->bucketName, $this->getPath($targetFile));
            $header = $headers->header;

            if ($targetFile->getMimeType() != null) {
                $ctype = $targetFile->getMimeType();
            } else {
                $ctype = $header['content-type'];
            }

            header('Cache-Control: public, max-age=0');
            header('Expires: '.gmdate("D, d M Y H:i:s", time())." GMT");
            header('Pragma: cache');
            header('Content-type: '.$ctype);
            header('Content-length: '.$header['content-length']);
            if ($name != null) {
                header("Content-Disposition: attachment; filename=\"" . $name . "\"");
            }

            $chunksize = 1*(1024*1024); // how many bytes per chunk
            $buffer = '';
            $cnt =0;
            $handle = fopen($this->getPathFull($targetFile), 'rb');
            if ($handle === false) {
                return false;
            }
            while (!feof($handle)) {
                $buffer = fread($handle, $chunksize);
                echo $buffer;
                ob_flush();
                flush();
            }
            $status = fclose($handle);
        }
        // throw after event
        $this->dispatcher->dispatch(KitpagesFileSystemEvents::afterSendFileToBrowser, $event);
        if ($status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
        exit();
    }

    public function getFileContent(AdapterFileInterface $targetFile)
    {
        $targetFilePath = $this->getPathFull($targetFile);
        return file_get_contents($targetFilePath);
    }

    public function getFileLocation(AdapterFileInterface $targetFile)
    {
        return $this->s3->get_object_url($this->bucketName, $this->getPath($targetFile));
    }

    public function getMimeContentType($file, $fileName)
    {
        return $this->util->getMimeContentType($file, $fileName);
    }

}
