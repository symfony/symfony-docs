.. index::
   single: ClassLoader; PSR-0 Class Loader

The PSR-0 Class Loader
======================

If your classes and third-party libraries follow the `PSR-0`_ standard,
you can use the :class:`Symfony\\Component\\ClassLoader\\ClassLoader` class
to load all of your project's classes.

.. tip::

    You can use both the ``ApcClassLoader`` and the ``XcacheClassLoader``
    to :doc:`cache </components/class_loader/cache_class_loader>` a ``ClassLoader``
    instance.

Usage
-----

Registering the :class:`Symfony\\Component\\ClassLoader\\ClassLoader` autoloader
is straightforward::

    require_once '/path/to/src/Symfony/Component/ClassLoader/ClassLoader.php';

    use Symfony\Component\ClassLoader\ClassLoader;

    $loader = new ClassLoader();

    // to enable searching the include path (eg. for PEAR packages)
    $loader->setUseIncludePath(true);

    // ... register namespaces and prefixes here - see below

    $loader->register();

.. note::

    The autoloader is automatically registered in a Symfony application
    (see ``app/autoload.php``).

Use :method:`Symfony\\Component\\ClassLoader\\ClassLoader::addPrefix` or
:method:`Symfony\\Component\\ClassLoader\\ClassLoader::addPrefixes` to register
your classes::

    // register a single namespaces
    $loader->addPrefix('Symfony', __DIR__.'/vendor/symfony/symfony/src');

    // register several namespaces at once
    $loader->addPrefixes(array(
        'Symfony' => __DIR__.'/../vendor/symfony/symfony/src',
        'Monolog' => __DIR__.'/../vendor/monolog/monolog/src',
    ));

    // register a prefix for a class following the PEAR naming conventions
    $loader->addPrefix('Twig_', __DIR__.'/vendor/twig/twig/lib');

    $loader->addPrefixes(array(
        'Swift_' => __DIR__.'/vendor/swiftmailer/swiftmailer/lib/classes',
        'Twig_'  => __DIR__.'/vendor/twig/twig/lib',
    ));

Classes from a sub-namespace or a sub-hierarchy of `PEAR`_ classes can be
looked for in a location list to ease the vendoring of a sub-set of classes
for large projects::

    $loader->addPrefixes(array(
        'Doctrine\\Common'           => __DIR__.'/vendor/doctrine/common/lib',
        'Doctrine\\DBAL\\Migrations' => __DIR__.'/vendor/doctrine/migrations/lib',
        'Doctrine\\DBAL'             => __DIR__.'/vendor/doctrine/dbal/lib',
        'Doctrine'                   => __DIR__.'/vendor/doctrine/orm/lib',
    ));

In this example, if you try to use a class in the ``Doctrine\Common`` namespace
or one of its children, the autoloader will first look for the class under
the ``doctrine-common`` directory. If not found, it will then fallback to
the default ``Doctrine`` directory (the last one configured) before giving
up. The order of the prefix registrations is significant in this case.

.. _PEAR:  http://pear.php.net/manual/en/standards.naming.php
.. _PSR-0: http://www.php-fig.org/psr/psr-0/
