.. index::
    single: Routing; Importing routing resources

How to Include External Routing Resources
=========================================

All routes are loaded via a single configuration file - usually ``app/config/routing.yml``
(see `Creating Routes`_ above). However, if you use routing annotations,
you'll need to point the router to the controllers with the annotations.
This can be done by "importing" directories into the routing configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/routing.yml
        app:
            resource: '@AppBundle/Controller/'
            type:     annotation # required to enable the Annotation reader for this resource

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <!-- the type is required to enable the annotation reader for this resource -->
            <import resource="@AppBundle/Controller/" type="annotation"/>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->addCollection(
            // second argument is the type, which is required to enable
            // the annotation reader for this resource
            $loader->import("@AppBundle/Controller/", "annotation")
        );

        return $collection;

.. note::

   When importing resources from YAML, the key (e.g. ``app``) is meaningless.
   Just be sure that it's unique so no other lines override it.

The ``resource`` key loads the given routing resource. In this example the
resource is a directory, where the ``@AppBundle`` shortcut syntax resolves
to the full path of the AppBundle. When pointing to a directory, all files
in that directory are parsed and put into the routing.

.. note::

    You can also include other routing configuration files, this is often
    used to import the routing of third party bundles:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/routing.yml
            app:
                resource: '@AcmeOtherBundle/Resources/config/routing.yml'

        .. code-block:: xml

            <!-- app/config/routing.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <routes xmlns="http://symfony.com/schema/routing"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/routing
                    http://symfony.com/schema/routing/routing-1.0.xsd">

                <import resource="@AcmeOtherBundle/Resources/config/routing.xml" />
            </routes>

        .. code-block:: php

            // app/config/routing.php
            use Symfony\Component\Routing\RouteCollection;

            $collection = new RouteCollection();
            $collection->addCollection(
                $loader->import("@AcmeOtherBundle/Resources/config/routing.php")
            );

            return $collection;

Prefixing Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~

You can also choose to provide a "prefix" for the imported routes. For example,
suppose you want to prefix all routes in the AppBundle with ``/site`` (e.g.
``/site/blog/{slug}`` instead of ``/blog/{slug}``):

.. configuration-block::

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

        $collection = new RouteCollection();
        $collection->addCollection($app);

        return $collection;

The path of each route being loaded from the new routing resource will now
be prefixed with the string ``/site``.

Adding a Host Requirement to Imported Routes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can set the host regex on imported routes. For more information, see
:ref:`component-routing-host-imported`.
