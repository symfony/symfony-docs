.. index::
    single: DependencyInjection; Advanced configuration

How to Create Service Aliases and Mark Services as Private
==========================================================

.. _container-private-services:

Marking Services as Public / Private
------------------------------------

When defining a service, it can be made to be *public* or *private*. If a service
is *public*, it means that you can access it directly from the container at runtime.
For example, the ``doctrine`` service is a public service::

    // only public services can be accessed in this way
    $doctrine = $container->get('doctrine');

But typically, services are accessed using :ref:`dependency injection <services-constructor-injection>`.
And in this case, those services do *not* need to be public.

.. _inlined-private-services:

So unless you *specifically* need to access a service directly from the container
via ``$container->get()``, the best-practice is to make your services *private*.
In fact, All services  are :ref:`private <container-public>` by default.

You can also control the ``public`` option on a service-by-service basis:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Service\Foo:
                public: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Service\Foo" public="true"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\Foo;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Foo::class)
                ->public();
        };

.. _services-why-private:

Private services are special because they allow the container to optimize whether
and how they are instantiated. This increases the container's performance. It also
gives you better errors: if you try to reference a non-existent service, you will
get a clear error when you refresh *any* page, even if the problematic code would
not have run on that page.

Now that the service is private, you *must not* fetch the service directly
from the container::

    use App\Service\Foo;

    $container->get(Foo::class);

Thus, a service can be marked as private if you do not want to access it
directly from your code. However, if a service has been marked as private,
you can still alias it (see below) to access this service (via the alias).

.. _services-alias:

Aliasing
--------

You may sometimes want to use shortcuts to access some services. You can
do so by aliasing them and, furthermore, you can even alias non-public
services.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            App\Mail\PhpMailer:
                public: false

            app.mailer:
                alias: App\Mail\PhpMailer
                public: true

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mail\PhpMailer" public="false"/>

                <service id="app.mailer" alias="App\Mail\PhpMailer"/>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Mail\PhpMailer;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(PhpMailer::class)
                ->private();

            $services->alias('app.mailer', PhpMailer::class);
        };

This means that when using the container directly, you can access the
``PhpMailer`` service by asking for the ``app.mailer`` service like this::

    $container->get('app.mailer'); // Would return a PhpMailer instance

.. tip::

    In YAML, you can also use a shortcut to alias a service:

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...
            app.mailer: '@App\Mail\PhpMailer'

Deprecating Service Aliases
~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you decide to deprecate the use of a service alias (because it is outdated
or you decided not to maintain it anymore), you can deprecate its definition:

.. configuration-block::

    .. code-block:: yaml

        app.mailer:
            alias: 'App\Mail\PhpMailer'

            # this outputs the following generic deprecation message:
            # Since acme/package 1.2: The "app.mailer" service alias is deprecated. You should stop using it, as it will be removed in the future
            deprecated:
                package: 'acme/package'
                version: '1.2'

            # you can also define a custom deprecation message (%alias_id% placeholder is available)
            deprecated:
                package: 'acme/package'
                version: '1.2'
                message: 'The "%alias_id%" alias is deprecated. Do not use it anymore.'

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.mailer" alias="App\Mail\PhpMailer">
                    <!-- this outputs the following generic deprecation message:
                         Since acme/package 1.2: The "app.mailer" service alias is deprecated. You should stop using it, as it will be removed in the future -->
                    <deprecated package="acme/package" version="1.2"/>

                    <!-- you can also define a custom deprecation message (%alias_id% placeholder is available) -->
                    <deprecated package="acme/package" version="1.2">
                        The "%alias_id%" service alias is deprecated. Don't use it anymore.
                    </deprecated>
                </service>
            </services>
        </container>

    .. code-block:: php

        $container
            ->setAlias('app.mailer', 'App\Mail\PhpMailer')

            // this outputs the following generic deprecation message:
            // Since acme/package 1.2: The "app.mailer" service alias is deprecated. You should stop using it, as it will be removed in the future
            ->setDeprecated('acme/package', '1.2')

            // you can also define a custom deprecation message (%alias_id% placeholder is available)
            ->setDeprecated(
                'acme/package',
                '1.2',
                'The "%alias_id%" service alias is deprecated. Don\'t use it anymore.'
            )
        ;

Now, every time this service alias is used, a deprecation warning is triggered,
advising you to stop or to change your uses of that alias.

The message is actually a message template, which replaces occurrences of the
``%alias_id%`` placeholder by the service alias id. You **must** have at least
one occurrence of the ``%alias_id%`` placeholder in your template.

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

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Foo::class)
                ->args([inline_service(AnonymousBar::class)]);
        };

.. note::

    Anonymous services do *NOT* inherit the definitions provided from the
    defaults defined in the configuration. So you'll need to explicitly mark
    service as autowired or autoconfigured when doing an anonymous service
    e.g.: ``inline_service(Foo::class)->autowire()->autoconfigure()``.

Using an anonymous service as a factory looks like this:

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

        use App\AnonymousBar;
        use App\Foo;

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

            $services->set(Foo::class)
                ->factory([inline_service(AnonymousBar::class), 'constructFoo']);
        };

Deprecating Services
--------------------

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

        return function(ContainerConfigurator $configurator) {
            $services = $configurator->services();

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

For service decorators (see :doc:`/service_container/service_decoration`), if the
definition does not modify the deprecated status, it will inherit the status from
the definition that is decorated.
