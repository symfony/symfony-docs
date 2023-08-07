Handlers
========

ElasticsearchLogstashHandler
----------------------------

This handler deals directly with the HTTP interface of Elasticsearch. This means
it will slow down your application if Elasticsearch takes time to answer. Even
if all HTTP calls are done asynchronously.

In a development environment, it's fine to keep the default configuration: for
each log, an HTTP request will be made to push the log to Elasticsearch.

In a production environment, it's highly recommended to wrap this handler in a
handler with buffering capabilities (like the ``FingersCrossedHandler`` or
``BufferHandler``) in order to call Elasticsearch only once with a bulk push. For
even better performance and fault tolerance, a proper `ELK stack`_ is recommended.

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

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <services>
                <service id="Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler"/>

                <!-- optionally, configure the handler using the constructor arguments (shown values are default) -->
                <service id="Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler">
                    <argument key="endpoint">http://127.0.0.1:9200</argument>
                    <argument key="index">monolog</argument>
                    <argument key="client"/>
                    <argument key="level" type="enum">Monolog\Level::Debug</argument>
                    <argument key="bubble">true</argument>
                    <argument key="elasticsearchVersion">1.0.0</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Monolog\Level;
        use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;

        $container->register(ElasticsearchLogstashHandler::class);

        // optionally, configure the handler using the constructor arguments (shown values are default)
        $container->register(ElasticsearchLogstashHandler::class)
            ->setArguments([
                '$endpoint' => "http://127.0.0.1:9200",
                '$index' => "monolog",
                '$client' => null,
                '$level' => Level::Debug,
                '$bubble' => true,
                '$elasticsearchVersion' => '1.0.0',
            ])
        ;

Then reference it in the Monolog configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/prod/monolog.yaml
        monolog:
            handlers:
                es:
                    type: service
                    id: Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler

    .. code-block:: xml

        <!-- config/packages/prod/monolog.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:monolog="http://symfony.com/schema/dic/monolog"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/monolog
                https://symfony.com/schema/dic/monolog/monolog-1.0.xsd">

            <monolog:config>
                <monolog:handler
                    name="es"
                    type="service"
                    id="Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler"
                />
            </monolog:config>
        </container>

    .. code-block:: php

        // config/packages/prod/monolog.php
        use Symfony\Bridge\Monolog\Handler\ElasticsearchLogstashHandler;
        use Symfony\Config\MonologConfig;

        return static function (MonologConfig $monolog): void {
            $monolog->handler('es')
                ->type('service')
                ->id(ElasticsearchLogstashHandler::class)
            ;
        };

.. _`ELK stack`: https://www.elastic.co/what-is/elk-stack
