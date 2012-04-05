<?php

namespace Kitpages\FileSystemBundle\ValueObject;

use Kitpages\FileSystemBundle\ValueObject\AdapterFileInterface;

class AdapterFile implements AdapterFileInterface {

    protected $path = null;
    protected $isPrivate = true;

    public function __construct($path, $isPrivate = true)
    {
        $this->path = $path;
        $this->isPrivate = $isPrivate;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

}