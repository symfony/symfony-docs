.. index::
    single: DependencyInjection; Service Locators

Service Locators
================

What is a Service Locator
-------------------------

Sometimes, a service needs the ability to access other services without being sure
that all of them will actually be used.

In such cases, you may want the instantiation of these services to be lazy, that is
not possible using explicit dependency injection since services are not all meant to
be ``lazy`` (see :doc:`/service_container/lazy_services`).

A real-world example being a CommandBus which maps command handlers by Command
class names and use them to handle their respective command when it is asked for::

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
            $commandClass = get_class($command)

            if (!isset($this->handlerMap[$commandClass])) {
                return;
            }

            return $this->handlerMap[$commandClass]->handle($command);
        }
    }

    // ...
    $commandBus->handle(new FooCommand());

Because only one command is handled at a time, other command handlers are not
used but unnecessarily instantiated.

A solution allowing to keep handlers lazily loaded could be to inject the whole
dependency injection container::

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
                $commandClass = get_class($command)

                if ($this->container->has($commandClass)) {
                    $handler = $this->container->get($commandClass);

                    return $handler->handle($command);
                }
            }
        }

But injecting the container has many drawbacks including:

- too broad access to existing services
- services which are actually useful are hidden

Service Locators are intended to solve this problem by giving access to a set of
identified services while instantiating them only when really needed.

Configuration
-------------

For injecting a service locator into your service(s), you first need to register
the service locator itself as a service using the `container.service_locator`
tag:

.. configuration-block::

    .. code-block:: yaml

        services:

            app.command_handler_locator:
                class: Symfony\Component\DependencyInjection\ServiceLocator
                arguments:
                    AppBundle\FooCommand: '@app.command_handler.foo'
                    AppBundle\BarCommand: '@app.command_handler.bar'
                tags: ['container.service_locator']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="app.command_handler_locator" class="Symfony\Component\DependencyInjection\ServiceLocator">
                    <argument key="AppBundle\FooCommand" type="service" id="app.command_handler.foo" />
                    <argument key="AppBundle\BarCommand" type="service" id="app.command_handler.bar" />
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
            ->setArguments(array(
                'AppBundle\FooCommand' => new Reference('app.command_handler.foo'),
                'AppBundle\BarCommand' => new Reference('app.command_handler.bar'),
            ))
        ;

.. note::

    The services defined in the service locator argument must be keyed.
    Those keys become their unique identifier inside the locator.


Now you can use it in your services by injecting it as needed:

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

    You should create and inject the service locator as an anonymous service if
    it is not intended to be used by multiple services

Usage
-----

Back to our CommandBus which now looks like::

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
