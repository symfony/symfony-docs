.. index::
    single: Configuration reference; Kernel class

Configuration in the ``AppKernel``
==================================

Some configuration can be done in the ``AppKernel`` class (which is located in
``app/AppKernel.php``. You can do this by overriding the method of the
:class:`Symfony\\Component\\HttpKernel\\Kernel` class, which is extended by
the ``AppKernel`` class.

Configuration
-------------

* `kernel name`_
* `root directory`_
* `cache directory`_
* `log directory`_

kernel name
~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->name``

To change this setting, override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getName` method.

root directory
~~~~~~~~~~~~~~

**type**: ``string`` **default**: the directory of ``AppKernel``

This returns the root directory of you Symfony2 application. If you use the
Symfony Standard edition, the root directory refers to the ``app`` directory.

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

cache directory
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->rootDir/cache/$this->environment``

This returns the cache directory. You need to override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getCacheDir` method. Read
":ref:`override-cache-dir`" for more information.

log directory
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->rootDir/logs``

This returns the log directory. You need to override the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getLogDir` method. Read
":ref:`override-logs-dir`" for more information.
