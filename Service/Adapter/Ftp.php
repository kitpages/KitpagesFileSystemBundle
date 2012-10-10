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

}
