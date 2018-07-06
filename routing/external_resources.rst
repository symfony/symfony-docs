.. index::
    single: Routing; Importing routing resources

How to Include External Routing Resources
=========================================

Simple applications can define all their routes in a single configuration file -
usually ``app/config/routing.yml`` (see :ref:`routing-creating-routes`).
However, in most applications it's common to import routes definitions from
different resources: PHP annotations in controller files, YAML or XML files
stored in some directory, etc.

This can be done by importing routing resources from the main routing file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        app_file:
            # loads routes from the given routing file stored in some bundle
            resource: '@AcmeOtherBundle/Resources/config/routing.yml'

        app_annotations:
            # loads routes from the PHP annotations of the controllers found in that directory
            resource: '@AppBundle/Controller/'
            type:     annotation

        app_directory:
            # loads routes from the YAML or XML files found in that directory
            resource: '../legacy/routing/'
            type:     directory

        app_bundle:
            # loads routes from the YAML or XML files found in some bundle directory
            resource: '@AppBundle/Resources/config/routing/public/'
            type:     directory

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- loads routes from the given routing file stored in some bundle -->
            <import resource="@AcmeOtherBundle/Resources/config/routing.yml" />

            <!-- loads routes from the PHP annotations of the controllers found in that directory -->
            <import resource="@AppBundle/Controller/" type="annotation" />

            <!-- loads routes from the YAML or XML files found in that directory -->
            <import resource="../legacy/routing/" type="directory" />

            <!-- loads routes from the YAML or XML files found in some bundle directory -->
            <import resource="@AppBundle/Resources/config/routing/public/" type="directory" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $routes = new RouteCollection();
        $routes->addCollection(
            // loads routes from the given routing file stored in some bundle
            $loader->import("@AcmeOtherBundle/Resources/config/routing.yml")

            // loads routes from the PHP annotations of the controllers found in that directory
            $loader->import("@AppBundle/Controller/", "annotation")

            // loads routes from the YAML or XML files found in that directory
            $loader->import("../legacy/routing/", "directory")

            // loads routes from the YAML or XML files found in some bundle directory
            $loader->import("@AppBundle/Resources/config/routing/public/", "directory")
        );

        return $routes;

.. note::

    When importing resources from YAML, the key (e.g. ``app_file``) is meaningless.
    Just be sure that it's unique so no other lines override it.

.. _prefixing-imported-routes:

Prefixing the URLs of Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to provide a "prefix" for the imported routes. For example,
suppose you want to prefix all routes in the AppBundle with ``/site`` (e.g.
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

        # app/config/routing.yml
        app:
            resource: '@AppBundle/Controller/'
            type:     annotation
            prefix:   /site

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <import
                resource="@AppBundle/Controller/"
                type="annotation"
                prefix="/site" />
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $app = $loader->import('@AppBundle/Controller/', 'annotation');
        $app->addPrefix('/site');

        $routes = new RouteCollection();
        $routes->addCollection($app);

        return $routes;

The path of each route being loaded from the new routing resource will now
be prefixed with the string ``/site``.

Prefixing the Names of Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 3.4
    The feature to prefix route names was introduced in Symfony 3.4.

You also have the possibility to prefix all route names defined in a controller
class with the ``name`` attribute of the ``@Route`` annotation::

    use Symfony\Component\Routing\Annotation\Route;

    /**
     * @Route(name="blog_")
     */
    class BlogController extends Controller
    {
        /**
         * @Route("/blog", name="index")
         */
        public function indexAction()
        {
            // ...
        }

        /**
         * @Route("/blog/posts/{slug}", name="post")
         */
        public function showAction(Post $post)
        {
            // ...
        }
    }

In this example, the names of the routes will be ``blog_index`` and ``blog_post``.

Adding a Host Requirement to Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can set the host regex on imported routes. For more information, see
:ref:`component-routing-host-imported`.
