Advanced Service Definition Options
===================================

Most applications only need the service definition options explained in the
:doc:`main service container article </service_container>`. This article
explains the less common options, used for advanced use cases.

.. _services-shared:

Non-Shared Services: Getting a New Instance Every Time
------------------------------------------------------

In the service container, all services are shared by default. This means that
each time you retrieve the service, you'll get the *same* instance. This is
usually the behavior you want, but in some cases, you might want to always get a
*new* instance.

The typical scenario is a *stateful* service: a service that stores data related
to one specific task in its own properties (e.g. a service that builds a report
and accumulates partial results while building it). If that service was shared,
all the code using it would read and modify the same data, mixing unrelated tasks.

In order to always get a new instance, set the ``shared`` setting to ``false``
in your service definition:

.. configuration-block::

    .. code-block:: php-attributes

        // src/SomeNonSharedService.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

        #[Autoconfigure(shared: false)]
        class SomeNonSharedService
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\SomeNonSharedService:
                shared: false
                # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <services>
            <service id="App\SomeNonSharedService" shared="false"/>
        </services>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\SomeNonSharedService;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(SomeNonSharedService::class)
                ->share(false);
        };

Now, whenever you request the ``App\SomeNonSharedService`` from the container,
you will be passed a new instance.

Before making a service non-shared, consider its drawbacks:

* Creating a new instance every time has a **performance** cost, and that cost
  grows with the number of dependencies of the service, because they must be
  resolved and injected on every instantiation;
* The new instance is created **each time you ask the container** for the
  service, not each time you use it. If some service receives a non-shared
  service as a constructor argument, it gets one instance and keeps reusing
  it during its entire lifetime. To really get a new instance on each call,
  inject a :ref:`service closure <autowiring_closures>` instead.

.. _deprecating-service-definitions:

Deprecating Services and Aliases
--------------------------------

Once you have decided to deprecate the use of a service (because it is outdated
or you decided not to maintain it anymore), you can deprecate its definition:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        App\Service\OldService:
            deprecated:
                package: 'vendor-name/package-name'
                version: '2.8'
                message: The "%service_id%" service is deprecated since vendor-name/package-name 2.8 and will be removed in 3.0.

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\OldService">
                    <deprecated package="vendor-name/package-name" version="2.8">The "%service_id%" service is deprecated since vendor-name/package-name 2.8 and will be removed in 3.0.</deprecated>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\OldService;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(OldService::class)
                ->deprecate(
                    'vendor-name/package-name',
                    '2.8',
                    'The "%service_id%" service is deprecated since vendor-name/package-name 2.8 and will be removed in 3.0.'
                );
        };

Now, every time this service is used, a deprecation warning is triggered,
advising you to stop or to change your uses of that service.

The message is actually a message template, which replaces occurrences of the
``%service_id%`` placeholder by the service's id. You **must** have at least one
occurrence of the ``%service_id%`` placeholder in your template.

.. note::

    The deprecation message is optional. If not set, Symfony will show this default
    message: ``The "%service_id%" service is deprecated. You should stop using it,
    as it will soon be removed.``.

.. tip::

    It is strongly recommended that you define a custom message because the
    default one is too generic. A good message informs when this service was
    deprecated, until when it will be maintained and the alternative services
    to use (if any).

For :doc:`service decorators </service_container/decoration>`, if the
definition does not modify the deprecated status, it will inherit the status from
the definition that is decorated.

Service :ref:`aliases <services-alias>` can be deprecated in the same way:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        app.mailer:
            alias: 'App\Mail\PhpMailer'

            # this outputs the following generic deprecation message:
            # Since acme/package 1.2: The "app.mailer" service alias is deprecated. You should stop using it, as it will be removed in the future
            deprecated:
                package: 'acme/package'
                version: '1.2'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer" alias="App\Mail\PhpMailer">
                    <!-- this outputs the following generic deprecation message:
                         Since acme/package 1.2: The "app.mailer" service alias is deprecated. You should stop using it, as it will be removed in the future -->
                    <deprecated package="acme/package" version="1.2"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            // this outputs the following generic deprecation message:
            // Since acme/package 1.2: The "app.mailer" service alias is deprecated. You should stop using it, as it will be removed in the future
            $services->alias('app.mailer', 'App\Mail\PhpMailer')
                ->deprecate('acme/package', '1.2', '');
        };

When deprecating aliases, you can also define a custom message and the
``%alias_id%`` placeholder will be replaced by the alias id. You **must** have
at least one occurrence of the ``%alias_id%`` placeholder in your template.

