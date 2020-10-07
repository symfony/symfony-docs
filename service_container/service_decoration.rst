.. index::
    single: Service Container; Decoration

How to Decorate Services
========================

When overriding an existing definition, the original service is lost:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            # this replaces the old App\Mailer definition with the new one, the
            # old definition is lost
            App\Mailer:
                class: App\NewMailer

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer"/>

                <!-- this replaces the old App\Mailer definition with the new
                     one, the old definition is lost -->
                <service id="App\Mailer" class="App\NewMailer"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mailer;
        use App\NewMailer;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Mailer::class);

            // this replaces the old App\Mailer definition with the new one, the
            // old definition is lost
            $services->set(Mailer::class, NewMailer::class);
        };

Most of the time, that's exactly what you want to do. But sometimes,
you might want to decorate the old one instead (i.e. apply the `Decorator pattern`_).
In this case, the old service should be kept around to be able to reference
it in the new one. This configuration replaces ``App\Mailer`` with a new one,
but keeps a reference of the old one as ``.inner``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\DecoratingMailer:
                # overrides the App\Mailer service
                # but that service is still available as ".inner"
                decorates: App\Mailer

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer"/>

                <!-- overrides the App\Mailer service
                     but that service is still available as ".inner" -->
                <service id="App\DecoratingMailer"
                    decorates="App\Mailer"
                />

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\DecoratingMailer;
        use App\Mailer;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Mailer::class);

            $services->set(DecoratingMailer::class)
                // overrides the App\Mailer service
                // but that service is still available as ".inner"
                ->decorate(Mailer::class);
        };

The ``decorates`` option tells the container that the ``App\DecoratingMailer``
service replaces the ``App\Mailer`` service. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
the decorated service is automatically injected when the constructor of the
decorating service has one argument type-hinted with the decorated service class.

If you are not using autowiring or the decorating service has more than one
constructor argument type-hinted with the decorated service class, you must
inject the decorated service explicitly (the ID of the decorated service is
automatically changed to ``'.inner'``):

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\DecoratingMailer:
                decorates: App\Mailer
                # pass the old service as an argument
                arguments: ['@.inner']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer"/>

                <service id="App\DecoratingMailer"
                    decorates="App\Mailer"
                >
                    <!-- pass the old service as an argument -->
                    <argument type="service" id=".inner"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\DecoratingMailer;
        use App\Mailer;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Mailer::class);

            $services->set(DecoratingMailer::class)
                ->decorate(Mailer::class)
                // pass the old service as an argument
                // In versions earlier to Symfony 5.1 the service() function was called ref()
                ->args([service('.inner')]);
        };

.. versionadded:: 5.1

    The special ``.inner`` value was introduced in Symfony 5.1. In previous
    versions you needed to use: ``decorating_service_id + '.inner'``.

.. tip::

    The visibility of the decorated ``App\Mailer`` service (which is an alias
    for the new service) will still be the same as the original ``App\Mailer``
    visibility.

.. note::

    The generated inner id is based on the id of the decorator service
    (``App\DecoratingMailer`` here), not of the decorated service (``App\Mailer``
    here). You can control the inner service name via the ``decoration_inner_name``
    option:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                App\DecoratingMailer:
                    # ...
                    decoration_inner_name: App\DecoratingMailer.wooz
                    arguments: ['@App\DecoratingMailer.wooz']

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
                xsd:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- ... -->

                    <service
                        id="App\DecoratingMailer"
                        decorates="App\Mailer"
                        decoration-inner-name="App\DecoratingMailer.wooz"
                        public="false"
                    >
                        <argument type="service" id="App\DecoratingMailer.wooz"/>
                    </service>

                </services>
            </container>

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            use App\DecoratingMailer;
            use App\Mailer;

            return function(ContainerConfigurator $configurator) {
                $services = $configurator->services();

                $services->set(Mailer::class);

                $services->set(DecoratingMailer::class)
                    ->decorate(Mailer::class, DecoratingMailer::class.'.wooz')
                    ->args([service(DecoratingMailer::class.'.wooz')]);
            };

Decoration Priority
-------------------

When applying multiple decorators to a service, you can control their order with
the ``decoration_priority`` option. Its value is an integer that defaults to
``0`` and higher priorities mean that decorators will be applied earlier.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        Foo: ~

        Bar:
            decorates: Foo
            decoration_priority: 5
            arguments: ['@.inner']

        Baz:
            decorates: Foo
            decoration_priority: 1
            arguments: ['@.inner']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Foo"/>

                <service id="Bar" decorates="Foo" decoration-priority="5">
                    <argument type="service" id=".inner"/>
                </service>

                <service id="Baz" decorates="Foo" decoration-priority="1">
                    <argument type="service" id=".inner"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Foo::class);

            $services->set(Bar::class)
                ->decorate(Foo::class, null, 5)
                ->args([service('.inner')]);

            $services->set(Baz::class)
                ->decorate(Foo::class, null, 1)
                ->args([service('.inner')]);
        };


The generated code will be the following::

    $this->services[Foo::class] = new Baz(new Bar(new Foo()));

Control the Behavior When the Decorated Service Does Not Exist
--------------------------------------------------------------

When you decorate a service that doesn't exist, the ``decoration_on_invalid``
option allows you to choose the behavior to adopt.

Three different behaviors are available:

* ``exception``: A ``ServiceNotFoundException`` will be thrown telling that decorator's dependency is missing. (default)
* ``ignore``: The container will remove the decorator.
* ``null``: The container will keep the decorator service and will set the decorated one to ``null``.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        Foo: ~

        Bar:
            decorates: Foo
            decoration_on_invalid: ignore
            arguments: ['@.inner']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Foo"/>

                <service id="Bar" decorates="Foo" decoration-on-invalid="ignore">
                    <argument type="service" id=".inner"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\DependencyInjection\ContainerInterface;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Foo::class);

            $services->set(Bar::class)
                ->decorate(Foo::class, null, 0, ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
                ->args([service('.inner')])
            ;
        };

.. caution::

    When using ``null``, you may have to update the decorator constructor in
    order to make decorated dependency nullable::

        // src/Service/DecoratorService.php
        namespace App\Service;

        use Acme\OptionalBundle\Service\OptionalService;

        class DecoratorService
        {
            private $decorated;

            public function __construct(?OptionalService $decorated)
            {
                $this->decorated = $decorated;
            }

            public function tellInterestingStuff(): string
            {
                if (!$this->decorated) {
                    return 'Just one interesting thing';
                }

                return $this->decorated->tellInterestingStuff().' + one more interesting thing';
            }
        }

.. note::

    Sometimes, you may want to add a compiler pass that creates service
    definitions on the fly. If you want to decorate such a service,
    be sure that your compiler pass is registered with ``PassConfig::TYPE_BEFORE_OPTIMIZATION``
    type so that the decoration pass will be able to find the created services.

.. _`Decorator pattern`: https://en.wikipedia.org/wiki/Decorator_pattern
