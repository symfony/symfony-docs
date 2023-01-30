.. index::
   single: Dependency Injection; Lazy Services

Lazy Services
=============

.. seealso::

    Another way to inject services lazily is via a :doc:`service subscriber </service_container/service_subscribers_locators>`.

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

.. caution::

    Lazy services do not support `final`_ classes, but you can use
    `Interface Proxifying`_ to work around this limitation.

    In PHP versions prior to 8.0 lazy services do not support parameters with
    default values for built-in PHP classes (e.g. ``PDO``).

.. versionadded:: 6.2

    Starting from Symfony 6.2, service laziness is supported out of the box
    without having to install any additional package.

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

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(AppExtension::class)->lazy();
        };

Once you inject the service into another service, a lazy ghost object with the
same signature of the class representing the service should be injected. A lazy
`ghost object`_ is an object that is created empty and that is able to initialize
itself when being accessed for the first time). The same happens when calling
``Container::get()`` directly.

To check if your lazy service works you can check the interface of the received object::

    dump(class_implements($service));
    // the output should include "Symfony\Component\VarExporter\LazyGhostObjectInterface"

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

Interface Proxifying
--------------------

Under the hood, proxies generated to lazily load services inherit from the class
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

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Twig\AppExtension" lazy="Twig\Extension\ExtensionInterface"/>
                <!-- or a complete definition: -->
                <service id="App\Twig\AppExtension" lazy="true">
                    <tag name="proxy" interface="Twig\Extension\ExtensionInterface"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Twig\AppExtension;
        use Twig\Extension\ExtensionInterface;

        return function(ContainerConfigurator $containerConfigurator) {
            $services = $containerConfigurator->services();

            $services->set(AppExtension::class)
                ->lazy()
                ->tag('proxy', ['interface' => ExtensionInterface::class])
            ;
        };

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
specified interfaces and will not extend the original service class, allowing to
lazy load services using `final`_ classes. You can configure the proxy to
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
