How to Disable Microseconds Precision (for a Performance Boost)
===============================================================

.. versionadded:: 2.11
    The ``use_microseconds`` option was introduced in MonologBundle 2.11.

Setting the parameter ``use_microseconds`` to ``false`` forces the logger to reduce
the precision in the ``datetime`` field of the log messages from microsecond to second,
avoiding a call to the ``microtime(true)`` function and the subsequent parsing.
Disabling the use of microseconds can provide a small performance gain speeding up the
log generation. This is recommended for systems that generate a large number of log events.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            use_microseconds: false
            handlers:
                applog:
                    type: stream
                    path: /var/log/symfony.log
                    level: error

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config use_microseconds="false">
                <monolog:handler
                    name="applog"
                    type="stream"
                    path="/var/log/symfony.log"
                    level="error"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'use_microseconds' => false,
            'handlers' => array(
                'applog' => array(
                    'type'  => 'stream',
                    'path'  => '/var/log/symfony.log',
                    'level' => 'error',
                ),
            ),
        ));
