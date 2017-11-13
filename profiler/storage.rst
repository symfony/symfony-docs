.. index::
    single: Profiling; Storage Configuration

Switching the Profiler Storage
==============================

In Symfony versions prior to 3.0, profiles could be stored in files, databases,
services like Redis and Memcache, etc. Starting from Symfony 3.0, the only storage
mechanism with built-in support is the filesystem.

By default the profile stores the collected data in the ``%kernel.cache_dir%/profiler/``
directory. If you want to use another location to store the profiles, define the
``dsn`` option of the ``framework.profiler``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            profiler:
                dsn: 'file:/tmp/symfony/profiler'

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:profiler dsn="file:/tmp/symfony/profiler" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php

        // ...
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'dsn' => 'file:/tmp/symfony/profiler',
            ),
        ));

You can also create your own profile storage service implementing the
:class:`Symfony\\Component\\HttpKernel\\Profiler\\ProfilerStorageInterface` and
overriding the ``profiler.storage`` service.
