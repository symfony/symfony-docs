.. index::
    single: Service Container; Decoration

How to Decorating Services
==========================

When overriding an existing definition (e.g. when applying the `Decorator pattern`_),
the original service is lost:

.. configuration-block::

    .. code-block:: yaml

        services:
            app.mailer:
                class: AppBundle\Mailer

            # this replaces the old app.mailer definition with the new one, the
            # old definition is lost
            app.mailer:
                class AppBundle\DecoratingMailer

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>

                <service id="app.mailer" class="AppBundle\Mailer" />

                <!-- this replaces the old app.mailer definition with the new
                     one, the old definition is lost -->
                <service id="app.mailer" class="AppBundle\DecoratingMailer" />

            </service>
        </container>

    .. code-block:: php

        $container->register('mailer', 'AppBundle\Mailer');

        // this replaces the old app.mailer definition with the new one, the
        // old definition is lost
        $container->register('mailer', 'AppBundle\DecoratingMailer');

Most of the time, that's exactly what you want to do. But sometimes,
you might want to decorate the old one instead. In this case, the
old service should be kept around to be able to reference it in the
new one. This configuration replaces ``app.mailer`` with a new one, but keeps
a reference of the old one  as ``app.decorating_mailer.inner``:

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...

            app.decorating_mailer:
              class:     AppBundle\DecoratingMailer
              decorates: app.mailer
              arguments: ['@app.decorating_mailer.inner']
              public:    false

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
            xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd"
        >
            <services>
                <!-- ... -->

                <service id="app.decorating_mailer"
                    class="AppBundle\DecoratingMailer"
                    decorates="app.mailer"
                    public="false"
                >
                    <argument type="service" id="app.decorating_mailer.inner" />
                </service>

            </service>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        // ...
        $container->register('app.decorating_mailer', 'AppBundle\DecoratingMailer')
            ->setDecoratedService('app.mailer')
            ->addArgument(new Reference('app.decorating_mailer.inner'))
            ->setPublic(false)
        ;

Here is what's going on here: the ``decorates`` option tells the container that
the ``app.decorating_mailer`` service replaces the ``app.mailer`` service. By
convention, the old ``app.mailer`` service is renamed to
``app.decorating_mailer.inner``, so you can inject it into your new service.

.. tip::

    Most of the time, the decorator should be declared private, as you will not
    need to retrieve it as ``app.decorating_mailer`` from the container.

    The visibility of the decorated ``app.mailer`` service (which is an alias
    for the new service) will still be the same as the original ``app.mailer``
    visibility.

.. note::

    The generated inner id is based on the id of the decorator service
    (``app.decorating_mailer`` here), not of the decorated service (``app.mailer``
    here). This is mandatory to allow several decorators on the same service
    (they need to have different generated inner ids).

    You can change the inner service name if you want to using the
    ``decoration_inner_name`` option:

    .. configuration-block::

        .. code-block:: yaml

        services:
            app.mailer:
                # ...
                decoration_inner_name: app.decorating_mailer.wooz
                arguments: ['@app.decorating_mailer.wooz']

        .. code-block:: xml

            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsd="http://www.w3.org/2001/XMLSchema-instance"
                xsd:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd"
            >
                <services>
                    <!-- ... -->

                    <service
                        decoration-inner-name="app.decorating_mailer.wooz"
                    >
                        <argument type="service" id="app.decorating_mailer.wooz" />
                    </service>

                </service>
            </container>

        .. code-block:: php

            use Symfony\Component\DependencyInjection\Reference;

            $container->register('app.decorating_mailer', 'AppBundle\DeocratingMailer')
                ->setDecoratedService('foo', 'app.decorating_mailer.wooz')
                ->addArgument(new Reference('app.decorating_mailer.wooz'))
                // ...
            ;

.. _decorator pattern: https://en.wikipedia.org/wiki/Decorator_pattern
