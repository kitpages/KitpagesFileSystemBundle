<?php
namespace Kitpages\FileSystemBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Kitpages\FileSystemBundle\Model\AdapterFileInterface;

class AdapterFileEvent extends AbstractEvent
{
    protected $adapterFile = null;
    protected $idService = null;

    public function __construct($idService, AdapterFileInterface $adapterFile)
    {
        $this->setAdapterFile($adapterFile);
        $this->setIdService($idService);
    }

    /**
     * @Param String $file
     */
    public function setIdService($idService)
    {
        $this->idService = $idService;
    }
    /**
     * return String
     */
    public function getIdService()
    {
        return $this->idService;
    }


    /**
     * @Param AdapterFileInterface $file
     */
    public function setAdapterFile(AdapterFileInterface $adapterFile)
    {
        $this->adapterFile = $adapterFile;
    }
    /**
     * return AdapterFileInterface
     */
    public function getAdapterFile()
    {
        return $this->adapterFile;
    }
}
