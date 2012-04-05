<?php

namespace Kitpages\FileSystemBundle\ValueObject;


interface AdapterFileInterface  {

    function __construct($path, $isPrivate = true);

    function getPath();

    function getIsPrivate();

}