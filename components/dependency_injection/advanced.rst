.. index::
    single: DependencyInjection; Advanced configuration

Advanced Container Configuration
================================

.. _container-private-services:

Marking Services as Public / Private
------------------------------------

When defining services, you'll usually want to be able to access these definitions
within your application code. These services are called ``public``. For
example, the ``doctrine`` service registered with the container when using
the DoctrineBundle is a public service. This means that you can fetch it
from the container using the ``get()`` method::

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

        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('Example\Foo');
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

   Services are by default public.

Aliasing
--------

You may sometimes want to use shortcuts to access some services. You can
do so by aliasing them and, furthermore, you can even alias non-public
services.

.. configuration-block::

    .. code-block:: yaml

        services:
           foo:
             class: Example\Foo
           bar:
             alias: foo

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="foo" class="Example\Foo" />

                <service id="bar" alias="foo" />
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container->setDefinition('foo', new Definition('Example\Foo'));

        $containerBuilder->setAlias('bar', 'foo');

This means that when using the container directly, you can access the ``foo``
service by asking for the ``bar`` service like this::

    $container->get('bar'); // Would return the foo service

.. tip::

    In YAML, you can also use a shortcut to alias a service:

    .. code-block:: yaml

        services:
           foo:
             class: Example\Foo
           bar: '@foo'

Decorating Services
-------------------

When overriding an existing definition, the old service is lost:

.. code-block:: php

    $container->register('foo', 'FooService');

    // this is going to replace the old definition with the new one
    // old definition is lost
    $container->register('foo', 'CustomFooService');

Most of the time, that's exactly what you want to do. But sometimes,
you might want to decorate the old one instead. In this case, the
old service should be kept around to be able to reference it in the
new one. This configuration replaces ``foo`` with a new one, but keeps
a reference of the old one  as ``bar.inner``:

.. configuration-block::

    .. code-block:: yaml

       bar:
         public: false
         class: stdClass
         decorates: foo
         arguments: ["@bar.inner"]

    .. code-block:: xml

        <service id="bar" class="stdClass" decorates="foo" public="false">
            <argument type="service" id="bar.inner" />
        </service>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        $container->register('bar', 'stdClass')
            ->addArgument(new Reference('bar.inner'))
            ->setPublic(false)
            ->setDecoratedService('foo');

Here is what's going on here: the ``setDecoratedService()`` method tells
the container that the ``bar`` service should replace the ``foo`` service,
renaming ``foo`` to ``bar.inner``.
By convention, the old ``foo`` service is going to be renamed ``bar.inner``,
so you can inject it into your new service.

.. note::
    The generated inner id is based on the id of the decorator service
    (``bar`` here), not of the decorated service (``foo`` here).  This is
    mandatory to allow several decorators on the same service (they need to have
    different generated inner ids).

    Most of the time, the decorator should be declared private, as you will not
    need to retrieve it as ``bar`` from the container. The visibility of the
    decorated ``foo`` service (which is an alias for ``bar``) will still be the
    same as the original ``foo`` visibility.

You can change the inner service name if you want to:

.. configuration-block::

    .. code-block:: yaml

       bar:
         class: stdClass
         public: false
         decorates: foo
         decoration_inner_name: bar.wooz
         arguments: ["@bar.wooz"]

    .. code-block:: xml

        <service id="bar" class="stdClass" decorates="foo" decoration-inner-name="bar.wooz" public="false">
            <argument type="service" id="bar.wooz" />
        </service>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        $container->register('bar', 'stdClass')
            ->addArgument(new Reference('bar.wooz'))
            ->setPublic(false)
            ->setDecoratedService('foo', 'bar.wooz');

.. versionadded:: 2.8
    The ability to define the decoration priority was introduced in Symfony 2.8.
    Prior to Symfony 2.8, the priority depends on the order in
    which definitions are found.

If you want to apply more than one decorator to a service, you can control their
order by configuring the priority of decoration, this can be any integer number
(decorators with higher priorities will be applied first).

.. configuration-block::

    .. code-block:: yaml

        foo:
            class: Foo

        bar:
            class: Bar
            public: false
            decorates: foo
            decoration_priority: 5
            arguments: ['@bar.inner']

        baz:
            class: Baz
            public: false
            decorates: foo
            decoration_priority: 1
            arguments: ['@baz.inner']

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="foo" class="Foo" />

                <service id="bar" class="Bar" decorates="foo" decoration-priority="5" public="false">
                    <argument type="service" id="bar.inner" />
                </service>

                <service id="baz" class="Baz" decorates="foo" decoration-priority="1" public="false">
                    <argument type="service" id="baz.inner" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Reference;

        $container->register('foo', 'Foo')

        $container->register('bar', 'Bar')
            ->addArgument(new Reference('bar.inner'))
            ->setPublic(false)
            ->setDecoratedService('foo', null, 5);

        $container->register('baz', 'Baz')
            ->addArgument(new Reference('baz.inner'))
            ->setPublic(false)
            ->setDecoratedService('foo', null, 1);

The generated code will be the following:

.. code-block:: php

    $this->services['foo'] = new Baz(new Bar(new Foo())));

Deprecating Services
--------------------

.. versionadded:: 2.8
    The ``deprecated`` setting was introduced in Symfony 2.8.

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
