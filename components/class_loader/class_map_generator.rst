.. index::
    single: Autoloading; Class Map Generator
    single: ClassLoader; Class Map Generator

The Class Map Generator
=======================

Loading a class usually is an easy task given the `PSR-0`_ and `PSR-4`_
standards. Thanks to the Symfony ClassLoader component or the autoloading
mechanism provided by Composer, you don't have to map your class names to
actual PHP files manually. Nowadays, PHP libraries usually come with autoloading
support through Composer.

But from time to time you may have to use a third-party library that comes
without any autoloading support and therefore forces you to load each class
manually. For example, imagine a library with the following directory structure:

.. code-block:: text

    library/
    ├── bar/
    │   ├── baz/
    │   │   └── Boo.php
    │   └── Foo.php
    └── foo/
        ├── bar/
        │   └── Foo.php
        └── Bar.php

These files contain the following classes:

===========================  ================
File                         Class Name
===========================  ================
``library/bar/baz/Boo.php``  ``Acme\Bar\Baz``
``library/bar/Foo.php``      ``Acme\Bar``
``library/foo/bar/Foo.php``  ``Acme\Foo\Bar``
``library/foo/Bar.php``      ``Acme\Foo``
===========================  ================

To make your life easier, the ClassLoader component comes with a
:class:`Symfony\\Component\\ClassLoader\\ClassMapGenerator` class that makes
it possible to create a map of class names to files.

Generating a Class Map
----------------------

To generate the class map, simply pass the root directory of your class
files to the
:method:`Symfony\\Component\\ClassLoader\\ClassMapGenerator::createMap`
method::

    use Symfony\Component\ClassLoader\ClassMapGenerator;

    var_dump(ClassMapGenerator::createMap(__DIR__.'/library'));

Given the files and class from the table above, you should see an output
like this:

.. code-block:: text

    Array
    (
        [Acme\Foo] => /var/www/library/foo/Bar.php
        [Acme\Foo\Bar] => /var/www/library/foo/bar/Foo.php
        [Acme\Bar\Baz] => /var/www/library/bar/baz/Boo.php
        [Acme\Bar] => /var/www/library/bar/Foo.php
    )

Dumping the Class Map
---------------------

Writing the class map to the console output is not really sufficient when
it comes to autoloading. Luckily, the ``ClassMapGenerator`` provides the
:method:`Symfony\\Component\\ClassLoader\\ClassMapGenerator::dump` method
to save the generated class map to the filesystem::

    use Symfony\Component\ClassLoader\ClassMapGenerator;

    ClassMapGenerator::dump(__DIR__.'/library', __DIR__.'/class_map.php');

This call to ``dump()`` generates the class map and writes it to the ``class_map.php``
file in the same directory with the following contents::

    <?php return array (
    'Acme\\Foo' => '/var/www/library/foo/Bar.php',
    'Acme\\Foo\\Bar' => '/var/www/library/foo/bar/Foo.php',
    'Acme\\Bar\\Baz' => '/var/www/library/bar/baz/Boo.php',
    'Acme\\Bar' => '/var/www/library/bar/Foo.php',
    );

Instead of loading each file manually, you'll only have to register the
generated class map with, for example, the
:class:`Symfony\\Component\\ClassLoader\\MapClassLoader`::

    use Symfony\Component\ClassLoader\MapClassLoader;

    $mapping = include __DIR__.'/class_map.php';
    $loader = new MapClassLoader($mapping);
    $loader->register();

    // you can now use the classes:
    use Acme\Foo;

    $foo = new Foo();

    // ...

.. note::

    The example assumes that you already have autoloading working (e.g.
    through `Composer`_ or one of the other class loaders from the ClassLoader
    component.

Besides dumping the class map for one directory, you can also pass an array
of directories for which to generate the class map (the result actually
is the same as in the example above)::

    use Symfony\Component\ClassLoader\ClassMapGenerator;

    ClassMapGenerator::dump(
        array(__DIR__.'/library/bar', __DIR__.'/library/foo'),
        __DIR__.'/class_map.php'
    );

.. _`PSR-0`: http://www.php-fig.org/psr/psr-0
.. _`PSR-4`: http://www.php-fig.org/psr/psr-4
.. _`Composer`: https://getcomposer.org
