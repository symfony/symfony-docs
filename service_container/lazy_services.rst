Lazy Services
=============

In some cases, you may need to inject a service that is heavy to instantiate
but is not always used. For example, imagine a ``NewsletterSender`` service
that injects a ``mailer`` service. Only a few methods of the
``NewsletterSender`` actually use the ``mailer``, but the ``mailer`` service is
always instantiated in order to construct the ``NewsletterSender``.

**Lazy services** solve this problem. When a service is marked as lazy, the
container injects a *proxy* of the service instead of the real service. The
proxy looks and acts like the real service, except that the service is not
instantiated until you interact with the proxy in some way.

The service container provides other features to delay the instantiation of
services. Depending on your needs, one of them can be a better fit than lazy
services:

* :ref:`Service closures <autowiring_closures>` inject a closure that creates
  and returns the service when calling it. Prefer them when your own code must
  control explicitly when (and whether) the service is created;
* :doc:`Service locators </service_container/subscribers_locators>`
  inject a set of services, and each of them is only created when you fetch it
  from the locator. Prefer them when your class needs occasional access to
  several services instead of one.

.. note::

    When using PHP 8.4 or later, lazy services rely on native lazy objects,
    so ``final`` and ``readonly`` classes are fully supported.

    On older PHP versions, lazy services do not support ``final`` or ``readonly``
    classes, but you can :ref:`restrict the proxy to an interface <lazy-services-interface-proxifying>`
    to work around this limitation.

.. _lazy-services_configuration:

Making a Service Lazy
---------------------

Use the ``lazy`` option to mark a service as lazy:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Twig/AppExtension.php
        namespace App\Twig;

        use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
        use Twig\Extension\ExtensionInterface;

        #[Autoconfigure(lazy: true)]
        class AppExtension implements ExtensionInterface
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Twig\AppExtension:
                lazy: true

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;

        return App::config([
            'services' => [
                AppExtension::class => [
                    'lazy' => true,
                ],
            ],
        ]);

In PHP classes, you can also use the ``#[Lazy]`` attribute, which is a shorter
equivalent of ``#[Autoconfigure(lazy: true)]``::

    // src/Twig/AppExtension.php
    namespace App\Twig;

    use Symfony\Component\DependencyInjection\Attribute\Lazy;
    use Twig\Extension\ExtensionInterface;

    #[Lazy]
    class AppExtension implements ExtensionInterface
    {
        // ...
    }

From now on, when injecting this service into other services or when getting it
directly from the container, a lazy `ghost object`_ with the same signature as
the service class is injected instead. A lazy ghost object is an object created
empty which initializes itself (i.e. instantiates the real service) when you
access it for the first time.

Making Only Specific Injections Lazy
------------------------------------

Marking a service as lazy makes it lazy everywhere it's injected. If you prefer
to keep it as a regular service, you can make it lazy only for some specific
injections. To do so, add the ``#[Lazy]`` attribute to the argument where the
service is injected::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use Symfony\Component\DependencyInjection\Attribute\Lazy;
    use Twig\Extension\ExtensionInterface;

    class MessageGenerator
    {
        public function __construct(
            #[Lazy]
            ExtensionInterface $extension,
        ) {
            // ...
        }
    }

The ``lazy`` argument of the ``#[Autowire]`` attribute produces the same
result. Use that attribute instead when you also need some of its other
features, such as selecting the service to inject::

    // src/Service/MessageGenerator.php
    namespace App\Service;

    use App\Twig\AppExtension;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Twig\Extension\ExtensionInterface;

    class MessageGenerator
    {
        public function __construct(
            #[Autowire(service: AppExtension::class, lazy: true)]
            ExtensionInterface $extension,
        ) {
            // ...
        }
    }

If the argument uses a union or intersection type, pass the interface that the
proxy must implement as the value of the attribute::

    class MessageGenerator
    {
        public function __construct(
            #[Lazy(FooInterface::class)]
            FooInterface|BarInterface $foo,

            // when using the #[Autowire] attribute, pass the interface in the lazy argument:
            // #[Autowire(service: 'foo', lazy: FooInterface::class)]
            // FooInterface|BarInterface $foo,
        ) {
            // ...
        }
    }

.. _lazy-services-per-argument:

Injecting a Service as a Lazy Proxy
-----------------------------------

