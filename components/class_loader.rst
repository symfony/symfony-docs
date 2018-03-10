.. index::
    single: Components; ClassLoader

The ClassLoader Component
=========================

    The ClassLoader component provides tools to autoload your classes and
    cache their locations for performance.

.. caution::

    The ClassLoader component was deprecated in Symfony 3.3 and it will be
    removed in 4.0. As an alternative, use Composer's class loading mechanism.

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

.. code-block:: terminal

    $ composer require symfony/class-loader

Alternatively, you can clone the `<https://github.com/symfony/class-loader>`_ repository.

.. include:: /components/require_autoload.rst.inc

Learn More
----------

.. toctree::
    :glob:
    :maxdepth: 1

    class_loader/class_loader
    class_loader/*

.. _PSR-0: https://www.php-fig.org/psr/psr-0/
.. _PSR-4: https://www.php-fig.org/psr/psr-4/
.. _`autoloading mechanism`: https://php.net/manual/en/language.oop5.autoload.php
.. _Packagist: https://packagist.org/packages/symfony/class-loader
