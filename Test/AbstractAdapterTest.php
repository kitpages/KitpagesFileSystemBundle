<?php

namespace Kitpages\FileSystemBundle\Test;

// external service
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;

use Kitpages\FileSystemBundle\Model\AdapterFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


abstract class AbstractAdapterTest extends WebTestCase{ // extends \PHPUnit_Framework_TestCase{

    protected $adapterClass = null;
    protected $adapterList = array();
    protected $pathFileLocal = null;
    protected $pathFileAmazon = null;
    protected $pathFileAmazon2 = null;
    protected $fileAmazon = null;
    protected $fileAmazon2 = null;

    public function setUp()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $fileSystemList = $container->get('kitpages_file_system.filesystem_map')->getAdapterList();
        foreach($fileSystemList as $fileSystem) {
            if (get_class($fileSystem) == "Kitpages\FileSystemBundle\Service\Adapter\\".$this->adapterClass) {
                $this->adapterList[] = $fileSystem;
            }
        }

        if (!is_dir(dirname(__FILE__).'/../Tests/tmp')) {
            mkdir(dirname(__FILE__).'/../Tests/tmp');
        }


        $this->pathFileLocal = dirname(__FILE__).'/../Tests/tmp/testAmazon.txt';
        $this->pathFileAdapter = 'test/testAmazon.txt';
        $this->pathFileAdapter2 = 'test/testAmazon2.txt';
        $this->fileAdapter = new AdapterFile($this->pathFileAdapter, false);
        $this->fileAdapter2 = new AdapterFile($this->pathFileAdapter2, false);
        $fNew = fopen($this->pathFileLocal, 'w');
        fputs ($fNew, 'fichier de test');
        fclose($fNew);
    }

    public function tearDown()
    {
        if (is_file($this->pathFileLocal)) {
            unlink($this->pathFileLocal);
        }
        foreach($this->adapterList as $adapter) {
            $adapter->unlink($this->fileAdapter);
            $adapter->unlink($this->fileAdapter2);
        }
    }

    public function testCopyAndMove()
    {
        foreach($this->adapterList as $adapter) {
            // transfert with temp
            $fileSize = filesize($this->pathFileLocal);
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);
            unlink($this->pathFileLocal);
            $adapter->copyAdapterToTemp($this->fileAdapter, $this->pathFileLocal);
            $this->assertEquals($fileSize, filesize($this->pathFileLocal));

            // copy inside the adapter
            $adapter->copy($this->fileAdapter, $this->fileAdapter2);
            $resultIsFile = $adapter->isFile($this->fileAdapter2);
            $this->assertEquals($resultIsFile, true);
        }
    }

    public function testCopyAndMovePrivate()
    {
        $this->fileAdapter = new AdapterFile($this->pathFileAdapter, true);
        $this->fileAdapter2 = new AdapterFile($this->pathFileAdapter2, true);
        foreach($this->adapterList as $adapter) {
            $fileSize = filesize($this->pathFileLocal);
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);
            unlink($this->pathFileLocal);
            $adapter->copyAdapterToTemp($this->fileAdapter, $this->pathFileLocal);
            $this->assertEquals($fileSize, filesize($this->pathFileLocal));

            $adapter->copy($this->fileAdapter, $this->fileAdapter2);
            $resultIsFile = $adapter->isFile($this->fileAdapter2);
            $this->assertEquals($resultIsFile, true);
        }
    }

    public function testRmdirr()
    {
        foreach($this->adapterList as $adapter) {
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $pathDirAdapter = dirname($this->pathFileAdapter);

            $dirAdapter = new AdapterFile($pathDirAdapter, false);

            $resultRmdirr = $adapter->rmdirr($dirAdapter);
            if($resultRmdirr === false) {
                $result = false;
            } else {
                $result = true;
            }
            $this->assertTrue($result);

            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, false);

        }
    }

    public function testIsFile()
    {
        foreach($this->adapterList as $adapter) {
            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, false);

            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, true);
        }
    }

    public function testIsFilePrivate()
    {
        $this->fileAdapter = new AdapterFile($this->pathFileAdapter, true);
        $this->fileAdapter2 = new AdapterFile($this->pathFileAdapter2, true);
        foreach($this->adapterList as $adapter) {
            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, false);

            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, true);
        }
    }

    public function testUnlink()
    {
        foreach($this->adapterList as $adapter) {
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);
            $adapter->unlink($this->fileAdapter);

            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, false);
        }
    }

    public function testUnlinkPrivate()
    {
        $this->fileAdapter = new AdapterFile($this->pathFileAdapter, true);
        $this->fileAdapter2 = new AdapterFile($this->pathFileAdapter2, true);
        foreach($this->adapterList as $adapter) {
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);
            $adapter->unlink($this->fileAdapter);

            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, false);
        }
    }

    //action
    public function testRename()
    {
        foreach($this->adapterList as $adapter) {
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $adapter->rename($this->fileAdapter, $this->fileAdapter2);

            $resultIsFile1 = $adapter->isFile($this->fileAdapter);
            $resultIsFile2 = $adapter->isFile($this->fileAdapter2);
            $this->assertEquals($resultIsFile1, false);
            $this->assertEquals($resultIsFile2, true);
        }
    }

    public function testRenamePublicPrivate()
    {
        $this->fileAdapter = new AdapterFile($this->pathFileAdapter, false);
        $this->fileAdapter2 = new AdapterFile($this->pathFileAdapter2, true);
        foreach($this->adapterList as $adapter) {
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $url = $adapter->getFileLocation($this->fileAdapter);
            $this->assertEquals(file_get_contents($url), file_get_contents($this->pathFileLocal));

            $adapter->rename($this->fileAdapter, $this->fileAdapter2);

            $resultIsFile = $adapter->isFile($this->fileAdapter2);
            $this->assertEquals($resultIsFile, true);

            $url = $adapter->getFileLocation($this->fileAdapter2);
            $handle = curl_init($url);
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            $this->assertContains($httpCode, array('404', '403'));

        }
    }

    // information