Marking a service ``lazy`` defers its instantiation for every consumer. When
only one injection point needs that, inject a lazy proxy on that argument
instead. The target service is left alone, so its other consumers still
receive the real instance:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Consumer.php
        namespace App;

        use Symfony\Component\DependencyInjection\Attribute\Lazy;

        class Consumer
        {
            public function __construct(
                #[Lazy]
                // this is equivalent: #[Autowire(lazy: true)]
                HeavyService $heavyService,
            ) {
            }
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Consumer:
                arguments:
                    # '@~' wraps the reference in a lazy proxy
                    - '@~App\HeavyService'

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Consumer;
        use App\HeavyService;

        return function (ContainerConfigurator $container): void {
            $container->services()
                ->set(Consumer::class)
                    ->args([lazy_proxy(HeavyService::class)]);
        };

The ``@~`` prefix composes with the ones controlling the behavior of invalid
references, so ``'@~?App\Maybe'`` injects a proxy of an optional service, and
resolves to ``null`` when that service does not exist. In PHP, ``lazy_proxy()``
returns a reference configurator, so ``ignoreOnInvalid()``, ``nullOnInvalid()``
and ``ignoreOnUninitialized()`` apply, as they do for ``service_closure()``.

To proxy specific interfaces rather than the target's own class, use the
``!lazy_proxy`` tag, which accepts a reference or a mapping:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Consumer:
                arguments:
                    - !lazy_proxy '@App\HeavyService'
                    - !lazy_proxy
                        service: '@App\HeavyService'
                        interface: 'App\HeavyInterface'
                    - !lazy_proxy
                        service: '@App\HeavyService'
                        interface: ['App\HeavyInterface', 'App\OtherInterface']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Consumer;
        use App\HeavyInterface;
        use App\HeavyService;
        use App\OtherInterface;

        return function (ContainerConfigurator $container): void {
            $container->services()
                ->set(Consumer::class)
                    ->args([
                        lazy_proxy(HeavyService::class),
                        lazy_proxy(HeavyService::class, HeavyInterface::class),
                        lazy_proxy(HeavyService::class, [
                            HeavyInterface::class,
                            OtherInterface::class,
                        ]),
                    ]);
        };

Naming one or more interfaces produces a proxy implementing only those, which is
what a consumer type-hinting the interface needs. Read more about this in
:ref:`Interface Proxifying <lazy-services-interface-proxifying>`.

.. versionadded:: 8.2

    The ``@~`` prefix, the ``!lazy_proxy`` tag and the ``lazy_proxy()`` function
    were introduced in Symfony 8.2.

.. _lazy-services-interface-proxifying:

Restricting the Proxy to an Interface
-------------------------------------

.. note::

    This technique is rarely needed nowadays. If you are using PHP 8.4 or later,
    ``final`` and ``readonly`` classes are supported natively, so the only
    remaining use case is to restrict the methods that can be called on the
    proxy.

Internally, the proxies generated to lazily load services inherit from the class
used by the service. However, sometimes this is not possible at all (e.g. because
the class is `final`_ and can not be extended) or not convenient.

To work around this limitation, you can configure the proxy to only implement
specific interfaces. In the following example, the ``AppExtension`` class
implements two interfaces, but the proxy generated for it only implements
``ExtensionInterface``:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Twig/AppExtension.php
        namespace App\Twig;

        use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
        use Twig\Extension\ExtensionInterface;
        use Twig\Extension\GlobalsInterface;

        #[Autoconfigure(lazy: ExtensionInterface::class)]
        class AppExtension implements ExtensionInterface, GlobalsInterface
        {
            // ...
        }

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Twig\AppExtension:
                lazy: 'Twig\Extension\ExtensionInterface'

                # alternatively, use the complete definition:
                # lazy: true
                # tags:
                #     - { name: 'proxy', interface: 'Twig\Extension\ExtensionInterface' }

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;
        use Twig\Extension\ExtensionInterface;

        return App::config([
            'services' => [
                AppExtension::class => [
                    'lazy' => ExtensionInterface::class,

                    // alternatively, use the complete definition:
                    // 'lazy' => true,
                    // 'tags' => [
                    //     ['proxy' => ['interface' => ExtensionInterface::class]],
                    // ],
                ],
            ],
        ]);

The virtual `proxy`_ injected into other services will only implement the
specified interfaces and will not extend the original service class, allowing you
to lazy load services using `final`_ classes. In the previous example, other
services can call the ``ExtensionInterface`` methods on the proxy, but not the
``GlobalsInterface`` methods or any other public method of the ``AppExtension``
class. You can configure the proxy to implement multiple interfaces by adding
new "proxy" tags.

.. tip::

    This feature can also act as a safe guard: given that the proxy does not
    extend the original class, only the methods defined by the interface can
    be called, which prevents calling implementation-specific methods. It also
    prevents injecting the dependency at all if you type-hinted a concrete
    implementation instead of the interface.

.. _`ghost object`: https://en.wikipedia.org/wiki/Lazy_loading#Ghost
.. _`final`: https://www.php.net/manual/en/language.oop5.final.php
.. _`proxy`: https://en.wikipedia.org/wiki/Proxy_pattern
