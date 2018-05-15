.. index::
   single: Logging
   single: Logging; Exclude HTTP Codes
   single: Monolog; Exclude HTTP Codes

How to Configure Monolog to Exclude Specific HTTP Codes from the Log
====================================================================

..versionadded:: 4.1
    The ability to exclude log messages based on their status codes was
    introduced in Symfony 4.1 and MonologBundle 3.3.

Sometimes your logs become flooded with unwanted HTTP errors, for example,
403s and 404s. When using a ``fingers_crossed`` handler, you can exclude
logging these HTTP codes based on the MonologBundle configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    # ...
                    type: fingers_crossed
                    handler: ...
                    excluded_http_codes: [403, 404, { 400: ['^/foo', '^/bar'] }]

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                http://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler type="fingers_crossed" name="main" handler="...">
                    <!-- ... -->
                    <monolog:excluded-http-code code="403">
                        <monolog:url>^/foo</monolog:url>
                        <monolog:url>^/bar</monolog:url>
                    </monolog:excluded-http-code>
                    <monolog:excluded-http-code code="404" />
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        $container->loadFromExtension('monolog', array(
            'handlers' => array(
                'main' => array(
                    // ...
                    'type'                => 'fingers_crossed',
                    'handler'             => ...,
                    'excluded_http_codes' => array(403, 404),
                ),
            ),
        ));
