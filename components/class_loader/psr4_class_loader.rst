.. index::
   single: ClassLoader; PSR-4 Class Loader

The PSR-4 Class Loader
======================

Libraries that follow the `PSR-4`_ standard can be loaded with the ``Psr4ClassLoader``.

.. note::

    If you manage your dependencies via Composer, you get a PSR-4 compatible
    autoloader out of the box. Use this loader in environments where Composer
    is not available.

.. tip::
    All Symfony Components follow PSR-4.

Usage
-----

The following example demonstrates, how you can use the
:class:`Symfony\\Component\\ClassLoader\\Psr4ClassLoader` autoloader to use
Symfony's Yaml component. Let's imagine, you downloaded both components –
ClassLoader and Yaml – as ZIP packages and unpacked them to a libs directory.

The directory structure will look like this:

.. code-block:: text

    /
    +- libs
    |  +- ClassLoader
    |  |  +- Psr4ClassLoader.php
    |  |  +- …
    |  +- Yaml
    |     +- Yaml.php
    |     +- …
    +- config.yml
    +- test.php

In ``demo.php``, to parse the file ``config.yml``, you can use the following
code.

.. code-block:: php

    use Symfony\Component\ClassLoader\Psr4ClassLoader;
    use Symfony\Component\Yaml\Yaml;

    require __DIR__ . '/lib/ClassLoader/Psr4ClassLoader.php';

    $loader = new Psr4ClassLoader();
    $loader->addPrefix('Symfony\\Component\\Yaml\\', __DIR__ . '/lib/Yaml');
    $loader->register();

    $data = Yaml::parse(__DIR__ . '/demo.yml');

First of all, we've loaded our class loader manually using ``require`` since we
don't have an autoload mechanism, yet. With the ``addPrefix()`` call, we told
the class loader where to look for classes with the namespace prefix
``Symfony\Component\Yaml\``. After registering the autoloader, the Yaml
component is ready to use.

.. _PSR-4: http://www.php-fig.org/psr/psr-4/
