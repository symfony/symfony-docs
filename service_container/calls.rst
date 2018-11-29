.. index::
    single: DependencyInjection; Method Calls

Service Method Calls and Setter Injection
=========================================

Usually, you'll want to inject your dependencies via the constructor. But sometimes,
especially if a dependency is optional, you may want to use "setter injection". For
example::

    namespace AppBundle\Service;

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

        # app/config/services.yml
        services:
            app.message_generator:
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
                <service id="app.message_generator" class="AppBundle\Service\MessageGenerator">
                    <!-- ... -->
                    <call method="setLogger">
                        <argument type="service" id="logger" />
                    </call>
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Service\MessageGenerator;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register('app.message_generator', 'AppBundle\Service\MessageGenerator')
            ->addMethodCall('setLogger', array(new Reference('logger')));
