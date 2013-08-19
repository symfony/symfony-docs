The Class Loader Component
==========================

    The Class Loader Component loads your project classes automatically.

Whenever you use an undefined class, PHP uses the autoloading mechanism to
delegate the loading of a file defining the class. Symfony2 provides two
autoloaders, which are able to load your classes:

* :doc:`A PSR-0 class loader<class_loader>`

* :doc:`Load classes based on class-to-file mapping<map_class_loader>`

Additionally, the Symfony Class Loader Component ships with a set of wrapper
classes which can be used to add additional functionality on top of existing
autoloaders:

* :doc:`cache_class_loader`

* :doc:`debug_class_loader`

Installation
------------

You can install the component in 2 different ways:

* Use the official Git repository (https://github.com/symfony/ClassLoader);
* :doc:`Install it via Composer </components/using_components>` (``symfony/class-loader``
  on `Packagist`_).

.. _Packagist: https://packagist.org/packages/symfony/class-loader
