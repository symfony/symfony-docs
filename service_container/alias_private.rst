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

In these cases, to get a minor performance boost, you can set the service
to be *not* public (i.e. private):

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

        use Example\Foo;
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition(Foo::class);
        $definition->setPublic(false);
        $container->setDefinition('foo', $definition);

What makes private services special is that, if they are only injected once,
they are converted from services to inlined instantiations (e.g. ``new PrivateThing()``).
This increases the container's performance.

Now that the service is private, you *should not* fetch the service directly
from the container::

    $container->get('foo');

This *may or may not work*, depending on if the service could be inlined.
Simply said: A service can be marked as private if you do not want to access
it directly from your code.

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
                <service id="app.phpmailer" class="AppBundle\Mail\PhpMailer" />

                <service id="app.mailer" alias="app.phpmailer" />
            </services>
        </container>

    .. code-block:: php

        use AppBundle\Mail\PhpMailer;
        use Symfony\Component\DependencyInjection\Alias;
        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('app.phpmailer', new Definition(PhpMailer::class));

        $containerBuilder->setAlias('app.mailer', 'app.phpmailer');

        // private aliases are created passing 'false' to Alias() second argument
        $containerBuilder->setAlias('app.mailer', new Alias('app.phpmailer', false));

This means that when using the container directly, you can access the
``app.phpmailer`` service by asking for the ``app.mailer`` service like this::

    $container->get('app.mailer'); // Would return a PhpMailer instance

.. tip::

    In YAML, you can also use a shortcut to alias a service:

    .. code-block:: yaml

        services:
            # ...
            app.mailer: '@app.phpmailer'
