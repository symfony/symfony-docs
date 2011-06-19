.. index::
   single: Performance; Autoloader

How to improve the performance of the Autoloader
================================================

By default, the Symfony2 standard edition uses the ``UniversalClassLoader``
in the `autoloader.php`_ file. This class loader is mainly about convenience
as it can easily load classes from a variety of directories. However in order
for this to work this loader will iterate over all configured prefixes, making
``file_exists`` calls until it finally finds the file to include.

The simplest solution is to cache the location of each class after it's found
the first time. The ClassLoader component features an ``ApcUniversalClassLoader``
loader that extends the ``UniversalClassLoader`` and stores class locations
in APC.

In order to use this class loader simply adapt your autoloader as follows:

.. code-block:: php

    // app/autoload.php
    require __DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
    require __DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

    use Symfony\Component\ClassLoader\ApcUniversalClassLoader;

    $loader = new ApcUniversalClassLoader('some caching prefix');
    
    // ...

.. _`autoloader.php`: https://github.com/symfony/symfony-standard/blob/master/app/autoload.php
