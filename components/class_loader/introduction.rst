.. index::
    single: Components; ClassLoader

The ClassLoader Component
=========================

    The ClassLoader component provides tools to autoload your classes and
    cache their locations for performance.

Usage
-----

Whenever you reference a class that has not been required or included yet,
PHP uses the `autoloading mechanism`_ to delegate the loading of a file defining
the class. Symfony2 provides two autoloaders, which are able to load your classes:

* :doc:`/components/class_loader/class_loader`: loads classes that follow
  the `PSR-0` class naming standard;

* :doc:`/components/class_loader/map_class_loader`: loads classes using
  a static map from class name to file path.

Additionally, the Symfony ClassLoader component ships with a set of wrapper
classes which can be used to add additional functionality on top of existing
autoloaders:

* :doc:`/components/class_loader/cache_class_loader`
* :doc:`/components/class_loader/debug_class_loader`

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/class-loader``
  on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/ClassLoader).

.. _`autoloading mechanism`: http://php.net/manual/en/language.oop5.autoload.php
.. _Packagist: https://packagist.org/packages/symfony/class-loader
