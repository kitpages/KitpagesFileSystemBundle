KitpagesFileSystemBundle
========================

This is a Symfony2 bundle that provides a filesystem abstraction layer. Basicaly this does the
same as the gaufrette library from KnpLabs, but it manages much more efficiently big files. We can
manage a 2GB file with a memory limit of 128Mo for the PHP process. We never transfert the entire
content in a $content variable.

With this bundle you can save your files on different filesystems (S3, Local filesystem, FTP,...)

Some elements of the configuration system are based on the code of the KnpGaufretteBundle.

Versions
========

2012-05-24 : v1.0.0
* first stable release

Actual state
============
This bundle is stable. The first adapters are :

* Local adapter : file system of the server
* S3 adapter : for Amazon Web Service AWS S3

Installation
============
You need to add the following lines in your deps :

Using [Composer](http://getcomposer.org/), just `$ composer require kitpages/file-system-bundle` package or:

``` javascript
{
  "require": {
    "kitpages/file-system-bundle": "dev-master"
  }
}
```

Only if you use AmazonS3
``` javascript
{
  "require": {
    amazonwebservices/aws-sdk-for-php: ~1.5
  }
}
```

Only if you use Flysystem
``` javascript
{
  "require": {
    "league/flysystem": "^1.0",
    "oneup/flysystem-bundle": "^1.14",
  }
}
```
AppKernel.php

```php
$bundles = array(
    ...
    new Kitpages\FileSystemBundle\KitpagesFileSystemBundle(),
);
```

// AWS SDK needs a special autoloader

```php
require_once __DIR__.'/../vendor/aws-sdk/sdk.class.php';
```

Configuration example
=====================
The following configuration defines 3 filesystems :

* kitpagesFile : a local filesystem
* kitpagesAmazon : a filesystem on Amazon S3
* kitpagesFlysystem : another filesystem abstraction

Let's see the configuration in config.yml

```yaml
kitpages_file_system:
    file_system_list:
        kitpagesFile:
            local:
                directory_public: %kernel.root_dir%/../web
                directory_private: %kernel.root_dir%
                base_url: %base_url%
        kitpagesAmazon:
            amazon_s3:
                bucket_name: %kitpagesFile_amazons3_bucketname%
                key: %kitpagesFile_amazons3_key%
                secret_key: %kitpagesFile_amazons3_secretkey%
        kitpagesFlysystem:
            flysystem:
                flysystem_adapter: oneup_flysystem.your_filesystem
                file_uri_prefix: https://your.custom.url/4687311687643/FRA/
```

Usage example
=============

```php
// use AdapterFile at the beginning of the file
use Kitpages\FileSystemBundle\Model\AdapterFile;

// get the adapter
$localAdapter = $this->get("kitpages_file_system.file_system.kitpagesFile");
$s3Adapter = $this->get("kitpages_file_system.file_system.kitpagesAmazon");

// private files (without direct public URL)
$adapter->copyTempToAdapter("/my_physical_dir/foo.txt", new AdapterFile("bar/foo.txt") );
$adapter->copyAdapterToTemp(new AdapterFile("bar/foo.txt"), "/my_physical_dir/foo.txt" );

// public files (with a direct URL given by the adapter)
$adapter->copyTempToAdapter("/my_physical_dir/foo.txt", new AdapterFile("bar/foo.txt", true) );
$url = $adapter->getFileLocation(new AdapterFile("bar/foo.txt", true));

// some functions of the adapter :
$adapterFile = new AdapterFile("bar/foo.txt");
$adapter->copyTempToAdapter("/my_physical_dir/foo.txt", $adapterFile );
$content = $adapter->getFileContent($adapterFile);
$adapter->sendFileToBrowser($adapterFile);
if ($adapter->isFile($adapterFile) ) {
    // if file exists in the adapter
}
```