//    public function testImage()
//    {
//        $fileImageAmazon = new AdapterFile('tools.jpg', false);
//        $adapter->copyTempToAdapter('/home/webadmin/htdocs/sfkitsite/app/data/tmp/tools.jpg', $fileImageAmazon);
//        $adapter->sendFileToBrowser($fileImageAmazon);
//        $adapter->unlink($fileImageAmazon);
//    }

//    public function testSendFileToBrowser()
//    {
//        $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);
//
//        $adapter->sendFileToBrowser($this->fileAdapter);
//    }

    public function testGetFileLocationPrivate()
    {
        $this->fileAdapter = new AdapterFile($this->pathFileAdapter, TRUE);
        foreach($this->adapterList as $adapter) {
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $url = $adapter->getFileLocation($this->fileAdapter);
            $handle = curl_init($url);
            curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);

            $this->assertContains($httpCode, array('404', '403'));
        }
    }

    public function testGetFileLocation()
    {
        foreach($this->adapterList as $adapter) {
            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $url = $adapter->getFileLocation($this->fileAdapter);
            $this->assertEquals(file_get_contents($url), file_get_contents($this->pathFileLocal));
        }
    }

    public function testGetFileContent()
    {
        foreach($this->adapterList as $adapter) {

            $adapter->copyTempToAdapter($this->pathFileLocal, $this->fileAdapter);

            $url = $adapter->getFileLocation($this->fileAdapter);

            $this->assertEquals($adapter->getFileContent($this->fileAdapter), file_get_contents($url));


            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, true);

            $adapter->unlink($this->fileAdapter);

            $resultIsFile = $adapter->isFile($this->fileAdapter);
            $this->assertEquals($resultIsFile, false);

        }
    }

}
