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
In fact, the :ref:`default services.yml configuration <container-public>` configures
all services to be private by default.

You can also control the ``public`` option on a service-by-service basis:

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...

            AppBundle\Service\Foo:
                public: false

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Service\Foo" public="false" />
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Service\Foo;

        $container->register(Foo::class)
            ->setPublic(false);

Private services are special because they allow the container to optimize whether
and how they are instantiated. This increases the container's performance.

Now that the service is private, you *should not* fetch the service directly
from the container::

    use AppBundle\Service\Foo;

    $container->get(Foo::class);

This *may or may not work*, depending on how the container has optimized the
service instantiation and, even in the cases where it works, is
deprecated. Simply said: A service should be marked as private if you do not want
to access it directly from your code.

However, if a service has been marked as private, you can still alias it
(see below) to access this service (via the alias).

.. note::

    Services are by default public, but it's considered a good practice to mark
    as much services private as possible.

.. _services-alias:

Aliasing
--------

You may sometimes want to use shortcuts to access some services. You can
do so by aliasing them and, furthermore, you can even alias non-public
services.

.. configuration-block::

    .. code-block:: yaml

        services:
            # ...
            AppBundle\Mail\PhpMailer:
                public: false

            app.mailer:
                alias: AppBundle\Mail\PhpMailer
                public: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service class="AppBundle\Mail\PhpMailer" public="false" />

                <service id="app.mailer" alias="AppBundle\Mail\PhpMailer" />
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Mail\PhpMailer;

        $container->register(PhpMailer::class)
            ->setPublic(false);

        $container->setAlias('app.mailer', PhpMailer::class);

This means that when using the container directly, you can access the
``PhpMailer`` service by asking for the ``app.mailer`` service like this::

    $container->get('app.mailer'); // Would return a PhpMailer instance

.. tip::

    In YAML, you can also use a shortcut to alias a service:

    .. code-block:: yaml

        services:
            # ...
            app.mailer: '@app.phpmailer'

Deprecating Services
--------------------

Once you have decided to deprecate the use of a service (because it is outdated
or you decided not to maintain it anymore), you can deprecate its definition:

.. configuration-block::

    .. code-block:: yaml

       AppBundle\Service\OldService:
           deprecated: The "%service_id%" service is deprecated since 2.8 and will be removed in 3.0.

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="AppBundle\Service\OldService">
                    <deprecated>The "%service_id%" service is deprecated since 2.8 and will be removed in 3.0.</deprecated>
                </service>
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Service\OldService;

        $container
            ->register(OldService::class)
            ->setDeprecated(
                true,
                'The "%service_id%" service is deprecated since 2.8 and will be removed in 3.0.'
            )
        ;

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
