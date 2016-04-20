.. index::
    single: Profiling; Storage Configuration

Switching the Profiler Storage
==============================

By default the profile stores the collected data in files in the ``%kernel.cache_dir%/profiler/`` directory.
You can control the storage being used through the ``dsn``, ``username``,
``password`` and ``lifetime`` options. For example, the following configuration
uses MySQL as the storage for the profiler with a lifetime of one hour:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            profiler:
                dsn:      'mysql:host=localhost;dbname=%database_name%'
                username: '%database_user%'
                password: '%database_password%'
                lifetime: 3600

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"
        >
            <framework:config>
                <framework:profiler
                    dsn="mysql:host=localhost;dbname=%database_name%"
                    username="%database_user%"
                    password="%database_password%"
                    lifetime="3600"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php

        // ...
        $container->loadFromExtension('framework', array(
            'profiler' => array(
                'dsn'      => 'mysql:host=localhost;dbname=%database_name%',
                'username' => '%database_user',
                'password' => '%database_password%',
                'lifetime' => 3600,
            ),
        ));

The :doc:`HttpKernel component </components/http_kernel/introduction>` currently
supports the following profiler storage drivers:

* file
* sqlite
* mysql
* mongodb
* memcache
* memcached
* redis
