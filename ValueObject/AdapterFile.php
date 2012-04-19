<?php

namespace Kitpages\FileSystemBundle\ValueObject;

use Kitpages\FileSystemBundle\ValueObject\AdapterFileInterface;

class AdapterFile implements AdapterFileInterface {

    protected $path = null;
    protected $isPrivate = true;
    protected $mimeType = true;

    public function __construct($path, $isPrivate = true, $mimeType = null)
    {
        $this->path = $path;
        $this->isPrivate = $isPrivate;
        $this->mimeType = $mimeType;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

}