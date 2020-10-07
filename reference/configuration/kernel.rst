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
* `Container Build Time`_

.. _configuration-kernel-charset:

Charset
~~~~~~~

**type**: ``string`` **default**: ``UTF-8``

This option defines the charset that is used in the application. This value is
exposed via the ``kernel.charset`` configuration parameter and the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getCharset` method.

To change this value, override the ``getCharset()`` method and return another
charset::

    // src/Kernel.php
    namespace App;

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

The name of the kernel isn't usually directly important - it's used in the
generation of cache files - and you probably will only change it when
:doc:`using applications with multiple kernels </configuration/multiple_kernels>`.

This value is exposed via the ``kernel.name`` configuration parameter and the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getName` method.

To change this setting, override the ``getName()`` method. Alternatively, move
your kernel into a different directory. For example, if you moved the kernel
into a ``foo/`` directory (instead of ``src/``), the kernel name will be ``foo``.

.. _configuration-kernel-project-directory:

Project Directory
~~~~~~~~~~~~~~~~~

**type**: ``string`` **default**: the directory of the project ``composer.json``

This returns the absolute path of the root directory of your Symfony project,
which is used by applications to perform operations with file paths relative to
the project's root directory.

By default, its value is calculated automatically as the directory where the
main ``composer.json`` file is stored. This value is exposed via the
``kernel.project_dir`` configuration parameter and the
:method:`Symfony\\Component\\HttpKernel\\Kernel::getProjectDir` method.

If you don't use Composer, or have moved the ``composer.json`` file location or
have deleted it entirely (for example in the production servers), you can
override the :method:`Symfony\\Component\\HttpKernel\\Kernel::getProjectDir`
method to return the right project directory::

    // src/Kernel.php
    namespace App;

    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    // ...

    class Kernel extends BaseKernel
    {
        // ...

        public function getProjectDir(): string
        {
            return \dirname(__DIR__);
        }
    }

Cache Directory
~~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->getProjectDir()/var/cache/$this->environment``

This returns the absolute path of the cache directory of your Symfony project.
It's calculated automatically based on the current
:ref:`environment <configuration-environments>`.

This value is exposed via the ``kernel.cache_dir`` configuration parameter and
the :method:`Symfony\\Component\\HttpKernel\\Kernel::getCacheDir` method. To
change this setting, override the ``getCacheDir()`` method to return the right
cache directory.

Log Directory
~~~~~~~~~~~~~

**type**: ``string`` **default**: ``$this->getProjectDir()/var/log``

.. deprecated:: 4.2

    The ``kernel.log_dir`` parameter was deprecated in Symfony 4.2,
    use ``kernel.logs_dir`` instead.

This returns the absolute path of the log directory of your Symfony project.
It's calculated automatically based on the current
:ref:`environment <configuration-environments>`.

This value is exposed via the ``kernel.logs_dir`` configuration parameter and
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
application, the build will not be strictly reproducible. If you care about
this, the solution is to use another configuration parameter called
``kernel.container_build_time`` and set it to a non-changing build time to
achieve a strict reproducible build:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            # ...
            kernel.container_build_time: '1234567890'

    .. code-block:: xml

        <!-- config/services.xml -->
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

        // config/services.php

        // ...
        $container->setParameter('kernel.container_build_time', '1234567890');

.. _`reproducible builds`: https://en.wikipedia.org/wiki/Reproducible_builds
