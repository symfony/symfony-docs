.. index::
    single: Configuration reference; Kernel class

Configuring in the Kernel
=========================

Some configuration can be done on the kernel class itself (located by default at
``src/Kernel.php``). You can do this by overriding specific methods in
the parent :class:`Symfony\\Component\\HttpKernel\\Kernel` class.

Configuration
-------------

* `Charset`_
* `Kernel Name`_
* `Project Directory`_
* `Cache Directory`_
* `Log Directory`_

.. _configuration-kernel-charset:

Charset
~~~~~~~

**type**: ``string`` **default**: ``UTF-8``

This returns the charset that is used in the application. To change it,
override the :method:`Symfony\\Component\\HttpKernel\\Kernel::getCharset`
method and return another charset, for instance::

    // src/Kernel.php
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    // ...

    class Kernel extends BaseKernel
    {
        public function getCharset()
        {
            return 'ISO-8859-1';
        }
    }

Kernel Name
~~~~~~~~~~~

**type**: ``string`` **default**: ``src`` (i.e. the directory name holding
the kernel class)

.. deprecated:: 4.2

    The ``kernel.name`` parameter and the ``Kernel::getName()`` method were
    deprecated in Symfony 4.2. If you need a unique ID for your kernels use the
    ``kernel.container_class`` parameter or the ``Kernel::getContainerClass()`` method.

To change this setting, override the :method:`Symfony\\Component\\HttpKernel\\Kernel::getName`
method. Alternatively, move your kernel into a different directory. For
example, if you moved the kernel into a ``foo/`` directory (instead of ``src/``),
the kernel name will be ``foo``.

The name of the kernel isn't usually directly important - it's used in the
generation of cache files - and you probably will only change it when
:doc:`using applications with multiple kernels </configuration/multiple_kernels>`.

Project Directory
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: see explanation

This returns the root directory of your Symfony project, which is useful to
perform operations with file paths relative to your project's root. It's set by
default to the parent directory of ``public/`` or ``bin/`` (depending if it's
the front controller or the console script).

If you need to change the project directory, pass the new path as the third
argument of the ``new Kernel(...)`` instantiation both in ``public/index.php``
and ``bin/console`` files.

.. versionadded:: 4.3

    The third argument of the ``Kernel`` class constructor was introduced in
    Symfony 4.3. Also, in previous Symfony versions the project directory was
    set by default to the directory that contained the project's
    ``composer.json`` file.

An alternative solution is to override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getProjectDir` method in the
application kernel and return the right project directory path::

    // src/Kernel.php
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    // ...

    class Kernel extends BaseKernel
    {
        // ...

        public function getProjectDir()
        {
            return realpath(__DIR__.'/../');
        }
    }

Cache Directory
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->rootDir/cache/$this->environment``

This returns the path to the cache directory. To change it, override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getCacheDir` method. Read
":ref:`override-cache-dir`" for more information.

Log Directory
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->rootDir/log``

This returns the path to the log directory. To change it, override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getLogDir` method. Read
":ref:`override-logs-dir`" for more information.
