.. index::
    single: Components; ClassLoader

The ClassLoader Component
=========================

    The ClassLoader component provides tools to autoload your classes and
    cache their locations for performance.

.. caution::

    The ClassLoader component was deprecated in Symfony 3.3 and it will be
    removed in 4.0. As an alternative, use any of the `class loading optimizations`_
    provided by Composer.

Usage
-----

Whenever you reference a class that has not been required or included yet,
PHP uses the `autoloading mechanism`_ to delegate the loading of a file
defining the class. Symfony provides three autoloaders, which are able to
load your classes:

* :doc:`/components/class_loader/class_loader`: loads classes that follow
  the `PSR-0`_ class naming standard;

* :doc:`/components/class_loader/psr4_class_loader`: loads classes that follow
  the `PSR-4`_ class naming standard;

* :doc:`/components/class_loader/map_class_loader`: loads classes using
  a static map from class name to file path.

Additionally, the Symfony ClassLoader component ships with a wrapper class
which makes it possible
:doc:`to cache the results of a class loader </components/class_loader/cache_class_loader>`.

When using the :doc:`Debug component </components/debug>`, you
can also use a special :ref:`DebugClassLoader <component-debug-class-loader>`
that eases debugging by throwing more helpful exceptions when a class could
not be found by a class loader.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/class-loader``
  on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/class-loader).

.. include:: /components/require_autoload.rst.inc

Learn More
----------

.. toctree::
    :glob:
    :maxdepth: 1

    class_loader/class_loader
    class_loader/class_map_generator.rst
    class_loader/debug_class_loader.rst
    class_loader/map_class_loader.rst
    class_loader/psr4_class_loader.rst

.. toctree::
    :hidden:

    class_loader/cache_class_loader

.. _PSR-0: http://www.php-fig.org/psr/psr-0/
.. _PSR-4: http://www.php-fig.org/psr/psr-4/
.. _`autoloading mechanism`: http://php.net/manual/en/language.oop5.autoload.php
.. _Packagist: https://packagist.org/packages/symfony/class-loader
.. _`class loading optimizations`: https://getcomposer.org/doc/articles/autoloader-optimization.md