Removing Service Definitions
----------------------------

A service can be removed from the service container if needed. This is useful
for example to make a service unavailable in some
:ref:`configuration environment <configuration-environments>` (e.g. in the
``test`` environment). Removing definitions is only possible when using the
PHP configuration format::

    // config/services_test.php
    namespace Symfony\Component\DependencyInjection\Loader\Configurator;

    use App\RemovedService;

    return function(ContainerConfigurator $container) {
        $services = $container->services();

        $services->remove(RemovedService::class);
    };

Now, the container will not contain the ``App\RemovedService`` in the ``test``
environment.

Abstract Service Arguments
--------------------------

Sometimes, the values of some service arguments can't be defined in the
configuration files because they are calculated at runtime using a
:doc:`compiler pass </service_container/compiler_passes>`
or :doc:`bundle extension </bundles/extension>`.

In those cases, you can use the ``abstract`` argument type to define at least
the name of the argument and some short description about its purpose:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Service\MyService:
                arguments:
                    $rootNamespace: !abstract 'should be defined by Pass'

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\MyService">
                    <argument key="$rootNamespace" type="abstract">should be defined by Pass</argument>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MyService;

        return function(ContainerConfigurator $container) {
            $services = $container->services();

            $services->set(MyService::class)
                ->arg('$rootNamespace', abstract_arg('should be defined by Pass'))
            ;

            // ...
        };

.. _anonymous-services:

Anonymous Services
------------------

In some cases, you may want to prevent a service being used as a dependency of
other services. This can be achieved by creating an anonymous service. These
services are like regular services but they don't define an ID and they are
created where they are used.

The following example shows how to inject an anonymous service into another service:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Foo:
                arguments:
                    - !service
                        class: App\AnonymousBar

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="foo" class="App\Foo">
                    <argument type="service">
                        <service class="App\AnonymousBar"/>
                    </argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\AnonymousBar;
        use App\Foo;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(Foo::class)
                ->args([inline_service(AnonymousBar::class)]);
        };

.. note::

    Anonymous services do NOT inherit the definitions provided from the
    defaults defined in the configuration. So you'll need to explicitly mark
    service as :ref:`autowired <services-autowire>` or
    :ref:`autoconfigured <services-autoconfigure>` when doing an anonymous
    service e.g.: ``inline_service(Foo::class)->autowire()->autoconfigure()``.

Anonymous services can also be used as :doc:`factories </service_container/factories>`:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Foo:
                factory: [ !service { class: App\FooFactory }, 'constructFoo' ]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="foo" class="App\Foo">
                    <factory method="constructFoo">
                        <service class="App\FooFactory"/>
                    </factory>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Foo;
        use App\FooFactory;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(Foo::class)
                ->factory([inline_service(FooFactory::class), 'constructFoo']);
        };

.. tip::

    In PHP classes, you can declare and inject anonymous services using the
    :ref:`#[AutowireInline] attribute <autowiring-anonymous-services-inline>`.

.. _service-configurators:

Service Configurators
---------------------

A *service configurator* is a callable that the container runs on a service right
after creating it. Use a configurator when a service needs a complex setup based
on settings coming from different sources, or when several services must be
configured in the same way at runtime. Unlike a
:doc:`factory </service_container/factories>`, a configurator doesn't create the
object; it only prepares an object that was already created.

For example, suppose your application sends different types of emails and that
each email is processed by formatters that can be enabled or disabled depending
on dynamic application settings. The ``App\Mail\NewsletterSender`` and
``App\Mail\GreetingCardSender`` classes both implement an
``EmailFormatterAwareInterface`` with a ``setEnabledFormatters()`` method, and a
separate ``App\Mail\EmailFormatterRegistry`` service knows which formatters are
enabled.

To avoid coupling both sender classes to ``EmailFormatterRegistry``, create a
configurator class::

    // src/Mail/EmailConfigurator.php
    namespace App\Mail;

    class EmailConfigurator
    {
        public function __construct(
            private EmailFormatterRegistry $formatterRegistry,
        ) {
        }

        public function configure(EmailFormatterAwareInterface $emailSender): void
        {
            $emailSender->setEnabledFormatters(
                $this->formatterRegistry->getEnabledFormatters()
            );
        }

        // ...
    }

The ``EmailConfigurator``'s job is to inject the enabled formatters into the
sender classes, because they are not aware of where those formatters come from.
On the other hand, ``EmailFormatterRegistry`` holds the knowledge about the
enabled formatters and how to load them, keeping the single responsibility
principle.

