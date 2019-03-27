.. index::
   single: Routing; Custom route loader

How to Create a custom Route Loader
===================================

What is a Custom Route Loader
-----------------------------

A custom route loader enables you to generate routes based on some
conventions or patterns. A great example for this use-case is the
`FOSRestBundle`_ where routes are generated based on the names of the
action methods in a controller.

You still need to modify your routing configuration (e.g.
``config/routes.yaml``) manually, even when using a custom route
loader.

.. note::

    There are many bundles out there that use their own route loaders to
    accomplish cases like those described above, for instance
    `FOSRestBundle`_, `JMSI18nRoutingBundle`_, `KnpRadBundle`_ and
    `SonataAdminBundle`_.

Loading Routes
--------------

The routes in a Symfony application are loaded by the
:class:`Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader`.
This loader uses several other loaders (delegates) to load resources of
different types, for instance YAML files or ``@Route`` annotations in controller
files. The specialized loaders implement
:class:`Symfony\\Component\\Config\\Loader\\LoaderInterface`
and therefore have two important methods:
:method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
and :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load`.

Take these lines from the ``routes.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        controllers:
            resource: ../src/Controller/
            type: annotation

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="../src/Controller" type="annotation"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../src/Controller', 'annotation');
        };

When the main loader parses this, it tries all registered delegate loaders and calls
their :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
method with the given resource (``../src/Controller/``)
and type (``annotation``) as arguments. When one of the loader returns ``true``,
its :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load` method
will be called, which should return a :class:`Symfony\\Component\\Routing\\RouteCollection`
containing :class:`Symfony\\Component\\Routing\\Route` objects.

.. note::

    Routes loaded this way will be cached by the Router the same way as
    when they are defined in one of the default formats (e.g. XML, YAML,
    PHP file).

Loading Routes with a Custom Service
------------------------------------

Using a regular Symfony service is the simplest way to load routes in a
customized way. It's much easier than creating a full custom route loader, so
you should always consider this option first.

To do so, define ``type: service`` as the type of the loaded routing resource
and configure the service and method to call:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        admin_routes:
            resource: 'admin_route_loader:loadRoutes'
            type: service

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="admin_route_loader:loadRoutes" type="service"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('admin_route_loader:loadRoutes', 'service');
        };

In this example, the routes are loaded by calling the ``loadRoutes()`` method
of the service whose ID is ``admin_route_loader``. Your service doesn't have to
extend or implement any special class, but the called method must return a
:class:`Symfony\\Component\\Routing\\RouteCollection` object.

.. note::

    The routes defined using service route loaders will be automatically
    cached by the framework. So whenever your service should load new routes,
    don't forget to clear the cache.

Creating a custom Loader
------------------------

To load routes from some custom source (i.e. from something other than annotations,
YAML or XML files), you need to create a custom route loader. This loader
has to implement :class:`Symfony\\Component\\Config\\Loader\\LoaderInterface`.

In most cases it is easier to extend from
:class:`Symfony\\Component\\Config\\Loader\\Loader` instead of implementing
:class:`Symfony\\Component\\Config\\Loader\\LoaderInterface` yourself.

The sample loader below supports loading routing resources with a type of
``extra``. The type name should not clash with other loaders that might
support the same type of resource. Make up any name specific to what
you do. The resource name itself is not actually used in the example::

    // src/Routing/ExtraLoader.php
    namespace App\Routing;

    use Symfony\Component\Config\Loader\Loader;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    class ExtraLoader extends Loader
    {
        private $isLoaded = false;

        public function load($resource, $type = null)
        {
            if (true === $this->isLoaded) {
                throw new \RuntimeException('Do not add the "extra" loader twice');
            }

            $routes = new RouteCollection();

            // prepare a new route
            $path = '/extra/{parameter}';
            $defaults = [
                '_controller' => 'App\Controller\ExtraController::extra',
            ];
            $requirements = [
                'parameter' => '\d+',
            ];
            $route = new Route($path, $defaults, $requirements);

            // add the new route to the route collection
            $routeName = 'extraRoute';
            $routes->add($routeName, $route);

            $this->isLoaded = true;

            return $routes;
        }

        public function supports($resource, $type = null)
        {
            return 'extra' === $type;
        }
    }

Make sure the controller you specify really exists. In this case you
have to create an ``extra()`` method in the ``ExtraController``::

    // src/Controller/ExtraController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    class ExtraController extends AbstractController
    {
        public function extra($parameter)
        {
            return new Response($parameter);
        }
    }

Now define a service for the ``ExtraLoader``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Routing\ExtraLoader:
                tags: [routing.loader]

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <!-- ... -->

                <service id="App\Routing\ExtraLoader">
                    <tag name="routing.loader"/>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Routing\ExtraLoader;

        $container->autowire(ExtraLoader::class)
            ->addTag('routing.loader')
        ;

Notice the tag ``routing.loader``. All services with this *tag* will be marked
as potential route loaders and added as specialized route loaders to the
``routing.loader`` *service*, which is an instance of
:class:`Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader`.

Using the Custom Loader
~~~~~~~~~~~~~~~~~~~~~~~

If you did nothing else, your custom routing loader would *not* be called.
What remains to do is adding a few lines to the routing configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        app_extra:
            resource: .
            type: extra

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="." type="extra"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('.', 'extra');
        };

The important part here is the ``type`` key. Its value should be ``extra`` as
this is the type which the ``ExtraLoader`` supports and this will make sure
its ``load()`` method gets called. The ``resource`` key is insignificant
for the ``ExtraLoader``, so it is set to ``.`` (a single dot).

.. note::

    The routes defined using custom route loaders will be automatically
    cached by the framework. So whenever you change something in the loader
    class itself, don't forget to clear the cache.

More Advanced Loaders
---------------------

If your custom route loader extends from
:class:`Symfony\\Component\\Config\\Loader\\Loader` as shown above, you
can also make use of the provided resolver, an instance of
:class:`Symfony\\Component\\Config\\Loader\\LoaderResolver`, to load secondary
routing resources.

You still need to implement
:method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
and :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load`.
Whenever you want to load another resource - for instance a YAML routing
configuration file - you can call the
:method:`Symfony\\Component\\Config\\Loader\\Loader::import` method::

    // src/Routing/AdvancedLoader.php
    namespace App\Routing;

    use Symfony\Component\Config\Loader\Loader;
    use Symfony\Component\Routing\RouteCollection;

    class AdvancedLoader extends Loader
    {
        public function load($resource, $type = null)
        {
            $routes = new RouteCollection();

            $resource = '@ThirdPartyBundle/Resources/config/routes.yaml';
            $type = 'yaml';

            $importedRoutes = $this->import($resource, $type);

            $routes->addCollection($importedRoutes);

            return $routes;
        }

        public function supports($resource, $type = null)
        {
            return 'advanced_extra' === $type;
        }
    }

.. note::

    The resource name and type of the imported routing configuration can
    be anything that would normally be supported by the routing configuration
    loader (YAML, XML, PHP, annotation, etc.).

.. note::

    For more advanced uses, check out the `ChainRouter`_ provided by the Symfony
    CMF project. This router allows applications to use two or more routers
    combined, for example to keep using the default Symfony routing system when
    writing a custom router.

.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`JMSI18nRoutingBundle`: https://github.com/schmittjoh/JMSI18nRoutingBundle
.. _`KnpRadBundle`: https://github.com/KnpLabs/KnpRadBundle
.. _`SonataAdminBundle`: https://github.com/sonata-project/SonataAdminBundle
.. _`ChainRouter`: https://symfony.com/doc/current/cmf/components/routing/chain.html
