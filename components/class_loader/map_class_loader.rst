.. index::
    single: ClassLoader; MapClassLoader
    
MapClassLoader
==============

The :class:`Symfony\\Component\\ClassLoader\\MapClassLoader` allows you to
autoload files via a static map from classes to files. This is useful if you
use third-party libraries which don't follow the `PSR-0`_ standards and so
can't use the :doc:`PSR-0 class loader </components/class_loader/class_loader>`.

The ``MapClassLoader`` can be used along with the :doc:`PSR-0 class loader </components/class_loader/class_loader>`
by configuring and calling the ``register()`` method on both.

.. note::

    The default behavior is to append the ``MapClassLoader`` on the autoload
    stack. If you want to use it as the first autoloader, pass ``true`` when
    calling the ``register()`` method. Your class loader will then be prepended
    on the autoload stack.

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

.. _PSR-0: http://www.php-fig.org/psr/psr-0/
