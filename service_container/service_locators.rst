.. index::
    single: DependencyInjection; Service Locators

Service Locators
================

Sometimes, a service needs access to several other services without being sure
that all of them will actually be used. In those cases, you may want the
instantiation of the services to be lazy. However, that's not possible using
the explicit dependency injection since services are not all meant to
be ``lazy`` (see :doc:`/service_container/lazy_services`).

A real-world example are applications that implement the `Command pattern`_
using a CommandBus to map command handlers by Command class names and use them
to handle their respective command when it is asked for::

    // ...
    class CommandBus
    {
        /**
         * @var CommandHandler[]
         */
        private $handlerMap;

        public function __construct(array $handlerMap)
        {
            $this->handlerMap = $handlerMap;
        }

        public function handle(Command $command)
        {
            $commandClass = get_class($command);

            if (!isset($this->handlerMap[$commandClass])) {
                return;
            }

            return $this->handlerMap[$commandClass]->handle($command);
        }
    }

    // ...
    $commandBus->handle(new FooCommand());

Considering that only one command is handled at a time, instantiating all the
other command handlers is unnecessary. A possible solution to lazy-load the
handlers could be to inject the whole dependency injection container::

        use Symfony\Component\DependencyInjection\ContainerInterface;

        class CommandBus
        {
            private $container;

            public function __construct(ContainerInterface $container)
            {
                $this->container = $container;
            }

            public function handle(Command $command)
            {
                $commandClass = get_class($command);

                if ($this->container->has($commandClass)) {
                    $handler = $this->container->get($commandClass);

                    return $handler->handle($command);
                }
            }
        }

However, injecting the entire container is discouraged because it gives too
broad access to existing services and it hides the actual dependencies of the
services.

**Service Locators** are intended to solve this problem by giving access to a
set of predefined services while instantiating them only when actually needed.

Defining a Service Locator
--------------------------

First, define a new service for the service locator. Use its ``arguments``
option to include as many services as needed to it and add the
``container.service_locator`` tag to turn it into a service locator:

.. configuration-block::

    .. code-block:: yaml

        services:

            app.command_handler_locator:
                class: Symfony\Component\DependencyInjection\ServiceLocator
                tags: ['container.service_locator']
                arguments:
                    -
                        AppBundle\FooCommand: '@app.command_handler.foo'
                        AppBundle\BarCommand: '@app.command_handler.bar'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="app.command_handler_locator" class="Symfony\Component\DependencyInjection\ServiceLocator">
                    <argument type="collection">
                        <argument key="AppBundle\FooCommand" type="service" id="app.command_handler.foo" />
                        <argument key="AppBundle\BarCommand" type="service" id="app.command_handler.bar" />
                    </argument>
                    <tag name="container.service_locator" />
                </service>

            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\ServiceLocator;
        use Symfony\Component\DependencyInjection\Reference;

        //...

        $container
            ->register('app.command_handler_locator', ServiceLocator::class)
            ->addTag('container.service_locator')
            ->setArguments(array(array(
                'AppBundle\FooCommand' => new Reference('app.command_handler.foo'),
                'AppBundle\BarCommand' => new Reference('app.command_handler.bar'),
            )))
        ;

.. note::

    The services defined in the service locator argument must include keys,
    which later become their unique identifiers inside the locator.

Now you can use the service locator injecting it in any other service:

.. configuration-block::

    .. code-block:: yaml

        services:

            AppBundle\CommandBus:
                arguments: ['@app.command_handler_locator']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="AppBundle\CommandBus">
                    <argument type="service" id="app.command_handler.locator" />
                </service>

            </services>
        </container>

    .. code-block:: php

        use AppBundle\CommandBus;
        use Symfony\Component\DependencyInjection\Reference;

        //...

        $container
            ->register(CommandBus::class)
            ->setArguments(array(new Reference('app.command_handler_locator')))
        ;

.. tip::

    If the service locator is not intended to be used by multiple services, it's
    better to create and inject it as an anonymous service.

Usage
-----

Back to the previous CommandBus example, it looks like this when using the
service locator::

    // ...
    use Psr\Container\ContainerInterface;

    class CommandBus
    {
        /**
         * @var ContainerInterface
         */
        private $handlerLocator;

        // ...

        public function handle(Command $command)
        {
            $commandClass = get_class($command);

            if (!$this->handlerLocator->has($commandClass)) {
                return;
            }

            $handler = $this->handlerLocator->get($commandClass);

            return $handler->handle($command);
        }
    }

The injected service is an instance of :class:`Symfony\\Component\\DependencyInjection\\ServiceLocator`
which implements the PSR-11 ``ContainerInterface``, but it is also a callable::

    // ...
    $locateHandler = $this->handlerLocator;
    $handler = $locateHandler($commandClass);

    return $handler->handle($command);

.. _`Command pattern`: https://en.wikipedia.org/wiki/Command_pattern
