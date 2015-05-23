.. index::
   single: DependencyInjection; Advanced configuration

Advanced Container Configuration
================================

Marking Services as public / private
------------------------------------

When defining services, you'll usually want to be able to access these definitions
within your application code. These services are called ``public``. For example,
the ``doctrine`` service registered with the container when using the DoctrineBundle
is a public service. This means that you can fetch it from the container
using the ``get()`` method::

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

However, if a service has been marked as private, you can still alias it (see
below) to access this service (via the alias).

.. note::

   Services are by default public.

Synthetic Services
------------------

Synthetic services are services that are injected into the container instead
of being created by the container.

For example, if you're using the :doc:`HttpKernel </components/http_kernel/introduction>`
component with the DependencyInjection component, then the ``request``
service is injected in the
:method:`ContainerAwareHttpKernel::handle() <Symfony\\Component\\HttpKernel\\DependencyInjection\\ContainerAwareHttpKernel::handle>`
method when entering the request :doc:`scope </cookbook/service_container/scopes>`.
The class does not exist when there is no request, so it can't be included in
the container configuration. Also, the service should be different for every
subrequest in the application.

To create a synthetic service, set ``synthetic`` to ``true``:

.. configuration-block::

    .. code-block:: yaml

        services:
            request:
                synthetic: true

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="request" synthetic="true" />
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->setDefinition('request', new Definition())
            ->setSynthetic(true);

As you see, only the ``synthetic`` option is set. All other options are only used
to configure how a service is created by the container. As the service isn't
created by the container, these options are omitted.

Now, you can inject the class by using
:method:`Container::set <Symfony\\Component\\DependencyInjection\\Container::set>`::

    // ...
    $container->set('request', new MyRequest(...));

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
           bar: "@foo"


Requiring Files
---------------

There might be use cases when you need to include another file just before
the service itself gets loaded. To do so, you can use the ``file`` directive.

.. configuration-block::

    .. code-block:: yaml

        services:
           foo:
             class: Example\Foo\Bar
             file: "%kernel.root_dir%/src/path/to/file/foo.php"

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="foo" class="Example\Foo\Bar">
                    <file>%kernel.root_dir%/src/path/to/file/foo.php</file>
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('Example\Foo\Bar');
        $definition->setFile('%kernel.root_dir%/src/path/to/file/foo.php');
        $container->setDefinition('foo', $definition);

Notice that Symfony will internally call the PHP statement ``require_once``,
which means that your file will be included only once per request.

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
