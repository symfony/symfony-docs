.. index::
    single: DependencyInjection; Service Subscribers

.. _service-locators:

Service Subscribers & Locators
==============================

Sometimes, a service needs access to several other services without being sure
that all of them will actually be used. In those cases, you may want the
instantiation of the services to be lazy. However, that's not possible using
the explicit dependency injection since services are not all meant to
be ``lazy`` (see :doc:`/service_container/lazy_services`).

This can typically be the case in your controllers, where you may inject several
services in the constructor, but the action called only uses some of them.
Another example are applications that implement the `Command pattern`_
using a CommandBus to map command handlers by Command class names and use them
to handle their respective command when it is asked for::

    // src/CommandBus.php
    namespace App;

    // ...
    class CommandBus
    {
        /**
         * @param CommandHandler[] $handlerMap
         */
        public function __construct(
            private array $handlerMap,
        ) {
        }

        public function handle(Command $command)
        {
            $commandClass = get_class($command);

            if (!$handler = $this->handlerMap[$commandClass] ?? null) {
                return;
            }

            return $handler->handle($command);
        }
    }

    // ...
    $commandBus->handle(new FooCommand());

Considering that only one command is handled at a time, instantiating all the
other command handlers is unnecessary. A possible solution to lazy-load the
handlers could be to inject the main dependency injection container.

However, injecting the entire container is discouraged because it gives too
broad access to existing services and it hides the actual dependencies of the
services. Doing so also requires services to be made public, which isn't the
case by default in Symfony applications.

**Service Subscribers** are intended to solve this problem by giving access to a
set of predefined services while instantiating them only when actually needed
through a **Service Locator**, a separate lazy-loaded container.

Defining a Service Subscriber
-----------------------------

First, turn ``CommandBus`` into an implementation of :class:`Symfony\\Contracts\\Service\\ServiceSubscriberInterface`.
Use its ``getSubscribedServices()`` method to include as many services as needed
in the service subscriber::

    // src/CommandBus.php
    namespace App;

    use App\CommandHandler\BarHandler;
    use App\CommandHandler\FooHandler;
    use Psr\Container\ContainerInterface;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;

    class CommandBus implements ServiceSubscriberInterface
    {
        public function __construct(
            private ContainerInterface $locator,
        ) {
        }

        public static function getSubscribedServices(): array
        {
            return [
                'App\FooCommand' => FooHandler::class,
                'App\BarCommand' => BarHandler::class,
            ];
        }

        public function handle(Command $command)
        {
            $commandClass = get_class($command);

            if ($this->locator->has($commandClass)) {
                $handler = $this->locator->get($commandClass);

                return $handler->handle($command);
            }
        }
    }

.. tip::

    If the container does *not* contain the subscribed services, double-check
    that you have :ref:`autoconfigure <services-autoconfigure>` enabled. You
    can also manually add the ``container.service_subscriber`` tag.

The injected service is an instance of :class:`Symfony\\Component\\DependencyInjection\\ServiceLocator`
which implements both the PSR-11 ``ContainerInterface`` and :class:`Symfony\\Contracts\\Service\\ServiceProviderInterface`.
It is also a callable and a countable::

    // ...
    $numberOfHandlers = count($this->locator);
    $nameOfHandlers = array_keys($this->locator->getProvidedServices());
    // ...
    $handler = ($this->locator)($commandClass);

    return $handler->handle($command);

Including Services
------------------

In order to add a new dependency to the service subscriber, use the
``getSubscribedServices()`` method to add service types to include in the
service locator::

    use Psr\Log\LoggerInterface;

    public static function getSubscribedServices(): array
    {
        return [
            // ...
            LoggerInterface::class,
        ];
    }

Service types can also be keyed by a service name for internal use::

    use Psr\Log\LoggerInterface;

    public static function getSubscribedServices(): array
    {
        return [
            // ...
            'logger' => LoggerInterface::class,
        ];
    }

