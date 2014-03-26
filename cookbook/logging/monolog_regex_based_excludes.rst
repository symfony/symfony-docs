.. index::
   single: Logging
   single: Logging; Exclude 404 Errors
   single: Monolog; Exclude 404 Errors

How to Configure Monolog to Exclude 404 Errors from the Log
===========================================================

.. versionadded:: 2.4
    This feature was introduced to the MonologBundle 2.4, which was first
    packaged with Symfony 2.4.

Sometimes your logs become flooded with unwanted 404 HTTP errors, for example,
when an attacker scans your app for some well-known application paths (e.g.
`/phpmyadmin`). When using a ``fingers_crossed`` handler, you can exclude
logging these 404 errors based on a regular expression in the MonologBundle
configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        monolog:
            handlers:
                main:
                    # ...
                    type: fingers_crossed
                    handler: ...
                    excluded_404s:
                        - ^/phpmyadmin

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd"
        >
            <monolog:config>
                <monolog:handler type="fingers_crossed" name="main" handler="...">
                    <!-- ... -->
                    <monolog:excluded-404>^/phpmyadmin</monolog:excluded-404>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array(
                    // ...
                    'type'          => 'fingers_crossed',
                    'handler'       => ...,
                    'excluded_404s' => array(
                        '^/phpmyadmin',
                    ),
                ),
            ),
        ));
