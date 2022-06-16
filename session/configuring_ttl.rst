.. index::
    single: Sessions, defining TTL

Configuring the Session TTL
===========================

Symfony by default will use PHP's ini setting ``session.gc_maxlifetime`` as
session lifetime. However if you :doc:`store sessions in a database </session/database>`
you can also configure your own TTL in the framework configuration or even at runtime.

Changing the ini setting is not possible once the session is started so if you
want to use a different TTL depending on which user is logged in, you really need
to do it at runtime using the callback method below.

.. _configuring-the-TTL:

Configuring the TTL
-------------------

You need to pass the TTL in the options array of the session handler you are using:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
                arguments:
                    - '@Redis'
                    - { 'ttl': 600 }

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler">
                <argument type="service" id="Redis"/>
                <argument type="collection">
                    <argument key="ttl">600</argument>
                </argument>
            </service>
        </services>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

        $services
            ->set(RedisSessionHandler::class)
            ->args([
                service('Redis'),
                ['ttl' => 600],
            ]);

.. _configuring-the-TTL-dynamically-at-runtime:

Configuring the TTL dynamically at runtime
------------------------------------------

If you would like to have a different TTL for different
users or sessions for whatever reason, this is also possible
by passing a callback as the TTL value. The callback then has
to return an integer which will be used as TTL.

The callback will be called right before the session is written.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
                arguments:
                    - '@Redis'
                    - { 'ttl': !closure '@my.ttl.handler' }
             
            my.ttl.handler:
                class: Some\InvokableClass # some class with an __invoke() method
                arguments:
                    # Inject whatever dependencies you need to be able to resolve a TTL for the current session
                    - '@security'

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler">
                <argument type="service" id="Redis"/>
                <argument type="collection">
                    <argument key="ttl" type="closure" id="my.ttl.handler"/>
                </argument>
            </service>
            <!-- some class with an __invoke() method -->
            <service id="my.ttl.handler" class="Some\InvokableClass">
                <!-- Inject whatever dependencies you need to be able to resolve a TTL for the current session -->
                <argument type="service" id="security"/>
            </service>
        </services>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

        $services
            ->set(RedisSessionHandler::class)
            ->args([
                service('Redis'),
                ['ttl' => closure(service('my.ttl.handler'))],
            ]);

        $services
            // some class with an __invoke() method
            ->set('my.ttl.handler', 'Some\InvokableClass')
            // Inject whatever dependencies you need to be able to resolve a TTL for the current session
            ->args([service('security')]);
