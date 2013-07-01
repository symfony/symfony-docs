.. index::
   single: Dependency Injection; Advanced configuration

Advanced Container Configuration
================================

Marking Services as public / private
------------------------------------

When defining services, you'll usually want to be able to access these definitions
within your application code. These services are called ``public``. For example,
the ``doctrine`` service registered with the container when using the DoctrineBundle
is a public service as you can access it via::

   $doctrine = $container->get('doctrine');

However, there are use-cases when you don't want a service to be public. This
is common when a service is only defined because it could be used as an
argument for another service.

.. note::

    If you use a private service as an argument to only one other service,
    this will result in an inlined instantiation (e.g. ``new PrivateFooBar()``)
    inside this other service, making it publicly unavailable at runtime.

Simply said: A service will be private when you do not want to access it
directly from your code.

Here is an example:

.. configuration-block::

    .. code-block:: yaml

        services:
           foo:
             class: Example\Foo
             public: false

    .. code-block:: xml

        <service id="foo" class="Example\Foo" public="false" />

    .. code-block:: php

        $definition = new Definition('Example\Foo');
        $definition->setPublic(false);
        $container->setDefinition('foo', $definition);

Now that the service is private, you *cannot* call::

    $container->get('foo');

However, if a service has been marked as private, you can still alias it (see
below) to access this service (via the alias).

.. note::

   Services are by default public.

Synthetic Services
------------------

Synthetic services are services that are injected into the container instead
of being created by the container.

For example, if you're using the :doc:`HttpKernel</components/http_kernel/introduction>`
component with the DependencyInjection component, then the the ``request``
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

        <service id="request"
            synthetic="true" />

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $container->setDefinition('request', new Definition())
            ->setSynthetic(true);

As you see, only the ``synthetic`` option is set. All other options are only used
to configure how a service is created by the container. As the service isn't
created by the container, these options are omitted.

Now, you can inject the class by using
:method:`Container::set<Symfony\\Component\\DependencyInjection\\Container::set>`::

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

        <service id="foo" class="Example\Foo"/>

        <service id="bar" alias="foo" />

    .. code-block:: php

        $definition = new Definition('Example\Foo');
        $container->setDefinition('foo', $definition);

        $containerBuilder->setAlias('bar', 'foo');

This means that when using the container directly, you can access the ``foo``
service by asking for the ``bar`` service like this::

    $container->get('bar'); // Would return the foo service

Requiring files
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

        <service id="foo" class="Example\Foo\Bar">
            <file>%kernel.root_dir%/src/path/to/file/foo.php</file>
        </service>

    .. code-block:: php

        $definition = new Definition('Example\Foo\Bar');
        $definition->setFile('%kernel.root_dir%/src/path/to/file/foo.php');
        $container->setDefinition('foo', $definition);

Notice that Symfony will internally call the PHP function require_once
which means that your file will be included only once per request.