.. tip::

    While this example uses a PHP class method, configurators can be any valid
    PHP callable, including functions, static methods and methods of services.

Define the configurator of a service with the ``configurator`` option. If you're
using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
all these classes are already loaded as services, so you only need to set the
``configurator`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            # registers all classes as services, including App\Mail\EmailConfigurator
            App\:
                resource: '../src/'
                # ...

            # override the services to set the configurator
            App\Mail\NewsletterSender:
                configurator: ['@App\Mail\EmailConfigurator', 'configure']

            App\Mail\GreetingCardSender:
                configurator: ['@App\Mail\EmailConfigurator', 'configure']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <prototype namespace="App\" resource="../src/"/>

                <service id="App\Mail\NewsletterSender">
                    <configurator service="App\Mail\EmailConfigurator" method="configure"/>
                </service>

                <service id="App\Mail\GreetingCardSender">
                    <configurator service="App\Mail\EmailConfigurator" method="configure"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\EmailConfigurator;
        use App\Mail\GreetingCardSender;
        use App\Mail\NewsletterSender;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            // registers all classes as services, including App\Mail\EmailConfigurator
            $services->load('App\\', '../src/');

            // override the services to set the configurator
            $services->set(NewsletterSender::class)
                ->configurator([service(EmailConfigurator::class), 'configure']);

            $services->set(GreetingCardSender::class)
                ->configurator([service(EmailConfigurator::class), 'configure']);
        };

When requesting the ``App\Mail\NewsletterSender`` or
``App\Mail\GreetingCardSender`` service, the created instance is first passed to
the ``EmailConfigurator::configure()`` method.

.. _configurators-invokable:

.. tip::

    If the configurator class defines an ``__invoke()`` method, you can omit
    the method name and pass only the service:
    ``configurator: '@App\Mail\EmailConfigurator'`` in YAML,
    ``<configurator service="App\Mail\EmailConfigurator"/>`` in XML and
    ``->configurator(service(EmailConfigurator::class))`` in PHP.

.. _services-synthetic:

Synthetic Services: Injecting Instances into the Container
----------------------------------------------------------

In some applications, you may need to inject a class instance as service,
instead of configuring the container to create a new instance.

For instance, the ``kernel`` service in Symfony is injected into the container
from within the ``Kernel`` class::

    // ...
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\HttpKernel\TerminableInterface;

    abstract class Kernel implements KernelInterface, TerminableInterface
    {
        // ...

        protected function initializeContainer(): void
        {
            // ...
            $this->container->set('kernel', $this);

            // ...
        }
    }

Services that are set at runtime are called *synthetic services*. This service
has to be configured so the container knows the service exists during compilation
(otherwise, services depending on ``kernel`` will get a "service does not exist" error).

In order to do so, mark the service as synthetic in your service definition
configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # synthetic services don't specify a class
            app.synthetic_service:
                synthetic: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>

                <!-- synthetic services don't specify a class -->
                <service id="app.synthetic_service" synthetic="true"/>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            // synthetic services don't specify a class
            $services->set('app.synthetic_service')
                ->synthetic();
        };

Now, you can inject the instance in the container using
:method:`Container::set() <Symfony\\Component\\DependencyInjection\\Container::set>`::

    // instantiate the synthetic service
    $theService = ...;
    $container->set('app.synthetic_service', $theService);

.. _parent-services:

Parent Services: Managing Common Dependencies
---------------------------------------------

As you add more functionality to your application, you may well start to
have related classes that share some of the same dependencies. For example,
you may have multiple repository classes which need the
``doctrine.orm.entity_manager`` service and an optional ``logger`` service::

    // src/Repository/BaseDoctrineRepository.php
    namespace App\Repository;

    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Log\LoggerInterface;

    // ...
    abstract class BaseDoctrineRepository
    {
        protected LoggerInterface $logger;

        public function __construct(
            protected EntityManagerInterface $entityManager,
        ) {
        }

        public function setLogger(LoggerInterface $logger): void
        {
            $this->logger = $logger;
        }

        // ...
    }

Your child service classes may look like this::

    // src/Repository/DoctrineUserRepository.php
    namespace App\Repository;

    use App\Repository\BaseDoctrineRepository;

    // ...
    class DoctrineUserRepository extends BaseDoctrineRepository
    {
        // ...
    }

    // src/Repository/DoctrinePostRepository.php
    namespace App\Repository;

    use App\Repository\BaseDoctrineRepository;

    // ...
    class DoctrinePostRepository extends BaseDoctrineRepository
    {
        // ...
    }

.. note::

    In most modern applications you don't need this feature: dependencies
    shared by several classes are handled by :ref:`autowiring <services-autowire>`,
    the ``_defaults`` and ``_instanceof`` options and the ``#[Autoconfigure]``
    attribute. Consider those options first and use parent services only when
    they don't fit your needs (e.g. when not using autowiring).

The service container allows you to extend parent services in order to
avoid duplicated service definitions:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Repository\BaseDoctrineRepository:
                abstract:  true
                arguments: ['@doctrine.orm.entity_manager']
                calls:
                    - setLogger: ['@logger']

            App\Repository\DoctrineUserRepository:
                # extend the App\Repository\BaseDoctrineRepository service
                parent: App\Repository\BaseDoctrineRepository

            App\Repository\DoctrinePostRepository:
                parent: App\Repository\BaseDoctrineRepository

            # ...

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Repository\BaseDoctrineRepository" abstract="true">
                    <argument type="service" id="doctrine.orm.entity_manager"/>

                    <call method="setLogger">
                        <argument type="service" id="logger"/>
                    </call>
                </service>

                <!-- extends the App\Repository\BaseDoctrineRepository service -->
                <service id="App\Repository\DoctrineUserRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                />

                <service id="App\Repository\DoctrinePostRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                />

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Repository\BaseDoctrineRepository;
        use App\Repository\DoctrinePostRepository;
        use App\Repository\DoctrineUserRepository;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(BaseDoctrineRepository::class)
                ->abstract()
                ->args([service('doctrine.orm.entity_manager')])
                ->call('setLogger', [service('logger')])
            ;

            $services->set(DoctrineUserRepository::class)
                // extend the App\Repository\BaseDoctrineRepository service
                ->parent(BaseDoctrineRepository::class)
            ;

            $services->set(DoctrinePostRepository::class)
                ->parent(BaseDoctrineRepository::class)
            ;
        };

In this context, having a ``parent`` service implies that the arguments
and method calls of the parent service should be used for the child services.
Specifically, the entity manager will be injected and ``setLogger()`` will
be called when ``App\Repository\DoctrineUserRepository`` is instantiated.

All attributes on the parent service are shared with the child **except** for
``shared``, ``abstract`` and ``tags``. These are *not* inherited from the parent.

.. tip::

    In the examples shown, the classes sharing the same configuration also
    extend from the same parent class in PHP. This isn't necessary at all.
    You can also extract common parts of similar service definitions into
    a parent service without also extending a parent class in PHP.

Overriding Parent Dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

There may be times where you want to override what service is injected for
one child service only. You can override most settings by specifying it in
the child class:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Repository\DoctrineUserRepository:
                parent: App\Repository\BaseDoctrineRepository

                # overrides the private setting of the parent service
                public: true

                # appends the '@app.username_checker' argument to the parent
                # argument list
                arguments: ['@app.username_checker']

            App\Repository\DoctrinePostRepository:
                parent: App\Repository\BaseDoctrineRepository

                # overrides the first argument (using the special index_N key)
                arguments:
                    index_0: '@doctrine.custom_entity_manager'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <!-- overrides the private setting of the parent service -->
                <service id="App\Repository\DoctrineUserRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                    public="true"
                >
                    <!-- appends the '@app.username_checker' argument to the parent
                         argument list -->
                    <argument type="service" id="app.username_checker"/>
                </service>

                <service id="App\Repository\DoctrinePostRepository"
                    parent="App\Repository\BaseDoctrineRepository"
                >
                    <!-- overrides the first argument (using the index attribute) -->
                    <argument index="0" type="service" id="doctrine.custom_entity_manager"/>
                </service>

                <!-- ... -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Repository\BaseDoctrineRepository;
        use App\Repository\DoctrinePostRepository;
        use App\Repository\DoctrineUserRepository;
        // ...

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(BaseDoctrineRepository::class)
                // ...
            ;

            $services->set(DoctrineUserRepository::class)
                ->parent(BaseDoctrineRepository::class)

                // overrides the private setting of the parent service
                ->public()

                // appends the '@app.username_checker' argument to the parent
                // argument list
                ->args([service('app.username_checker')])
            ;

            $services->set(DoctrinePostRepository::class)
                ->parent(BaseDoctrineRepository::class)

                // overrides the first argument
                ->arg(0, service('doctrine.custom_entity_manager'))
            ;
        };
