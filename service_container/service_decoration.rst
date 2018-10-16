.. index::
    single: Service Container; Decoration

How to Decorate Services
========================

When overriding an existing definition (e.g. when applying the `Decorator pattern`_),
the original service is lost:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            # this replaces the old App\Mailer definition with the new one, the
            # old definition is lost
            App\Mailer:
                class: App\DecoratingMailer

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer" />

                <!-- this replaces the old App\Mailer definition with the new
                     one, the old definition is lost -->
                <service id="App\Mailer" class="App\DecoratingMailer" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Mailer;
        use App\DecoratingMailer;

        $container->register(Mailer::class);

        // this replaces the old App\Mailer definition with the new one, the
        // old definition is lost
        $container->register(Mailer::class, DecoratingMailer::class);

Most of the time, that's exactly what you want to do. But sometimes,
you might want to decorate the old service instead and keep the old service so
that you can reference it:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\DecoratingMailer:
                # overrides the App\Mailer service
                # but that service is still available as App\DecoratingMailer.inner
                decorates: App\Mailer

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer" />

                <service id="App\DecoratingMailer"
                    decorates="App\Mailer"
                />

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\DecoratingMailer;
        use App\Mailer;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(Mailer::class);

        $container->register(DecoratingMailer::class)
            ->setDecoratedService(Mailer::class)
        ;

The ``decorates`` option tells the container that the ``App\DecoratingMailer``
service replaces the ``App\Mailer`` service. If you're using the
:ref:`default services.yaml configuration <service-container-services-load-example>`,
the decorated service is automatically injected when the constructor of the
decorating service has one argument type-hinted with the decorated service class.

.. versionadded:: 4.1
    The autowiring of the decorated service was introduced in Symfony 4.1.

If you are not using autowiring or the decorating service has more than one
constructor argument type-hinted with the decorated service class, you must
inject the decorated service explicitly (the ID of the decorated service is
automatically changed to ``decorating_service_id + '.inner'``):

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Mailer: ~

            App\DecoratingMailer:
                decorates: App\Mailer
                # pass the old service as an argument
                arguments: ['@App\DecoratingMailer.inner']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="App\Mailer" />

                <service id="App\DecoratingMailer"
                    decorates="App\Mailer"
                >
                    <argument type="service" id="App\DecoratingMailer.inner" />
                </service>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\DecoratingMailer;
        use App\Mailer;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(Mailer::class);

        $container->register(DecoratingMailer::class)
            ->setDecoratedService(Mailer::class)
            ->addArgument(new Reference(DecoratingMailer::class.'.inner'))
        ;

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
                xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- ... -->

                    <service
                        id="App\DecoratingMailer"
                        decorates="App\Mailer"
                        decoration-inner-name="App\DecoratingMailer.wooz"
                        public="false"
                    >
                        <argument type="service" id="App\DecoratingMailer.wooz" />
                    </service>

                </services>
            </container>

        .. code-block:: php

            // config/services.php
            use App\DecoratingMailer;
            use Symfony\Component\DependencyInjection\Reference;

            $container->register(DecoratingMailer::class)
                ->setDecoratedService(App\Mailer, DecoratingMailer::class.'.wooz')
                ->addArgument(new Reference(DecoratingMailer::class.'.wooz'))
                // ...
            ;

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
            public: false
            decorates: Foo
            decoration_priority: 5
            arguments: ['@Bar.inner']

        Baz:
            public: false
            decorates: Foo
            decoration_priority: 1
            arguments: ['@Baz.inner']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="Foo" />

                <service id="Bar" decorates="Foo" decoration-priority="5" public="false">
                    <argument type="service" id="Bar.inner" />
                </service>

                <service id="Baz" decorates="Foo" decoration-priority="1" public="false">
                    <argument type="service" id="Baz.inner" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(Foo:class)

        $container->register(Bar:class)
            ->addArgument(new Reference(Bar:class.'inner'))
            ->setPublic(false)
            ->setDecoratedService(Foo:class, null, 5);

        $container->register(Baz:class)
            ->addArgument(new Reference(Baz:class.'inner'))
            ->setPublic(false)
            ->setDecoratedService(Foo:class, null, 1);

The generated code will be the following::

    $this->services[Foo:class] = new Baz(new Bar(new Foo()));

.. _decorator pattern: https://en.wikipedia.org/wiki/Decorator_pattern
