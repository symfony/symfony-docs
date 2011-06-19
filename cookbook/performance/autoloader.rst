.. index::
   single: Performance

How to improve the performance of the autoloader
================================================

By default the Symfony2 standard edition uses the ``UniversalClassLoader`` in the
`autoloader.php`_. This class loader is mainly about convenience as it can easily load
classes from a verity of directories. However in order for this to work this loader will
iterate over all configured prefixes, making ``file_exists`` calls until it finally
finds the file to include.

The simplest solution is to just cache when ever a file is found inside the APC byte code cache.
The ClassLoader component features an ``ApcUniversalClassLoader`` loader that extends the
``UniversalClassLoader`` that does exactly that.

In order to use this class loader simply adapt our autoloader as follows:

.. code-block:: php

    require __DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
    require __DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

    use Symfony\Component\ClassLoader\ApcUniversalClassLoader;

    $loader = new ApcUniversalClassLoader('some prefix');

.. _`autoloader.php`: https://github.com/symfony/symfony-standard/blob/master/app/autoload.php
