.. index::
    single: Profiling; Storage Configuration

Switching the Profiler Storage
==============================

The profiler stores the collected data in the ``%kernel.cache_dir%/profiler/``
directory. If you want to use another location to store the profiles, define the
``dsn`` option of the ``framework.profiler``:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/dev/web_profiler.yaml
        framework:
            profiler:
                dsn: 'file:/tmp/symfony/profiler'

    .. code-block:: xml

        <!-- config/packages/dev/web_profiler.xml -->
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

        // config/packages/dev/web_profiler.php

        // ...
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'dsn' => 'file:/tmp/symfony/profiler',
            ),
        ));

You can also create your own profile storage service implementing the
:class:`Symfony\\Component\\HttpKernel\\Profiler\\ProfilerStorageInterface` and
overriding the ``profiler.storage`` service.
