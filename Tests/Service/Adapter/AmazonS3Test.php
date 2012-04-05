<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

// external service
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;

use Kitpages\FileSystemBundle\ValueObject\AdapterFile;

class AmazonS3Test  extends \PHPUnit_Framework_TestCase{
    ////
    // dependency injection
    ////
    protected $bucket = null;
    protected $s3 = null;
    protected $key = null;
    protected $secretKey = null;

    public function setUp()
    {
        if (!class_exists('\AmazonS3')) {
            $this->markTestSkipped('The zend amazon s3 service class is not available.');
        }

        $this->AmazonS3 = new AmazonS3('AKIAIKUZSXEHHE4DGJZA', 'prFha1QhoGTVkWsPLGBvoRMPzbPZLis/Zt3iSZJN', 'stream-wrapper');
        $this->pathFileLocal = dirname(__FILE__).'/../../tmp/testAmazon.txt';
        $this->pathFileAmazon = 'test/testAmazon.txt';
        $this->pathFileAmazon2 = 'test/testAmazon2.txt';
        $this->fileAmazon = new AdapterFile($this->pathFileAmazon, false);
        $this->fileAmazon2 = new AdapterFile($this->pathFileAmazon2, false);
        $fNew = fopen($this->pathFileLocal, 'w');
        fputs ($fNew, 'fichier de test');
        fclose($fNew);

    }

    public function tearDown()
    {
        if (is_file($this->pathFileLocal)) {
            unlink($this->pathFileLocal);
        }
        $this->AmazonS3->unlink($this->fileAmazon);
        $this->AmazonS3->unlink($this->fileAmazon2);
    }

    public function testCopyAndMove()
    {

        $result = $this->AmazonS3->moveTempToAdapter($this->pathFileLocal, $this->fileAmazon);
        $this->assertEquals(filesize($this->pathFileLocal), $result);
        unlink($this->pathFileLocal);

        $result = $this->AmazonS3->moveAdapterToTemp($this->fileAmazon, $this->pathFileLocal);
        $this->assertEquals(filesize($this->pathFileLocal), $result);

        $this->AmazonS3->copy($this->fileAmazon, $this->fileAmazon2);
        $resultIsFile = $this->AmazonS3->isFile($this->fileAmazon2);

        $this->assertEquals($resultIsFile, true);
    }

    public function testIsFile()
    {
        $resultIsFile = $this->AmazonS3->isFile($this->fileAmazon);
        $this->assertEquals($resultIsFile, false);

        $result = $this->AmazonS3->moveTempToAdapter($this->pathFileLocal, $this->fileAmazon);
        $this->assertEquals(filesize($this->pathFileLocal), $result);

        $resultIsFile = $this->AmazonS3->isFile($this->fileAmazon);
        $this->assertEquals($resultIsFile, true);

    }

    public function testUnlink()
    {
        $result = $this->AmazonS3->moveTempToAdapter($this->pathFileLocal, $this->fileAmazon);
        $this->assertEquals(filesize($this->pathFileLocal), $result);
        $this->AmazonS3->unlink($this->fileAmazon);

        $resultIsFile = $this->AmazonS3->isFile($this->fileAmazon);
        $this->assertEquals($resultIsFile, false);
    }

    //action
    public function testRename()
    {
        $result = $this->AmazonS3->moveTempToAdapter($this->pathFileLocal, $this->fileAmazon);
        $this->assertEquals(filesize($this->pathFileLocal), $result);

        $this->AmazonS3->rename($this->fileAmazon, $this->fileAmazon2);

        $resultIsFile = $this->AmazonS3->isFile($this->fileAmazon2);
        $this->assertEquals($resultIsFile, true);
    }


    // information

//    public function testImage()
//    {
//        $fileImageAmazon = new AdapterFile('tools.jpg', false);
//        $result = $this->AmazonS3->moveTempToAdapter('/home/webadmin/htdocs/sfkitsite/app/data/tmp/tools.jpg', $fileImageAmazon);
//        $this->AmazonS3->sendFileToBrowser($fileImageAmazon);
//        $this->AmazonS3->unlink($fileImageAmazon);
//    }

//    public function testSendFileToBrowser()
//    {
//        $result = $this->AmazonS3->moveTempToAdapter($this->pathFileLocal, $this->fileAmazon);
//        $this->assertEquals(filesize($this->pathFileLocal), $result);
//
//        $this->AmazonS3->sendFileToBrowser($this->fileAmazon);
//    }

    public function testGetFileLocation()
    {
        $result = $this->AmazonS3->moveTempToAdapter($this->pathFileLocal, $this->fileAmazon);
        $this->assertEquals(filesize($this->pathFileLocal), $result);

        $url = $this->AmazonS3->getFileLocation($this->fileAmazon);
        $this->assertEquals(file_get_contents($url), file_get_contents($this->pathFileLocal));

    }

}
