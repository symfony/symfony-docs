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
            AppBundle\Mailer: ~

            # this replaces the old AppBundle\Mailer definition with the new one, the
            # old definition is lost
            AppBundle\Mailer:
                class: AppBundle\DecoratingMailer

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Mailer" />

                <!-- this replaces the old AppBundle\Mailer definition with the new
                     one, the old definition is lost -->
                <service id="AppBundle\Mailer" class="AppBundle\DecoratingMailer" />
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use AppBundle\Mailer;
        use AppBundle\DecoratingMailer;

        $container->register(Mailer::class);

        // this replaces the old AppBundle\Mailer definition with the new one, the
        // old definition is lost
        $container->register(Mailer::class, DecoratingMailer::class);

Most of the time, that's exactly what you want to do. But sometimes,
you might want to decorate the old service instead and keep the old service so
that you can reference it:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            AppBundle\Mailer: ~

            AppBundle\DecoratingMailer:
                # overrides the AppBundle\Mailer service
                # but that service is still available as AppBundle\Mailer.inner
                decorates: AppBundle\Mailer

                # pass the old service as an argument
                arguments: ['@AppBundle\DecoratingMailer.inner']

                # private, because usually you do not need to fetch AppBundle\DecoratingMailer directly
                public:    false

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Mailer" />

                <service id="AppBundle\DecoratingMailer"
                    decorates="AppBundle\Mailer"
                    public="false"
                >
                    <argument type="service" id="AppBundle\DecoratingMailer.inner" />
                </service>

            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use AppBundle\DecoratingMailer;
        use AppBundle\Mailer;
        use Symfony\Component\DependencyInjection\Reference;

        $container->register(Mailer::class);

        $container->register(DecoratingMailer::class)
            ->setDecoratedService(Mailer::class)
            ->addArgument(new Reference(DecoratingMailer::class.'.inner'))
            ->setPublic(false)
        ;

The ``decorates`` option tells the container that the ``AppBundle\DecoratingMailer`` service
replaces the ``AppBundle\Mailer`` service. The old ``AppBundle\Mailer`` service is renamed to
``AppBundle\DecoratingMailer.inner`` so you can inject it into your new service.

.. tip::

    The visibility (public) of the decorated ``AppBundle\Mailer`` service (which is an alias
    for the new service) will still be the same as the original ``AppBundle\Mailer``
    visibility.

.. note::

    The generated inner id is based on the id of the decorator service
    (``AppBundle\DecoratingMailer`` here), not of the decorated service (``AppBundle\Mailer``
    here). You can control the inner service name via the ``decoration_inner_name``
    option:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            services:
                AppBundle\DecoratingMailer:
                    # ...
                    decoration_inner_name: AppBundle\DecoratingMailer.wooz
                    arguments: ['@AppBundle\DecoratingMailer.wooz']

        .. code-block:: xml

            <!-- config/services.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
                xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

                <services>
                    <!-- ... -->

                    <service
                        id="AppBundle\DecoratingMailer"
                        decorates="AppBundle\Mailer"
                        decoration-inner-name="AppBundle\DecoratingMailer.wooz"
                        public="false"
                    >
                        <argument type="service" id="AppBundle\DecoratingMailer.wooz" />
                    </service>

                </services>
            </container>

        .. code-block:: php

            // config/services.php
            use AppBundle\DecoratingMailer;
            use Symfony\Component\DependencyInjection\Reference;

            $container->register(DecoratingMailer::class)
                ->setDecoratedService(AppBundle\Mailer, DecoratingMailer::class.'.wooz')
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
