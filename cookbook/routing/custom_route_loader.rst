.. index::
   single: Routing; Custom route loader

How to Create a Custom Route Loader
===================================

A custom route loader allows you to add routes to an application, without
writing them down in for instance a Yaml file. This comes in handy when
you have made a bundle and don't want to manually add the routes for the
bundle to ``app/config/routing.yml``. Especially when you want to make the
bundle reusable, or when you have open-sourced it, this would slow down
the installation process and make it error-prone.

But you can also use a custom route loader when your routes correspond
to a certain pattern and you don't want to or can't write them all out by
hand. For instance when you have CRUD controllers of which the routes all
correspond to a pattern like ``*_new``, ``*_edit``, etc.

.. note::

    There are many bundles out there that use their own route loaders to
    accomplish cases like those described above, for instance
    `FOSRestBundle`_, `KnpRadBundle`_ and `SonataAdminBundle`_.

Loading Routes
--------------

The routes in a Symfony application are loaded by the
:class:`Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader`.
This loader uses several other loaders (delegates) to load resources of
different types, for instance Yaml files or ``@Route`` and ``@Method`` annotations
in controller files. The specialized loaders implement
:class:`Symfony\\Component\\Config\\Loader\\LoaderInterface`
and therefore have two important methods:
:method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
and :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load`.

Take these lines from ``routing.yml``:

.. code-block:: yaml

    _demo:
        resource: "@AcmeDemoBundle/Controller/DemoController.php"
        type:     annotation
        prefix:   /demo

The main loader tries all the delegate loaders and calls their
:method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
with the given resource (``@AcmeDemoBundle/Controller/DemoController.php``)
and type ("annotation") as arguments. When one of the loader returns ``true``,
its method :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load`
will be called, and the loader returns a :class:`Symfony\\Component\\Routing\\RouteCollection`
containing :class:`Symfony\\Component\\Routing\\Route` objects.

Creating a Custom Loader
------------------------

To load routes in another way than using annotations, Yaml or XML files,
you need to create a custom route loader. This loader should implement
:class:`Symfony\\Component\\Config\\Loader\\LoaderInterface`.

The sample loader below supports resources of type "extra". The resource
name itself is not used in the example::

    namespace Acme\DemoBundle\Routing;

    use Symfony\Component\Config\Loader\LoaderInterface;
    use Symfony\Component\Config\Loader\LoaderResolver;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;

    class ExtraLoader implements LoaderInterface
    {
        private $loaded = false;

        public function load($resource, $type = null)
        {
            if (true === $this->loaded) {
                throw new \RuntimeException('Do not add the "extra" loader twice');
            }

            $routes = new RouteCollection();

            // prepare a new route
            $pattern = '/extra/{parameter}';
            $defaults = array(
                '_controller' => 'AcmeDemoBundle:Demo:extra',
            );
            $requirements = array(
                'parameter' => '\d+',
            );
            $route = new Route($pattern, $defaults, $requirements);

            // add the new route to the route collection:
            $routeName = 'extraRoute';
            $routes->add($routeName, $route);

            return $routes;
        }

        public function supports($resource, $type = null)
        {
            return 'extra' === $type;
        }

        public function getResolver()
        {
            // will be explained later
        }

        public function setResolver(LoaderResolver $resolver)
        {
            // same here
        }
    }

.. note::

    Make sure the controller you specify really exists.

Now define a service for the ``ExtraLoader``:

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_demo.routing_loader:
                class: Acme\DemoBundle\Routing\ExtraLoader
                tags:
                    - { name: routing.loader }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

            <services>
                <service id="acme_demo.routing_loader" class="Acme\DemoBundle\Routing\ExtraLoader">
                    <tag name="routing.loader" />
                </service>
            </services>
        </container>

    .. code-block:: php

        use Symfony\Component\DependencyInjection\Definition;

        $container
            ->setDefinition(
                'acme_demo.routing_loader',
                new Definition('Acme\DemoBundle\Routing\ExtraLoader')
            )
            ->addTag('routing.loader')
        ;

Notice the tag ``routing.loader``. All services with this tag will be marked
as potential route loaders and added as specialized routers to the
:class:`Symfony\\Bundle\\FrameworkBundle\\Routing\\DelegatingLoader`.

Finally, we only need to add a few extra lines to the routing configuration:

.. configuration-block::

    .. code-block:: yaml

        AcmeDemoBundle_Extra:
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

        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->addCollection($loader->import('.', 'extra'));

        return $collection;

The important part here is the ``type`` key. Its value should be "extra".
This is the type which our ``ExtraLoader`` supports and this will make sure
its ``load()`` method gets called. The ``resource`` key is insignificant
for the ``ExtraLoader``, so we set it to ".".

.. note::

    The routes defined using custom route loaders will be automatically
    cached by the framework. So whenever you change something to the behavior
    of the loader, don't forget to clear the cache.


More Advanced Loaders
---------------------

In most cases it's better not to implement
:class:`Symfony\\Component\\Config\\Loader\\LoaderInterface`
yourself, but extend from :class:`Symfony\\Component\\Config\\Loader\\Loader`.
This class knows how to use a :class:`Symfony\\Component\\Config\\Loader\\LoaderResolver`
to load secondary routing resources.

Of course you still need to implement
:method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::supports`
and :method:`Symfony\\Component\\Config\\Loader\\LoaderInterface::load`.
Whenever you want to load another resource, for instance a Yaml routing
configuration file, you can call the
:method:`Symfony\\Component\\Config\\Loader\\Loader::import` method::

    namespace Acme\DemoBundle\Routing;

    use Symfony\Component\Config\Loader\Loader;
    use Symfony\Component\Routing\RouteCollection;

    class AdvancedLoader extends Loader
    {
        public function load($resource, $type = null)
        {
            $collection = new RouteCollection();

            $resource = '@AcmeDemoBundle/Resources/config/import_routing.yml';
            $type = 'yaml';

            $importedRoutes = $this->import($resource, $type);

            $collection->addCollection($importedRoutes);

            return $collection;
        }

        public function supports($resource, $type = null)
        {
            return $type === 'advanced_extra';
        }
    }

.. note::

    The resource name and type of the imported routing configuration can
    be anything that would normally be supported by the routing configuration
    loader (Yaml, XML, PHP, annotation, etc.).

.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`KnpRadBundle`: https://github.com/KnpLabs/KnpRadBundle
.. _`SonataAdminBundle`: https://github.com/sonata-project/SonataAdminBundle
