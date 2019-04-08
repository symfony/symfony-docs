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
* `Container Build Time`_

Charset
~~~~~~~

**type**: ``string`` **default**: ``UTF-8``

This option defines the charset that is used in the application. This value is
exposed via the ``kernel.charset`` configuration parameter and the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getCharset` method.

To change this value, override the ``getCharset()`` method and return another
charset:

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

The name of the kernel isn't usually directly important - it's used in the
generation of cache files. If you have an application with multiple kernels,
the easiest way to make each have a unique name is to duplicate the ``app``
directory and rename it to something else (e.g. ``foo``).

This value is exposed via the ``kernel.name`` configuration parameter and the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getName` method.

To change this setting, override the ``getName()`` method. Alternatively, move
your kernel into a different directory. For example, if you moved the kernel
into a ``foo`` directory (instead of ``app``), the kernel name will be ``foo``.

Root Directory
~~~~~~~~~~~~~~

.. deprecated:: 3.3

    The ``getRootDir()`` method is deprecated since Symfony 3.3. Use the new
    ``getProjectDir()`` method instead.

**type**: ``string`` **default**: the directory of ``AppKernel``

This returns the absolute path of the directory where your kernel class is
stored. If you use the Symfony Standard edition, this is the ``app/`` directory
of your project.

This value is exposed via the ``kernel.root_dir`` configuration parameter and
the :method:`Symfony\\Component\\HttpKernel\\Kernel::getRootDir` method. To
change this setting, override the ``getRootDir()`` method::

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

Project Directory
~~~~~~~~~~~~~~~~~

.. versionadded:: 3.3

    The ``getProjectDir()`` method was introduced in Symfony 3.3.

**type**: ``string`` **default**: the directory of the project ``composer.json``

This returns the absolute path of the root directory of your Symfony project.
It's calculated automatically as the directory where the main ``composer.json``
file is stored.

This value is exposed via the ``kernel.project_dir`` configuration parameter and
the :method:`Symfony\\Component\\HttpKernel\\Kernel::getProjectDir` method. To
change this setting, override the ``getProjectDir()`` method to return the right
project directory::

    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
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

This returns the absolute path of the cache directory of your Symfony project.
It's calculated automatically based on the current
:doc:`environment </configuration/environments>`.

This value is exposed via the ``kernel.cache_dir`` configuration parameter and
the :method:`Symfony\\Component\\HttpKernel\\Kernel::getCacheDir` method. To
change this setting, override the ``getCacheDir()`` method to return the right
cache directory.

Log Directory
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->rootDir/logs``

This returns the absolute path of the log directory of your Symfony project.
It's calculated automatically based on the current
:doc:`environment </configuration/environments>`.

This value is exposed via the ``kernel.log_dir`` configuration parameter and
the :method:`Symfony\\Component\\HttpKernel\\Kernel::getLogDir` method. To
change this setting, override the ``getLogDir()`` method to return the right
log directory.

Container Build Time
~~~~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: the result of executing ``time()``

Symfony follows the `reproducible builds`_ philosophy, which ensures that the
result of compiling the exact same source code doesn't produce different
results. This helps checking that a given binary or executable code was compiled
from some trusted source code.

In practice, the compiled :doc:`service container </service_container>` of your
application will always be the same if you don't change its source code. This is
exposed via these configuration parameters:

* ``container.build_hash``, a hash of the contents of all your source files;
* ``container.build_time``, a timestamp of the moment when the container was
  built (the result of executing PHP's :phpfunction:`time` function);
* ``container.build_id``, the result of merging the two previous parameters and
  encoding the result using CRC32.

Since the ``container.build_time`` value will change every time you compile the
application, the build will not be strictly reproducible. The solution is to use
another configuration parameter called ``kernel.container_build_time`` and set
it to a non-changing build time to achieve a strict reproducible build::

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        parameters:
            # ...
            kernel.container_build_time: '1234567890'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <!-- ... -->
                <parameter key="kernel.container_build_time">1234567890</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->setParameter('kernel.container_build_time', '1234567890');

.. _`reproducible builds`: https://en.wikipedia.org/wiki/Reproducible_builds
