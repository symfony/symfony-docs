.. index::
   pair: Autoloader; Configuration
   single: Components; ClassLoader

The ClassLoader Component
=========================

    The ClassLoader Component loads your project classes automatically if they
    follow some standard PHP conventions.

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

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/ClassLoader);
* Install it via PEAR ( `pear.symfony.com/ClassLoader`);
* Install it via Composer (`symfony/class-loader` on Packagist).

Usage
-----

.. versionadded:: 2.1
   The ``useIncludePath`` method was added in Symfony 2.1.

Registering the :class:`Symfony\\Component\\ClassLoader\\UniversalClassLoader`
autoloader is straightforward::

    require_once '/path/to/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';

    use Symfony\Component\ClassLoader\UniversalClassLoader;

    $loader = new UniversalClassLoader();

    // You can search the include_path as a last resort.
    $loader->useIncludePath(true);

    // ... register namespaces and prefixes here - see below

    $loader->register();

For minor performance gains class paths can be cached in memory using APC by
registering the :class:`Symfony\\Component\\ClassLoader\\ApcUniversalClassLoader`::

    require_once '/path/to/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
    require_once '/path/to/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

    use Symfony\Component\ClassLoader\ApcUniversalClassLoader;

    $loader = new ApcUniversalClassLoader('apc.prefix.');
    $loader->register();

The autoloader is useful only if you add some libraries to autoload.

.. note::

    The autoloader is automatically registered in a Symfony2 application (see
    ``app/autoload.php``).

If the classes to autoload use namespaces, use the
:method:`Symfony\\Component\\ClassLoader\\UniversalClassLoader::registerNamespace`
or
:method:`Symfony\\Component\\ClassLoader\\UniversalClassLoader::registerNamespaces`
methods::

    $loader->registerNamespace('Symfony', __DIR__.'/vendor/symfony/symfony/src');

    $loader->registerNamespaces(array(
        'Symfony' => __DIR__.'/../vendor/symfony/symfony/src',
        'Monolog' => __DIR__.'/../vendor/monolog/monolog/src',
    ));

    $loader->register();

For classes that follow the PEAR naming convention, use the
:method:`Symfony\\Component\\ClassLoader\\UniversalClassLoader::registerPrefix`
or
:method:`Symfony\\Component\\ClassLoader\\UniversalClassLoader::registerPrefixes`
methods::

    $loader->registerPrefix('Twig_', __DIR__.'/vendor/twig/twig/lib');

    $loader->registerPrefixes(array(
        'Swift_' => __DIR__.'/vendor/swiftmailer/swiftmailer/lib/classes',
        'Twig_'  => __DIR__.'/vendor/twig/twig/lib',
    ));

    $loader->register();

.. note::

    Some libraries also require their root path be registered in the PHP
    include path (``set_include_path()``).

Classes from a sub-namespace or a sub-hierarchy of PEAR classes can be looked
for in a location list to ease the vendoring of a sub-set of classes for large
projects::

    $loader->registerNamespaces(array(
        'Doctrine\\Common'           => __DIR__.'/vendor/doctrine/common/lib',
        'Doctrine\\DBAL\\Migrations' => __DIR__.'/vendor/doctrine/migrations/lib',
        'Doctrine\\DBAL'             => __DIR__.'/vendor/doctrine/dbal/lib',
        'Doctrine'                   => __DIR__.'/vendor/doctrine/orm/lib',
    ));

    $loader->register();

In this example, if you try to use a class in the ``Doctrine\Common`` namespace
or one of its children, the autoloader will first look for the class under the
``doctrine-common`` directory, and it will then fallback to the default
``Doctrine`` directory (the last one configured) if not found, before giving up.
The order of the registrations is significant in this case.

.. _standards: http://symfony.com/PSR0
.. _PEAR:      http://pear.php.net/manual/en/standards.php
