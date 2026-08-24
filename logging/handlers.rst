Handlers
========

ElasticsearchLogstashHandler
----------------------------

This handler deals directly with the HTTP interface of Elasticsearch. This means
it will slow down your application if Elasticsearch takes time to answer. Even
if all HTTP calls are done asynchronously.

To use it, declare it as a service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler: ~

            # optionally, configure the handler using the constructor arguments (shown values are default)
            Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler:
                arguments:
                    $endpoint: "http://127.0.0.1:9200"
                    $index: "monolog"
                    $client: null
                    $level: !php/enum Monolog\Level::Debug
                    $bubble: true
                    $elasticsearchVersion: '1.0.0'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Monolog\Level;
        use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;

        return App::config([
            'services' => [
                ElasticsearchLogstashHandler::class => null,

                // optionally, configure the handler using the constructor arguments (shown values are default)
                ElasticsearchLogstashHandler::class => [
                    'arguments' => [
                        '$endpoint' => "http://127.0.0.1:9200",
                        '$index' => "monolog",
                        '$client' => null,
                        '$level' => Level::Debug,
                        '$bubble' => true,
                        '$elasticsearchVersion' => '1.0.0',
                    ],
                ],
            ],
        ]);

Then reference it in the Monolog configuration.

In a development environment, it's fine to keep the default configuration: for
each log, an HTTP request will be made to push the log to Elasticsearch:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                es:
                    type: service
                    id: Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler

    .. code-block:: php

        // config/packages/prod/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;

        return App::config([
            'monolog' => [
                'handlers' => [
                    'es' => [
                        'type' => 'service',
                        'id' => ElasticsearchLogstashHandler::class,
                    ],
                ],
            ],
        ]);

In a production environment, it's highly recommended to wrap this handler in a
handler with buffering capabilities (like the `FingersCrossedHandler`_ or
`BufferHandler`_) in order to call Elasticsearch only once with a bulk push. For
even better performance and fault tolerance, a proper `ELK stack`_ is recommended.

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                main:
                    type: fingers_crossed
                    handler: es

                es:
                    type: service
                    id: Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler

    .. code-block:: php

        // config/packages/prod/monolog.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;

        return App::config([
            'monolog' => [
                'handlers' => [
                    'main' => [
                        'type' => 'fingers_crossed',
                        'handler' => 'es',
                    ],
                    'es' => [
                        'type' => 'service',
                        'id' => ElasticsearchLogstashHandler::class,
                    ],
                ],
            ],
        ]);

.. _`BufferHandler`: https://github.com/Seldaek/monolog/blob/main/src/Monolog/Handler/BufferHandler.php
.. _`ELK stack`: https://www.elastic.co/what-is/elk-stack
.. _`FingersCrossedHandler`: https://github.com/Seldaek/monolog/blob/main/src/Monolog/Handler/FingersCrossedHandler.php
