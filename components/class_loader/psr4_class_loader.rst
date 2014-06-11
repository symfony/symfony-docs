.. index::
   single: ClassLoader; PSR-4 Class Loader

The PSR-4 Class Loader
======================

.. versionadded:: 2.5
    The :class:`Symfony\\Component\\ClassLoader\\Psr4ClassLoader` was
    introduced in Symfony 2.5.

Libraries that follow the `PSR-4`_ standard can be loaded with the ``Psr4ClassLoader``.

.. note::

    If you manage your dependencies via Composer, you get a PSR-4 compatible
    autoloader out of the box. Use this loader in environments where Composer
    is not available.

.. tip::

    All Symfony components follow PSR-4.

Usage
-----

The following example demonstrates how you can use the
:class:`Symfony\\Component\\ClassLoader\\Psr4ClassLoader` autoloader to use
Symfony's Yaml component. Imagine, you downloaded both the ClassLoader and
Yaml component as ZIP packages and unpacked them to a ``libs`` directory.
The directory structure will look like this:

.. code-block:: text

    libs/
        ClassLoader/
            Psr4ClassLoader.php
            ...
        Yaml/
            Yaml.php
            ...
    config.yml
    demo.php

In ``demo.php`` you are going to parse the ``config.yml`` file. To do that, you
first need to configure the ``Psr4ClassLoader``:

.. code-block:: php

    use Symfony\Component\ClassLoader\Psr4ClassLoader;
    use Symfony\Component\Yaml\Yaml;

    require __DIR__.'/lib/ClassLoader/Psr4ClassLoader.php';

    $loader = new Psr4ClassLoader();
    $loader->addPrefix('Symfony\\Component\\Yaml\\', __DIR__.'/lib/Yaml');
    $loader->register();

    $data = Yaml::parse(__DIR__.'/config.yml');

First of all, the class loader is loaded manually using a ``require``
statement, since there is no autoload mechanism yet. With the
:method:`Symfony\\Component\\ClassLoader\\Psr4ClassLoader::addPrefix` call, you
tell the class loader where to look for classes with the
``Symfony\Component\Yaml\`` namespace prefix. After registering the autoloader,
the Yaml component is ready to be used.

.. _PSR-4: http://www.php-fig.org/psr/psr-4/
