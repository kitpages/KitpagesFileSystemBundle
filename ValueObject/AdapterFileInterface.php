<?php

namespace Kitpages\FileSystemBundle\ValueObject;


interface AdapterFileInterface  {

    function __construct($path, $isPrivate = true, $mimeType = null);

    function getPath();

    function getIsPrivate();

    function getMimeType();

}