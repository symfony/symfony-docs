How to Define a Custom Logging Formatter
========================================

Each logging handler uses a ``Formatter`` to format the record before logging
it. All Monolog handlers use an instance of
``Monolog\Formatter\LineFormatter`` by default but you can replace it.
Your formatter must implement ``Monolog\Formatter\FormatterInterface``.

For example, to use the built-in ``JsonFormatter`` only in the ``prod`` environment,
register it as a service then configure your handler to use it:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/monolog.yaml
        when@prod:
            monolog:
                handlers:
                    file:
                        type: stream
                        level: debug
                        formatter: 'monolog.formatter.json'

    .. code-block:: php

        // config/packages/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'when@prod' => [
                'monolog' => [
                    'handlers' => [
                        'file' => [
                            'type' => 'stream',
                            'level' => 'debug',
                            'formatter' => 'monolog.formatter.json',
                        ],
                    ],
                ],
            ],
        ]);

Many built-in formatters are available in Monolog. A lot of them are declared as services
and can be used in the ``formatter`` option:

* ``monolog.formatter.chrome_php``: formats a record according to the ChromePHP array format
* ``monolog.formatter.gelf_message``: serializes a format to GELF format
* ``monolog.formatter.html``: formats a record into an HTML table
* ``monolog.formatter.json``: serializes a record into a JSON object
* ``monolog.formatter.line``: formats a record into a one-line string
* ``monolog.formatter.loggly``: formats a record information into JSON in a format compatible with Loggly
* ``monolog.formatter.logstash``: serializes a record to Logstash Event Format
* ``monolog.formatter.normalizer``: normalizes a record to remove objects/resources so it's easier to dump to various targets
* ``monolog.formatter.scalar``: formats a record into an associative array of scalar (+ null) values (objects and arrays will be JSON encoded)
* ``monolog.formatter.wildfire``: serializes a record according to Wildfire's header requirements
