How to Configure Monolog to Exclude Specific HTTP Codes from the Log
====================================================================

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
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler type="fingers_crossed" name="main" handler="...">
                    <!-- ... -->
                    <monolog:excluded-http-code code="403">
                        <monolog:url>^/foo</monolog:url>
                        <monolog:url>^/bar</monolog:url>
                    </monolog:excluded-http-code>
                    <monolog:excluded-http-code code="404"/>
                </monolog:handler>
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog) {
            $mainHandler = $monolog->handler('main')
                // ...
                ->type('fingers_crossed')
                ->handler('...')
            ;

            $mainHandler->excludedHttpCode()->code(403);
            $mainHandler->excludedHttpCode()->code(404);
        };

.. caution::

    Combining ``excluded_http_codes`` with a ``passthru_level`` lower than
    ``error`` (i.e. ``debug``, ``info``, ``notice`` or ``warning``) will not
    actually exclude log messages for those HTTP codes because they are logged
    with level of ``error`` or higher and ``passthru_level`` takes precedence
    over the HTTP codes being listed in ``excluded_http_codes``.
