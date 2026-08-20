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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\SomeNonSharedService;

        return App::config([
            'services' => [
                SomeNonSharedService::class => [
                    'shared' => false,
                ],
            ],
        ]);

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
                message: The "%%service_id%%" service is deprecated since vendor-name/package-name 2.8 and will be removed in 3.0.

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\OldService;

        return App::config([
            'services' => [
                OldService::class => [
                    'deprecated' => [
                        'package' => 'vendor-name/package-name',
                        'version' => '2.8',
                        'message' => 'The "%%service_id%%" service is deprecated since vendor-name/package-name 2.8 and will be removed in 3.0.',
                    ],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\PhpMailer;

        return App::config([
            'services' => [
                'app.mailer' => [
                    'alias' => PhpMailer::class,

                    // this outputs the following generic deprecation message:
                    // Since acme/package 1.2: The "app.mailer" service alias is deprecated. You should stop using it, as it will be removed in the future
                    'deprecated' => [
                        'package' => 'acme/package',
                        'version' => '1.2',
                    ],
                ],
            ],
        ]);

When deprecating aliases, you can also define a custom message and the
``%alias_id%`` placeholder will be replaced by the alias id. You **must** have
at least one occurrence of the ``%alias_id%`` placeholder in your template:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        app.mailer:
            alias: 'App\Mail\PhpMailer'

            deprecated:
                package: 'acme/package'
                version: '1.2'
                message: 'The "%%alias_id%%" alias is deprecated. Do not use it anymore.'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\PhpMailer;

        return App::config([
            'services' => [
                'app.mailer' => [
                    'alias' => PhpMailer::class,

                    'deprecated' => [
                        'package' => 'acme/package',
                        'version' => '1.2',
                        'message' => 'The "%%alias_id%%" alias is deprecated. Do not use it anymore.',
                    ],
                ],
            ],
        ]);

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

    return function(ContainerConfigurator $containerConfigurator) {
        $services = $containerConfigurator->services();

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\MyService;

        return App::config([
            'services' => [
                MyService::class => [
                    'arguments' => [
                        '$rootNamespace' => abstract_arg('should be defined by Pass'),
                    ],
                ],
            ],
        ]);

If you don't replace the value of an abstract argument during runtime, a
``RuntimeException`` will be thrown with a message like
``Argument "$rootNamespace" of service "App\Service\MyService" is abstract: should be defined by Pass.``

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\AnonymousBar;
        use App\Foo;

        return App::config([
            'services' => [
                Foo::class => [
                    'arguments' => [
                        inline_service(AnonymousBar::class),
                    ],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Foo;
        use App\FooFactory;

        return App::config([
            'services' => [
                Foo::class => [
                    'factory' => [inline_service(FooFactory::class), 'constructFoo'],
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\EmailConfigurator;
        use App\Mail\GreetingCardSender;
        use App\Mail\NewsletterSender;

        return App::config([
            'services' => [
                // registers all classes as services, including App\Mail\EmailConfigurator
                'App\\' => [
                    'resource' => '../src/',
                ],
                // override the services to set the configurator
                NewsletterSender::class => [
                    'configurator' => [service(EmailConfigurator::class), 'configure'],
                ],
                GreetingCardSender::class => [
                    'configurator' => [service(EmailConfigurator::class), 'configure'],
                ],
            ],
        ]);

When requesting the ``App\Mail\NewsletterSender`` or
``App\Mail\GreetingCardSender`` service, the created instance is first passed to
the ``EmailConfigurator::configure()`` method.

.. _configurators-invokable:

.. tip::

    If the configurator class defines an ``__invoke()`` method, you can omit
    the method name and pass only the service:
    ``configurator: '@App\Mail\EmailConfigurator'`` in YAML and
    ``'configurator' => service(EmailConfigurator::class)`` in PHP.

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return App::config([
            'services' => [
                'app.synthetic_service' => [
                    // synthetic services don't specify a class
                    'synthetic' => true,
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Repository\BaseDoctrineRepository;
        use App\Repository\DoctrinePostRepository;
        use App\Repository\DoctrineUserRepository;

        return App::config([
            'services' => [
                BaseDoctrineRepository::class => [
                    'abstract' => true,
                    'arguments' => [service('doctrine.orm.entity_manager')],
                    'calls' => [
                        'setLogger' => [service('logger')],
                    ],
                ],
                DoctrineUserRepository::class => [
                    // extend the App\Repository\BaseDoctrineRepository service
                    'parent' => BaseDoctrineRepository::class,
                ],
                DoctrinePostRepository::class => [
                    'parent' => BaseDoctrineRepository::class,
                ],
            ],
        ]);

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

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Repository\BaseDoctrineRepository;
        use App\Repository\DoctrinePostRepository;
        use App\Repository\DoctrineUserRepository;

        return App::config([
            'services' => [
                // ...

                DoctrineUserRepository::class => [
                    'parent' => BaseDoctrineRepository::class,
                    // overrides the private setting of the parent service
                    'public' => true,
                    // appends the 'app.username_checker' service to the parent
                    // argument list
                    'arguments' => [service('app.username_checker')],
                ],
                DoctrinePostRepository::class => [
                    'parent' => BaseDoctrineRepository::class,
                    // overrides the first argument (using the special index_N key)
                    'arguments' => [
                        'index_0' => service('doctrine.custom_entity_manager'),
                    ],
                ],
            ],
        ]);
