Lazy Services
=============

.. seealso::

    Other ways to inject services lazily are via a :doc:`service closure </service_container/service_closures>` or
    :doc:`service subscriber </service_container/service_subscribers_locators>`.

Why Lazy Services?
------------------

In some cases, you may want to inject a service that is a bit heavy to instantiate,
but is not always used inside your object. For example, imagine you have
a ``NewsletterManager`` and you inject a ``mailer`` service into it. Only
a few methods on your ``NewsletterManager`` actually use the ``mailer``,
but even when you don't need it, a ``mailer`` service is always instantiated
in order to construct your ``NewsletterManager``.

Configuring lazy services is one answer to this. With a lazy service, a
"proxy" of the ``mailer`` service is actually injected. It looks and acts
like the ``mailer``, except that the ``mailer`` isn't actually instantiated
until you interact with the proxy in some way.

.. note::

    When using PHP 8.4 or later, lazy services rely on native lazy objects,
    so ``final`` and ``readonly`` classes are fully supported.

    On older PHP versions, lazy services do not support ``final`` or ``readonly``
    classes, but you can use :ref:`interface proxifying <lazy-services-interface-proxifying>`
    to work around this limitation.

.. _lazy-services_configuration:

Configuration
-------------

You can mark the service as ``lazy`` by manipulating its definition:

.. configuration-block::

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

Once you inject the service into another service, a lazy ghost object with the
same signature of the class representing the service should be injected. A lazy
`ghost object`_ is an object that is created empty and that is able to initialize
itself when being accessed for the first time). The same happens when calling
``Container::get()`` directly.

You can also configure your service's laziness thanks to the
:class:`Symfony\\Component\\DependencyInjection\\Attribute\\Autoconfigure` attribute.
For example, to define your service as lazy use the following::

    namespace App\Twig;

    use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
    use Twig\Extension\ExtensionInterface;

    #[Autoconfigure(lazy: true)]
    class AppExtension implements ExtensionInterface
    {
        // ...
    }

You can also configure laziness when your service is injected with the
:class:`Symfony\\Component\\DependencyInjection\\Attribute\\Autowire` attribute::

    namespace App\Service;

    use App\Twig\AppExtension;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;

    class MessageGenerator
    {
        public function __construct(
            #[Autowire(service: 'app.twig.app_extension', lazy: true)] ExtensionInterface $extension
        ) {
            // ...
        }
    }

This attribute also allows you to define the interfaces to proxy when using
laziness, and supports lazy-autowiring of union types::

    public function __construct(
        #[Autowire(service: 'foo', lazy: FooInterface::class)]
        FooInterface|BarInterface $foo,
    ) {
    }

Another possibility is to use the :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Lazy` attribute::

    namespace App\Twig;

    use Symfony\Component\DependencyInjection\Attribute\Lazy;
    use Twig\Extension\ExtensionInterface;

    #[Lazy]
    class AppExtension implements ExtensionInterface
    {
        // ...
    }

This attribute can be applied to both class and parameters that should be lazy-loaded.
It defines an optional parameter used to define interfaces for proxy and intersection types::

    public function __construct(
        #[Lazy(FooInterface::class)]
        FooInterface|BarInterface $foo,
    ) {
    }

.. _lazy-services-interface-proxifying:

Interface Proxifying
--------------------

.. note::

    If you are using PHP 8.4 or later, ``final`` and ``readonly`` classes
    are supported natively and the technique explained in this section is not
    required. It remains useful as a safe guard to restrict callable methods to
    those defined by the interface.

Internally, proxies generated to lazily load services inherit from the class
used by the service. However, sometimes this is not possible at all (e.g. because
the class is `final`_ and can not be extended) or not convenient.

To workaround this limitation, you can configure a proxy to only implement
specific interfaces.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Twig\AppExtension:
                lazy: 'Twig\Extension\ExtensionInterface'
                # or a complete definition:
                lazy: true
                tags:
                    - { name: 'proxy', interface: 'Twig\Extension\ExtensionInterface' }

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;
        use Twig\Extension\ExtensionInterface;

        return App::config([
            'services' => [
                AppExtension::class => [
                    'lazy' => ExtensionInterface::class,
                    'tags' => [
                        ['proxy' => ['interface' => ExtensionInterface::class]],
                    ],
                ],
            ],
        ]);

Just like in the :ref:`Configuration <lazy-services_configuration>` section, you can
use the :class:`Symfony\\Component\\DependencyInjection\\Attribute\\Autoconfigure`
attribute to configure the interface to proxify by passing its FQCN as the ``lazy``
parameter value::

    namespace App\Twig;

    use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
    use Twig\Extension\ExtensionInterface;

    #[Autoconfigure(lazy: ExtensionInterface::class)]
    class AppExtension implements ExtensionInterface
    {
        // ...
    }

The virtual `proxy`_ injected into other services will only implement the
specified interfaces and will not extend the original service class, allowing you
to lazy load services using `final`_ classes. You can configure the proxy to
implement multiple interfaces by adding new "proxy" tags.

.. tip::

    This feature can also act as a safe guard: given that the proxy does not
    extend the original class, only the methods defined by the interface can
    be called, preventing to call implementation specific methods. It also
    prevents injecting the dependency at all if you type-hinted a concrete
    implementation instead of the interface.

.. _`ghost object`: https://en.wikipedia.org/wiki/Lazy_loading#Ghost
.. _`final`: https://www.php.net/manual/en/language.oop5.final.php
.. _`proxy`: https://en.wikipedia.org/wiki/Proxy_pattern