When extending a class that also implements ``ServiceSubscriberInterface``,
it's your responsibility to call the parent when overriding the method. This
typically happens when extending ``AbstractController``::

    use Psr\Log\LoggerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class MyController extends AbstractController
    {
        public static function getSubscribedServices(): array
        {
            return array_merge(parent::getSubscribedServices(), [
                // ...
                'logger' => LoggerInterface::class,
            ]);
        }
    }

Optional Services
~~~~~~~~~~~~~~~~~

For optional dependencies, prepend the service type with a ``?`` to prevent
errors if there's no matching service found in the service container::

    use Psr\Log\LoggerInterface;

    public static function getSubscribedServices(): array
    {
        return [
            // ...
            '?'.LoggerInterface::class,
        ];
    }

.. note::

    Make sure an optional service exists by calling ``has()`` on the service
    locator before calling the service itself.

Aliased Services
~~~~~~~~~~~~~~~~

By default, autowiring is used to match a service type to a service from the
service container. If you don't use autowiring or need to add a non-traditional
service as a dependency, use the ``container.service_subscriber`` tag to map a
service type to a service.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\CommandBus:
                tags:
                    - { name: 'container.service_subscriber', key: 'logger', id: 'monolog.logger.event' }

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="App\CommandBus">
                    <tag name="container.service_subscriber" key="logger" id="monolog.logger.event"/>
                </service>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\CommandBus;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(CommandBus::class)
                ->tag('container.service_subscriber', ['key' => 'logger', 'id' => 'monolog.logger.event']);
        };

.. tip::

    The ``key`` attribute can be omitted if the service name internally is the
    same as in the service container.

Add Dependency Injection Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.2

    The ability to add attributes was introduced in Symfony 6.2.

As an alternate to aliasing services in your configuration, you can also configure
the following dependency injection attributes in the ``getSubscribedServices()``
method directly:

* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Autowire`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\TaggedIterator`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\TaggedLocator`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Target`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\MapDecorated`

This is done by having ``getSubscribedServices()`` return an array of
:class:`Symfony\\Contracts\\Service\\Attribute\\SubscribedService` objects
(these can be combined with standard ``string[]`` values)::

    use Psr\Container\ContainerInterface;
    use Psr\Log\LoggerInterface;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
    use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Contracts\Service\Attribute\SubscribedService;

    public static function getSubscribedServices(): array
    {
        return [
            // ...
            new SubscribedService('logger', LoggerInterface::class, attributes: new Autowire(service: 'monolog.logger.event')),

            // can event use parameters
            new SubscribedService('env', 'string', attributes: new Autowire('%kernel.environment%')),

            // Target
            new SubscribedService('event.logger', LoggerInterface::class, attributes: new Target('eventLogger')),

            // TaggedIterator
            new SubscribedService('loggers', 'iterable', attributes: new TaggedIterator('logger.tag')),

            // TaggedLocator
            new SubscribedService('handlers', ContainerInterface::class, attributes: new TaggedLocator('handler.tag')),
        ];
    }

.. note::

    The above example requires using ``3.2`` version or newer of ``symfony/service-contracts``.

Defining a Service Locator
--------------------------

To manually define a service locator and inject it to another service, create an
argument of type ``service_locator``.

Consider the following ``CommandBus`` class where you want to inject
some services into it via a service locator::

    // src/CommandBus.php
    namespace App;

    use Psr\Container\ContainerInterface;

    class CommandBus
    {
        public function __construct(
            private ContainerInterface $locator,
        ) {
        }
    }

Symfony allows you to inject the service locator using YAML/XML/PHP configuration
or directly via PHP attributes:

.. configuration-block::

    .. code-block:: php-attributes

        // src/CommandBus.php
        namespace App;

        use Psr\Container\ContainerInterface;
        use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;

        class CommandBus
        {
            public function __construct(
                // creates a service locator with all the services tagged with 'app.handler'
                #[TaggedLocator('app.handler')]
                private ContainerInterface $locator,
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\CommandBus:
                arguments:
                  - !service_locator
                      App\FooCommand: '@app.command_handler.foo'
                      App\BarCommand: '@app.command_handler.bar'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\CommandBus">
                    <argument type="service_locator">
                        <argument key="App\FooCommand" type="service" id="app.command_handler.foo"/>
                        <argument key="App\BarCommand" type="service" id="app.command_handler.bar"/>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\CommandBus;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(CommandBus::class)
                ->args([service_locator([
                    'App\FooCommand' => service('app.command_handler.foo'),
                    'App\BarCommand' => service('app.command_handler.bar'),
                ])]);
        };

As shown in the previous sections, the constructor of the ``CommandBus`` class
must type-hint its argument with ``ContainerInterface``. Then, you can get any of
the service locator services via their ID (e.g. ``$this->locator->get('App\FooCommand')``).

Reusing a Service Locator in Multiple Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you inject the same service locator in several services, it's better to
define the service locator as a stand-alone service and then inject it in the
other services. To do so, create a new service definition using the
``ServiceLocator`` class:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            app.command_handler_locator:
                class: Symfony\Component\DependencyInjection\ServiceLocator
                arguments:
                    -
                        App\FooCommand: '@app.command_handler.foo'
                        App\BarCommand: '@app.command_handler.bar'
                # if you are not using the default service autoconfiguration,
                # add the following tag to the service definition:
                # tags: ['container.service_locator']

            # if the element has no key, the ID of the original service is used
            app.another_command_handler_locator:
                class: Symfony\Component\DependencyInjection\ServiceLocator
                arguments:
                    -
                        - '@app.command_handler.baz'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="app.command_handler_locator" class="Symfony\Component\DependencyInjection\ServiceLocator">
                    <argument type="collection">
                        <argument key="App\FooCommand" type="service" id="app.command_handler.foo"/>
                        <argument key="App\BarCommand" type="service" id="app.command_handler.bar"/>
                        <!-- if the element has no key, the ID of the original service is used -->
                        <argument type="service" id="app.command_handler.baz"/>
                    </argument>
                    <!--
                        if you are not using the default service autoconfiguration,
                        add the following tag to the service definition:
                        <tag name="container.service_locator"/>
                    -->
                </service>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\DependencyInjection\ServiceLocator;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set('app.command_handler_locator', ServiceLocator::class)
                ->args([[
                    'App\FooCommand' => service('app.command_handler.foo'),
                    'App\BarCommand' => service('app.command_handler.bar'),
                ]])
                // if you are not using the default service autoconfiguration,
                // add the following tag to the service definition:
                // ->tag('container.service_locator')
            ;

            // if the element has no key, the ID of the original service is used
            $services->set('app.another_command_handler_locator', ServiceLocator::class)
                ->args([[
                    service('app.command_handler.baz'),
                ]])
            ;
        };

.. note::

    The services defined in the service locator argument must include keys,
    which later become their unique identifiers inside the locator.

Now you can inject the service locator in any other services:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\CommandBus:
                arguments: ['@app.command_handler_locator']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <service id="App\CommandBus">
                    <argument type="service" id="app.command_handler_locator"/>
                </service>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\CommandBus;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(CommandBus::class)
                ->args([service('app.command_handler_locator')]);
        };

Using Service Locators in Compiler Passes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In :doc:`compiler passes </service_container/compiler_passes>` it's recommended
to use the :method:`Symfony\\Component\\DependencyInjection\\Compiler\\ServiceLocatorTagPass::register`
method to create the service locators. This will save you some boilerplate and
will share identical locators among all the services referencing them::

    use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    public function process(ContainerBuilder $containerBuilder): void
    {
        // ...

        $locateableServices = [
            // ...
            'logger' => new Reference('logger'),
        ];

        $myService = $containerBuilder->findDefinition(MyService::class);

        $myService->addArgument(ServiceLocatorTagPass::register($containerBuilder, $locateableServices));
    }

Indexing the Collection of Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Services passed to the service locator can define their own index using an
arbitrary attribute whose name is defined as ``index_by`` in the service locator.

In the following example, the ``App\Handler\HandlerCollection`` locator receives
all services tagged with ``app.handler`` and they are indexed using the value
of the ``key`` tag attribute (as defined in the ``index_by`` locator option):

.. configuration-block::

    .. code-block:: php-attributes

        // src/CommandBus.php
        namespace App;

        use Psr\Container\ContainerInterface;
        use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;

        class CommandBus
        {
            public function __construct(
                #[TaggedLocator('app.handler', indexAttribute: 'key')]
                private ContainerInterface $locator,
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Handler\One:
                tags:
                    - { name: 'app.handler', key: 'handler_one' }

            App\Handler\Two:
                tags:
                    - { name: 'app.handler', key: 'handler_two' }

            App\Handler\HandlerCollection:
                # inject all services tagged with app.handler as first argument
                arguments: [!tagged_locator { tag: 'app.handler', index_by: 'key' }]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Handler\One">
                    <tag name="app.handler" key="handler_one"/>
                </service>

                <service id="App\Handler\Two">
                    <tag name="app.handler" key="handler_two"/>
                </service>

                <service id="App\HandlerCollection">
                    <!-- inject all services tagged with app.handler as first argument -->
                    <argument type="tagged_locator" tag="app.handler" index-by="key"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(App\Handler\One::class)
                ->tag('app.handler', ['key' => 'handler_one'])
            ;

            $services->set(App\Handler\Two::class)
                ->tag('app.handler', ['key' => 'handler_two'])
            ;

            $services->set(App\Handler\HandlerCollection::class)
                // inject all services tagged with app.handler as first argument
                ->args([tagged_locator('app.handler', 'key')])
            ;
        };

Inside this locator you can retrieve services by index using the value of the
``key`` attribute. For example, to get the ``App\Handler\Two`` service::

    // src/Handler/HandlerCollection.php
    namespace App\Handler;

    use Psr\Container\ContainerInterface;

    class HandlerCollection
    {
        public function getHandlerTwo(ContainerInterface $locator)
        {
            return $locator->get('handler_two');
        }

        // ...
    }

Instead of defining the index in the service definition, you can return its
value in a method called ``getDefaultIndexName()`` inside the class associated
to the service::

    // src/Handler/One.php
    namespace App\Handler;

    class One
    {
        public static function getDefaultIndexName(): string
        {
            return 'handler_one';
        }

        // ...
    }

If you prefer to use another method name, add a ``default_index_method``
attribute to the locator service defining the name of this custom method:

.. configuration-block::

    .. code-block:: php-attributes

        // src/CommandBus.php
        namespace App;

        use Psr\Container\ContainerInterface;
        use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;

        class CommandBus
        {
            public function __construct(
                #[TaggedLocator('app.handler', 'key', defaultIndexMethod: 'myOwnMethodName')]
                private ContainerInterface $locator,
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\HandlerCollection:
                arguments: [!tagged_locator { tag: 'app.handler', index_by: 'key', default_index_method: 'myOwnMethodName' }]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <!-- ... -->

                <service id="App\HandlerCollection">
                    <argument type="tagged_locator" tag="app.handler" index-by="key" default-index-method="myOwnMethodName"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $containerConfigurator) {
            $containerConfigurator->services()
                ->set(App\HandlerCollection::class)
                    ->args([tagged_locator('app.handler', 'key', 'myOwnMethodName')])
            ;
        };

.. note::

    Since code should not be responsible for defining how the locators are
    going to be used, a configuration key (``key`` in the example above) must
    be set so the custom method may be called as a fallback.

Service Subscriber Trait
------------------------

The :class:`Symfony\\Contracts\\Service\\ServiceSubscriberTrait` provides an
implementation for :class:`Symfony\\Contracts\\Service\\ServiceSubscriberInterface`
that looks through all methods in your class that are marked with the
:class:`Symfony\\Contracts\\Service\\Attribute\\SubscribedService` attribute. It
describes the services needed by the class based on each method's return type.
The service id is ``__METHOD__``. This allows you to add dependencies to your
services based on type-hinted helper methods::

    // src/Service/MyService.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;
    use Symfony\Contracts\Service\ServiceSubscriberTrait;

    class MyService implements ServiceSubscriberInterface
    {
        use ServiceSubscriberTrait;

        public function doSomething()
        {
            // $this->router() ...
            // $this->logger() ...
        }

        #[SubscribedService]
        private function router(): RouterInterface
        {
            return $this->container->get(__METHOD__);
        }

        #[SubscribedService]
        private function logger(): LoggerInterface
        {
            return $this->container->get(__METHOD__);
        }
    }

This  allows you to create helper traits like RouterAware, LoggerAware, etc...
and compose your services with them::

    // src/Service/LoggerAware.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;

    trait LoggerAware
    {
        #[SubscribedService]
        private function logger(): LoggerInterface
        {
            return $this->container->get(__CLASS__.'::'.__FUNCTION__);
        }
    }

    // src/Service/RouterAware.php
    namespace App\Service;

    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;

    trait RouterAware
    {
        #[SubscribedService]
        private function router(): RouterInterface
        {
            return $this->container->get(__CLASS__.'::'.__FUNCTION__);
        }
    }

    // src/Service/MyService.php
    namespace App\Service;

    use Symfony\Contracts\Service\ServiceSubscriberInterface;
    use Symfony\Contracts\Service\ServiceSubscriberTrait;

    class MyService implements ServiceSubscriberInterface
    {
        use ServiceSubscriberTrait, LoggerAware, RouterAware;

        public function doSomething()
        {
            // $this->router() ...
            // $this->logger() ...
        }
    }

.. caution::

    When creating these helper traits, the service id cannot be ``__METHOD__``
    as this will include the trait name, not the class name. Instead, use
    ``__CLASS__.'::'.__FUNCTION__`` as the service id.

``SubscribedService`` Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 6.2

    The ability to add attributes was introduced in Symfony 6.2.

You can use the ``attributes`` argument of ``SubscribedService`` to add any
of the following dependency injection attributes:

* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Autowire`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\TaggedIterator`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\TaggedLocator`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Target`
* :class:`Symfony\\Component\\DependencyInjection\\Attribute\\MapDecorated`

Here's an example::

    // src/Service/MyService.php
    namespace App\Service;

    use Psr\Log\LoggerInterface;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\DependencyInjection\Attribute\Target;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Service\Attribute\SubscribedService;
    use Symfony\Contracts\Service\ServiceSubscriberInterface;
    use Symfony\Contracts\Service\ServiceSubscriberTrait;

    class MyService implements ServiceSubscriberInterface
    {
        use ServiceSubscriberTrait;

        public function doSomething()
        {
            // $this->environment() ...
            // $this->router() ...
            // $this->logger() ...
        }

        #[SubscribedService(attributes: new Autowire('%kernel.environment%'))]
        private function environment(): string
        {
            return $this->container->get(__METHOD__);
        }

        #[SubscribedService(attributes: new Autowire(service: 'router'))]
        private function router(): RouterInterface
        {
            return $this->container->get(__METHOD__);
        }

        #[SubscribedService(attributes: new Target('requestLogger'))]
        private function logger(): LoggerInterface
        {
            return $this->container->get(__METHOD__);
        }
    }

.. note::

    The above example requires using ``3.2`` version or newer of ``symfony/service-contracts``.

Testing a Service Subscriber
----------------------------

To unit test a service subscriber, you can create a fake container::

    use Symfony\Contracts\Service\ServiceLocatorTrait;
    use Symfony\Contracts\Service\ServiceProviderInterface;

    // Create the fake services
    $foo = new stdClass();
    $bar = new stdClass();
    $bar->foo = $foo;

    // Create the fake container
    $container = new class([
        'foo' => fn () => $foo,
        'bar' => fn () => $bar,
    ]) implements ServiceProviderInterface {
        use ServiceLocatorTrait;
    };

    // Create the service subscriber
    $serviceSubscriber = new MyService($container);
    // ...

Another alternative is to mock it using ``PHPUnit``::

    use Psr\Container\ContainerInterface;

    $container = $this->createMock(ContainerInterface::class);
    $container->expects(self::any())
        ->method('get')
        ->willReturnMap([
            ['foo', $this->createStub(Foo::class)],
            ['bar', $this->createStub(Bar::class)],
        ])
    ;

    $serviceSubscriber = new MyService($container);
    // ...

.. _`Command pattern`: https://en.wikipedia.org/wiki/Command_pattern
