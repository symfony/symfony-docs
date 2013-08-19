.. index::
    single: Class Loader; MapClassLoader
    
MapClassLoader
==============

Introduction
------------

Additionally to any dynamic class loader (like the
:doc:`PSR-0 class loader</components/class_loader/class_loader>`) you can use
the :class:`Symfony\\Component\\ClassLoader\\MapClassLoader` to statically map
classes to files. This is useful if you use third-party libraries which don't
follow the `PSR-0`_ standards.

Usage
-----

Using it is as easy as passing your mapping to its constructor when creating
an instance of the ``MapClassLoader`` class::

    require_once '/path/to/src/Symfony/Component/ClassLoader/MapClassLoader';
    
    $mapping = array(
        'Foo' => '/path/to/Foo',
        'Bar' => '/path/to/Bar',
    );
    
    $loader = new MapClassLoader($mapping);
    
    $loader->register();
    
.. note::

    The default behavior is to append the ``MapClassLoader`` on the autoload
    stack. If you want to use it as the default autoloader, pass ``true``
    when calling the ``register()`` method. Your class loader will then be
    prepended on the autoload stack.

.. _PSR-0: http://symfony.com/PSR0
