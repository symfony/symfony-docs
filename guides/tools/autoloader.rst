Autoloader
==========

Whenever you use an undefined class, PHP uses the autoloading mechanism to
delegate the loading of a file defining the class. Symfony2 provides a
"universal" autoloader, which is able to load classes from files that
implement one of the following conventions:

* The technical interoperability `standards`_ for PHP 5.3 namespaces and class
  names;

* The `PEAR`_ naming convention for classes.

If your classes and the third-party libraries you use for your project follow
these standards, the Symfony2 autoloader is the only autoloader you will ever
need.

Usage
-----

Registering the autoloader is straightforward::

    require_once '/path/to/src/Symfony/Foundation/UniversalClassLoader.php';

    use Symfony\Foundation\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->register();

The autoloader is useful only if you add some libraries to autoload.

.. note::
   The autoloader is automatically registered in a Symfony2 application (see
   ``src/autoload.php``).

If the classes to autoload use namespaces, use the ``registerNamespace()`` or
``registerNamespaces()`` methods::

    $loader->registerNamespace('Symfony', __DIR__.'/vendor/symfony/src');

    $loader->registerNamespaces(array(
      'Symfony' => __DIR__.'/vendor/symfony/src',
      'Zend'    => __DIR__.'/vendor/zend/library',
    ));

For classes that follow the PEAR naming convention, use the ``registerPrefix``
or ``registerPrefixes`` methods::

    $loader->registerPrefix('Twig_', __DIR__.'/vendor/twig/lib');

    $loader->registerPrefixes(array(
      'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
      'Twig_'  => __DIR__.'/vendor/twig/lib',
    ));

.. note::
   Some libraries also need that their root path be registered in the PHP include
   path (``set_include_path()``).

Classes from a sub-namespace or a sub-hierarchy of PEAR classes can be looked
for in a list of locations to ease the vendoring of a sub-set of classes for
large projects::

    $loader->registerNamespaces(array(
      'Doctrine\Common'          => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-common/lib',
      'Doctrine\DBAL\Migrations' => __DIR__.'/vendor/doctrine-migrations/lib',
      'Doctrine\DBAL'            => __DIR__.'/vendor/doctrine/lib/vendor/doctrine-dbal/lib',
      'Doctrine'                 => __DIR__.'/vendor/doctrine/lib',
    ));

In this example, if you try to use a class in the ``Doctrine\Common`` namespace
or one of its children, the autoloader will first look for the class under the
``doctrine-common`` directory, and it will then fallback to the default
``Doctrine`` directory (the last one configured) if not found, before giving up.
The order of the registrations is significant in this case.

.. _standards: http://groups.google.com/group/php-standards/web/psr-0-final-proposal
.. _PEAR:      http://pear.php.net/manual/en/standards.php
