Service Method Calls and Setter Injection
=========================================

.. tip::

    If you're using autowiring, you can use ``#[Required]`` or ``@required`` to
    :ref:`automatically configure method calls <autowiring-calls>`.

Usually, you'll want to inject your dependencies via the constructor. But sometimes,
especially if a dependency is optional, you may want to use "setter injection". For
example::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;

        public function setLogger(LoggerInterface $logger): void
        {
            $this->logger = $logger;
        }

        // ...
    }

To configure the container to call the ``setLogger`` method, use the ``calls`` key:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\MessageGenerator:
                # ...
                calls:
                    - setLogger: ['@logger']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MessageGenerator">
                    <!-- ... -->
                    <call method="setLogger">
                        <argument type="service" id="logger"/>
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MessageGenerator;

        return function(ContainerConfigurator $container) {
            // ...

            $services->set(MessageGenerator::class)
                // In versions earlier to Symfony 5.1 the service() function was called ref()
                ->call('setLogger', [service('logger')]);
        };

To provide immutable services, some classes implement immutable setters.
Such setters return a new instance of the configured class
instead of mutating the object they were called on::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;

        public function withLogger(LoggerInterface $logger): self
        {
            $new = clone $this;
            $new->logger = $logger;

            return $new;
        }

        // ...
    }

Because the method returns a separate cloned instance, configuring such a service means using
the return value of the wither method (``$service = $service->withLogger($logger);``).
The configuration to tell the container it should do so would be like:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Service\MessageGenerator:
                # ...
                calls:
                    - withLogger: !returns_clone ['@logger']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MessageGenerator">
                    <!-- ... -->
                    <call method="withLogger" returns-clone="true">
                        <argument type="service" id="logger"/>
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(MessageGenerator::class)
            ->addMethodCall('withLogger', [new Reference('logger')], true);

.. tip::

    If autowire is enabled, you can also use annotations; with the previous
    example it would be::

        /**
         * @required
         * @return static
         */
        public function withLogger(LoggerInterface $logger)
        {
            $new = clone $this;
            $new->logger = $logger;

            return $new;
        }

    You can also leverage the PHP 8 ``static`` return type instead of the
    ``@return static`` annotation. If you don't want a method with a
    PHP 8 ``static`` return type and a ``@required`` annotation to behave as
    a wither, you can add a ``@return $this`` annotation to disable the
    *returns clone* feature.

    .. versionadded:: 5.1

        Support for the PHP 8 ``static`` return type was introduced in
        Symfony 5.1.
