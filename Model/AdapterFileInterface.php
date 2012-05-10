<?php

namespace Kitpages\FileSystemBundle\Model;


interface AdapterFileInterface  {

    function __construct($path, $isPrivate = true, $mimeType = null);

    function getPath();

    function getIsPrivate();

    function getMimeType();

    function setMimeType($mimeType);

}