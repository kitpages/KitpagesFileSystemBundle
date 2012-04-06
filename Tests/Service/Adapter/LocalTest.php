<?php

namespace Kitpages\FileSystemBundle\Service\Adapter;

// external service
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;

use Kitpages\FileSystemBundle\ValueObject\AdapterFile;
use Kitpages\FileSystemBundle\Test\AbstractAdapterTest;

class LocalTest extends AbstractAdapterTest{ // extends \PHPUnit_Framework_TestCase{

    protected $adapterClass = 'Local';

}
