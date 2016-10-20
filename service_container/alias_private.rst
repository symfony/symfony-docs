.. index::
    single: DependencyInjection; Advanced configuration

How to Create Service Aliases and Mark Services as Private
==========================================================

.. _container-private-services:

Marking Services as Public / Private
------------------------------------

When defining services, you'll usually want to be able to access these definitions
within your application code. These services are called *public*. For
example, the ``doctrine`` service is a public service. This means that you can
fetch it from the container using the ``get()`` method::

    $doctrine = $container->get('doctrine');

In some cases, a service *only* exists to be injected into another service
and is *not* intended to be fetched directly from the container as shown
above.

.. _inlined-private-services:

In these cases, to get a minor performance boost and ensure the service will not
be retrieved directly from the container, you can set the service to be *not*
public (i.e. private):

.. configuration-block::

    .. code-block:: yaml

        services:
            foo:
                class: Example\Foo
                public: false

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="foo" class="Example\Foo" public="false" />
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('Example\Foo');
        $definition->setPublic(false);
        $container->setDefinition('foo', $definition);

What makes private services special is that, since the container knows that the
service will never be requested from outside, it can optimize whether and how it
is instanciated. This increases the container's performance.

Now that the service is private, you *should not* fetch the service directly
from the container::

    $container->get('foo');

This *may or may not work*, depending on how the container has optimized the
service instanciation and, even in the cases where it works, is
deprecated. Simply said: A service can be marked as private if you do not want
to access it directly from your code.

However, if a service has been marked as private, you can still alias it
(see below) to access this service (via the alias).

.. note::

    Services are by default public, but it's considered a good practice to mark
    as much services private as possible.

Aliasing
--------

You may sometimes want to use shortcuts to access some services. You can
do so by aliasing them and, furthermore, you can even alias non-public
services.

.. configuration-block::

    .. code-block:: yaml

        services:
            app.phpmailer:
                class: AppBundle\Mail\PhpMailer

            app.mailer:
                alias: app.phpmailer

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.phpmailer" class="AppBundle\PhpMailer" />

                <service id="app.mailer" alias="app.phpmailer" />
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('app.phpmailer', new Definition('AppBundle\PhpMailer'));

        $containerBuilder->setAlias('app.mailer', 'app.phpmailer');

This means that when using the container directly, you can access the
``app.phpmailer`` service by asking for the ``app.mailer`` service like this::

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

       acme.my_service:
           class: ...
           deprecated: The "%service_id%" service is deprecated since 2.8 and will be removed in 3.0.

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-Instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="acme.my_service" class="...">
                    <deprecated>The "%service_id%" service is deprecated since 2.8 and will be removed in 3.0.</deprecated>
                </service>
            </services>
        </container>

    .. code-block:: php

        $container
            ->register('acme.my_service', '...')
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

For service decorators (see above), if the definition does not modify the
deprecated status, it will inherit the status from the definition that is
decorated.

.. caution::

    The ability to "un-deprecate" a service is possible only when declaring the
    definition in PHP.
