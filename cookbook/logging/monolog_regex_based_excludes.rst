.. index::
   single: Logging
   single: Logging; Exclude 404 Errors
   single: Monolog; Exclude 404 Errors

How to Configure Monolog to Exclude 404 Errors from the Log
===========================================================

Sometimes you get your logs flooded with unwanted 404 HTTP errors, for example,
when an attacker scans your app for some well-known application paths (e.g.
`/phpmyadmin`). You can exclude logging these 404 errors based on a regular
expression in the MonologBundle configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                main:
                    # ...
                    excluded_404s:
                        - ^/phpmyadmin/

    .. code-block:: xml

        <!-- app/config/config_prod.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd"
        >
            <monolog:config>
                <monolog:handler name="main">
                    <!-- ... -->
                    <monolog:excluded-404>^/phpmyadmin/</monolog:excluded-404>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array(
                    // ...
                    'excluded_404s' => array(
                        '^/phpmyadmin/',
                    ),
                ),
            ),
        ));

.. note::

    To be able to use ``excluded_404s`` option you need to update your version
    of the MonologBundle to 2.4.
