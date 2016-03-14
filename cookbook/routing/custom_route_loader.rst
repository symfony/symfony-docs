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
``app/config/routing.yml``) manually, even when using a custom route
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
different types, for instance YAML files or ``@Route`` and ``@Method`` annotations
in controller files. The specialized loaders implement
:class:`Symfony\\Component\\Config\\Loader\\LoaderInterface`
and therefore have two important methods:
:method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
and :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load`.

Take these lines from the ``routing.yml`` in the Symfony Standard Edition:

.. code-block:: yaml

    # app/config/routing.yml
    app:
        resource: '@AppBundle/Controller/'
        type:     annotation

When the main loader parses this, it tries all registered delegate loaders and calls
their :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
method with the given resource (``@AppBundle/Controller/``)
and type (``annotation``) as arguments. When one of the loader returns ``true``,
its :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load` method
will be called, which should return a :class:`Symfony\\Component\\Routing\\RouteCollection`
containing :class:`Symfony\\Component\\Routing\\Route` objects.

.. note::

    Routes loaded this way will be cached by the Router the same way as
    when they are defined in one of the default formats (e.g. XML, YML,
    PHP file).

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
support the same type of resource. Just make up a name specific to what
you do. The resource name itself is not actually used in the example::

    // src/AppBundle/Routing/ExtraLoader.php
    namespace AppBundle\Routing;

    use Symfony\Component\Config\Loader\Loader;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    class ExtraLoader extends Loader
    {
        private $loaded = false;

        public function load($resource, $type = null)
        {
            if (true === $this->loaded) {
                throw new \RuntimeException('Do not add the "extra" loader twice');
            }

            $routes = new RouteCollection();

            // prepare a new route
            $path = '/extra/{parameter}';
            $defaults = array(
                '_controller' => 'AppBundle:Extra:extra',
            );
            $requirements = array(
                'parameter' => '\d+',
            );
            $route = new Route($path, $defaults, $requirements);

            // add the new route to the route collection
            $routeName = 'extraRoute';
            $routes->add($routeName, $route);

            $this->loaded = true;

            return $routes;
        }

        public function supports($resource, $type = null)
        {
            return 'extra' === $type;
        }
    }

Make sure the controller you specify really exists. In this case you
have to create an ``extraAction`` method in the ``ExtraController``
of the ``AppBundle``::

    // src/AppBundle/Controller/ExtraController.php
    namespace AppBundle\Controller;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class ExtraController extends Controller
    {
        public function extraAction($parameter)
        {
            return new Response($parameter);
        }
    }

Now define a service for the ``ExtraLoader``:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.routing_loader:
                class: AppBundle\Routing\ExtraLoader
                tags:
                    - { name: routing.loader }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="app.routing_loader" class="AppBundle\Routing\ExtraLoader">
                    <tag name="routing.loader" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->setDefinition(
                'app.routing_loader',
                new Definition('AppBundle\Routing\ExtraLoader')
            )
            ->addTag('routing.loader')
        ;

Notice the tag ``routing.loader``. All services with this *tag* will be marked
as potential route loaders and added as specialized route loaders to the
``routing.loader`` *service*, which is an instance of
:class:`Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader`.

Using the custom Loader
~~~~~~~~~~~~~~~~~~~~~~~

If you did nothing else, your custom routing loader would *not* be called.
What remains to do is adding a few lines to the routing configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        app_extra:
            resource: .
            type: extra

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="." type="extra" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->addCollection($loader->import('.', 'extra'));

        return $collection;

The important part here is the ``type`` key. Its value should be "extra" as
this is the type which the ``ExtraLoader`` supports and this will make sure
its ``load()`` method gets called. The ``resource`` key is insignificant
for the ``ExtraLoader``, so it is set to ".".

.. note::

    The routes defined using custom route loaders will be automatically
    cached by the framework. So whenever you change something in the loader
    class itself, don't forget to clear the cache.

More advanced Loaders
---------------------

If your custom route loader extends from
:class:`Symfony\\Component\\Config\\Loader\\Loader` as shown above, you
can also make use of the provided resolver, an instance of
:class:`Symfony\\Component\\Config\\Loader\\LoaderResolver`, to load secondary
routing resources.

Of course you still need to implement
:method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
and :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load`.
Whenever you want to load another resource - for instance a YAML routing
configuration file - you can call the
:method:`Symfony\\Component\\Config\\Loader\\Loader::import` method::

    // src/AppBundle/Routing/AdvancedLoader.php
    namespace AppBundle\Routing;

    use Symfony\Component\Config\Loader\Loader;
    use Symfony\Component\Routing\RouteCollection;

    class AdvancedLoader extends Loader
    {
        public function load($resource, $type = null)
        {
            $collection = new RouteCollection();

            $resource = '@AppBundle/Resources/config/import_routing.yml';
            $type = 'yaml';

            $importedRoutes = $this->import($resource, $type);

            $collection->addCollection($importedRoutes);

            return $collection;
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

.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`JMSI18nRoutingBundle`: https://github.com/schmittjoh/JMSI18nRoutingBundle
.. _`KnpRadBundle`: https://github.com/KnpLabs/KnpRadBundle
.. _`SonataAdminBundle`: https://github.com/sonata-project/SonataAdminBundle
