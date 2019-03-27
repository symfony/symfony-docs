.. index::
    single: Routing; Importing routing resources

How to Include External Routing Resources
=========================================

Simple applications can define all their routes in a single configuration file -
usually ``config/routes.yaml`` (see :ref:`routing-creating-routes`).
However, in most applications it's common to import routes definitions from
different resources: PHP annotations in controller files, YAML, XML or PHP
files stored in some directory, etc.

This can be done by importing routing resources from the main routing file:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        app_file:
            # loads routes from the given routing file stored in some bundle
            resource: '@AcmeOtherBundle/Resources/config/routing.yaml'

        app_annotations:
            # loads routes from the PHP annotations of the controllers found in that directory
            resource: '../src/Controller/'
            type:     annotation

        app_directory:
            # loads routes from the YAML, XML or PHP files found in that directory
            resource: '../legacy/routing/'
            type:     directory

        app_bundle:
            # loads routes from the YAML, XML or PHP files found in some bundle directory
            resource: '@AcmeOtherBundle/Resources/config/routing/'
            type:     directory

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- loads routes from the given routing file stored in some bundle -->
            <import resource="@AcmeOtherBundle/Resources/config/routing.yaml"/>

            <!-- loads routes from the PHP annotations of the controllers found in that directory -->
            <import resource="../src/Controller/" type="annotation"/>

            <!-- loads routes from the YAML or XML files found in that directory -->
            <import resource="../legacy/routing/" type="directory"/>

            <!-- loads routes from the YAML or XML files found in some bundle directory -->
            <import resource="@AcmeOtherBundle/Resources/config/routing/" type="directory"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            // loads routes from the given routing file stored in some bundle
            $routes->import('@AcmeOtherBundle/Resources/config/routing.yaml');

            // loads routes from the PHP annotations of the controllers found in that directory
            $routes->import('../src/Controller/', 'annotation');

            // loads routes from the YAML or XML files found in that directory
            $routes->import('../legacy/routing/', 'directory');

            // loads routes from the YAML or XML files found in some bundle directory
            $routes->import('@AcmeOtherBundle/Resources/config/routing/public/', 'directory');
        };

.. note::

    When importing resources, the key (e.g. ``app_file``) is the name of collection.
    Just be sure that it's unique per file so no other lines override it.

.. _prefixing-imported-routes:

Prefixing the URLs of Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to provide a "prefix" for the imported routes. For example,
suppose you want to prefix all application routes with ``/site`` (e.g.
``/site/blog/{slug}`` instead of ``/blog/{slug}``):

.. configuration-block::

    .. code-block:: php-annotations

        use Symfony\Component\Routing\Annotation\Route;

        /**
         * @Route("/site")
         */
        class DefaultController
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        controllers:
            resource: '../src/Controller/'
            type:     annotation
            prefix:   /site

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="../src/Controller/" type="annotation" prefix="/site"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../src/Controller/', 'annotation')
                ->prefix('/site')
            ;
        };

The path of each route being loaded from the new routing resource will now
be prefixed with the string ``/site``.

.. note::

    If any of the prefixed routes defines an empty path, Symfony adds a trailing
    slash to it. In the previous example, an empty path prefixed with ``/site``
    will result in the ``/site/`` URL. If you want to avoid this behavior, set
    the ``trailing_slash_on_root`` option to ``false``:

    .. configuration-block::

        .. code-block:: yaml

            # config/routes.yaml
            controllers:
                resource: '../src/Controller/'
                type:     annotation
                prefix:   /site
                trailing_slash_on_root: false

        .. code-block:: xml

            <!-- config/routes.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                    https://symfony.com/schema/routing/routing-1.0.xsd">

                <import
                    resource="../src/Controller/"
                    type="annotation"
                    prefix="/site"
                    trailing-slash-on-root="false"/>
            </routes>

        .. code-block:: php

            // config/routes.php
            namespace Symfony\Component\Routing\Loader\Configurator;

            use App\Controller\ArticleController;

            return function (RoutingConfigurator $routes) {
                $routes->import('../src/Controller/', 'annotation')
                    ->prefix('/site', false)
                ;
            };

Prefixing the Names of Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You also have the possibility to prefix the names of all the routes defined in
a controller class or imported from a configuration file:

.. configuration-block::

    .. code-block:: php-annotations

        use Symfony\Component\Routing\Annotation\Route;

        /**
         * @Route(name="blog_")
         */
        class BlogController extends AbstractController
        {
            /**
             * @Route("/blog", name="index")
             */
            public function index()
            {
                // ...
            }

            /**
             * @Route("/blog/posts/{slug}", name="post")
             */
            public function show(Post $post)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        controllers:
            resource:    '../src/Controller/'
            type:        annotation
            name_prefix: 'blog_'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import
                resource="../src/Controller/"
                type="annotation"
                name-prefix="blog_"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        return function (RoutingConfigurator $routes) {
            $routes->import('../src/Controller/', 'annotation')
                ->namePrefix('blog_')
            ;
        };

In this example, the names of the routes will be ``blog_index`` and ``blog_post``.

Adding a Host Requirement to Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can set the host regex on imported routes. For more information, see
:ref:`component-routing-host-imported`.
