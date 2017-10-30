.. index::
    single: DependencyInjection; Method Calls

Service Method Calls and Setter Injection
=========================================

.. tip::

    If you're using autowiring, you can use ``@required`` to
    :ref:`automatically configure method calls <autowiring-calls>`.

Usually, you'll want to inject your dependencies via the constructor. But sometimes,
especially if a dependency is optional, you may want to use "setter injection". For
example::

    namespace App\Service;

    use Psr\Log\LoggerInterface;

    class MessageGenerator
    {
        private $logger;

        public function setLogger(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        // ...
    }

To configure the container to call the ``setLogger`` method, use the ``calls`` key:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yaml
        services:
            App\Service\MessageGenerator:
                # ...
                calls:
                    - method: setLogger
                      arguments:
                          - '@logger'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MessageGenerator">
                    <!-- ... -->
                    <call method="setLogger">
                        <argument type="service" id="logger" />
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use App\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(MessageGenerator::class)
            ->addMethodCall('setLogger', array(new Reference('logger')));
