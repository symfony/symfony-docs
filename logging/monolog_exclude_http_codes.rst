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

    .. code-block:: php

        // config/packages/prod/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'monolog' => [
                'handlers' => [
                    'main' => [
                        // ...
                        'type' => 'fingers_crossed',
                        'handler' => ...,
                        'excluded_http_codes' => [403, 404, ['400' => ['^/foo', '^/bar']]],
                    ],
                ],
            ],
        ]);

.. warning::

    Combining ``excluded_http_codes`` with a ``passthru_level`` lower than
    ``error`` (i.e. ``debug``, ``info``, ``notice`` or ``warning``) will not
    actually exclude log messages for those HTTP codes because they are logged
    with level of ``error`` or higher and ``passthru_level`` takes precedence
    over the HTTP codes being listed in ``excluded_http_codes``.
