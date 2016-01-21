.. index::
    single: Configuration reference; Kernel class

Configuring in the Kernel (e.g. AppKernel)
==========================================

Some configuration can be done on the kernel class itself (usually called
``app/AppKernel.php``). You can do this by overriding specific methods in
the parent :class:`Symfony\\Component\\HttpKernel\\Kernel` class.

Configuration
-------------

* `Charset`_
* `Kernel Name`_
* `Root Directory`_
* `Cache Directory`_
* `Log Directory`_

Charset
~~~~~~~

**type**: ``string`` **default**: ``UTF-8``

This returns the charset that is used in the application. To change it,
override the :method:`Symfony\\Component\\HttpKernel\\Kernel::getCharset`
method and return another charset, for instance::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function getCharset()
        {
            return 'ISO-8859-1';
        }
    }

Kernel Name
~~~~~~~~~~~

**type**: ``string`` **default**: ``app`` (i.e. the directory name holding
the kernel class)

To change this setting, override the :method:`Symfony\\Component\\HttpKernel\\Kernel::getName`
method. Alternatively, move your kernel into a different directory. For
example, if you moved the kernel into a ``foo`` directory (instead of ``app``),
the kernel name will be ``foo``.

The name of the kernel isn't usually directly important - it's used in the
generation of cache files. If you have an application with multiple kernels,
the easiest way to make each have a unique name is to duplicate the ``app``
directory and rename it to something else (e.g. ``foo``).

Root Directory
~~~~~~~~~~~~~~

**type**: ``string`` **default**: the directory of ``AppKernel``

This returns the root directory of your kernel. If you use the Symfony Standard
edition, the root directory refers to the ``app`` directory.

To change this setting, override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getRootDir` method::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        // ...

        public function getRootDir()
        {
            return realpath(parent::getRootDir().'/../');
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

**type**: ``string`` **default**: ``$this->rootDir/logs``

This returns the path to the log directory. To change it, override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getLogDir` method. Read
":ref:`override-logs-dir`" for more information.
