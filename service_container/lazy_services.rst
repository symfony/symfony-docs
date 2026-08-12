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

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension" lazy="true"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(AppExtension::class)->lazy();
        };

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

.. versionadded:: 7.1

    The ``#[Lazy]`` attribute was introduced in Symfony 7.1.

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

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension" lazy="Twig\Extension\ExtensionInterface"/>

                <!-- alternatively, use the complete definition:
                <service id="App\Twig\AppExtension" lazy="true">
                    <tag name="proxy" interface="Twig\Extension\ExtensionInterface"/>
                </service>
                -->
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;
        use Twig\Extension\ExtensionInterface;

        return function(ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(AppExtension::class)
                ->lazy(ExtensionInterface::class)
            ;

            // alternatively, use the complete definition:
            // $services->set(AppExtension::class)
            //     ->lazy()
            //     ->tag('proxy', ['interface' => ExtensionInterface::class])
            // ;
        };

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
