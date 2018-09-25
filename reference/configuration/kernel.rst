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

To change this setting, override the :method:`Symfony\\Component\\HttpKernel\\Kernel::getName`
method. Alternatively, move your kernel into a different directory. For
example, if you moved the kernel into a ``foo/`` directory (instead of ``src/``),
the kernel name will be ``foo``.

The name of the kernel isn't usually directly important - it's used in the
generation of cache files - and you probably will only change it when
:doc:`using applications with multiple kernels </configuration/multiple_kernels>`.

Project Directory
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: the directory of the project ``composer.json``

This returns the root directory of your Symfony project. It's calculated as
the directory where the main ``composer.json`` file is stored.

If for some reason the ``composer.json`` file is not stored at the root of your
project, you can override the :method:`Symfony\\Component\\HttpKernel\\Kernel::getProjectDir`
method to return the right project directory::

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
