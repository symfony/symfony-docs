.. index::
    single: Routing; Requirements

How to Define Route Requirements
================================

:ref:`Route requirements <routing-requirements>` can be used to make a specific route
*only* match under specific conditions. The simplest example involves restricting
a routing ``{wildcard}`` to only match some regular expression:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            /**
             * @Route("/blog/{page}", name="blog_list", requirements={"page"="\d+"})
             */
            public function list($page)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path:      /blog/{page}
            controller: App\Controller\BlogController::list
            requirements:
                page: '\d+'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page}" controller="App\Controller\BlogController::list">
                <requirement key="page">\d+</requirement>
            </route>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogController;

        return function (RoutingConfigurator $routes) {
            $routes->add('blog_list', '/blog/{page}')
                ->controller([BlogController::class, 'list'])
                ->requirements([
                    'page' => '\d+',
                ])
            ;
            // ...
        };

Thanks to the ``\d+`` requirement (i.e. a "digit" of any length), ``/blog/2`` will
match this route but ``/blog/some-string`` will *not* match.

.. sidebar:: Earlier Routes Always Win

    Why would you ever care about requirements? If a request matches *two* routes,
    then the first route always wins. By adding requirements to the first route,
    you can make each route match in just the right situations. See :ref:`routing-requirements`
    for an example.

Since the parameter requirements are regular expressions, the complexity
and flexibility of each requirement is entirely up to you. Suppose the homepage
of your application is available in two different languages, based on the
URL:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/MainController.php

        // ...
        class MainController extends AbstractController
        {
            /**
             * @Route("/{_locale}", defaults={"_locale"="en"}, requirements={
             *     "_locale"="en|fr"
             * })
             */
            public function homepage($_locale)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        homepage:
            path:       /{_locale}
            controller: App\Controller\MainController::homepage
            defaults:   { _locale: en }
            requirements:
                _locale:  en|fr

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="homepage" path="/{_locale}" controller="App\Controller\MainController::homepage">
                <default key="_locale">en</default>
                <requirement key="_locale">en|fr</requirement>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\MainController;

        return function (RoutingConfigurator $routes) {
            $routes->add('homepage', '/{_locale}')
                ->controller([MainController::class, 'homepage'])
                ->defaults([
                    '_locale' => 'en',
                ])
                ->requirements([
                    '_locale' => 'en|fr',
                ])
            ;
        };

For incoming requests, the ``{_locale}`` portion of the URL is matched against
the regular expression ``(en|fr)``.

=======  ========================
Path     Parameters
=======  ========================
``/``    ``{_locale}`` = ``"en"``
``/en``  ``{_locale}`` = ``"en"``
``/fr``  ``{_locale}`` = ``"fr"``
``/es``  *won't match this route*
=======  ========================

.. note::

    You can enable UTF-8 route matching by setting the ``utf8`` option when
    declaring or importing routes. This will make e.g. a ``.`` in requirements
    match any UTF-8 characters instead of just a single byte.

.. tip::

    The route requirements can also include container parameters, as explained
    in :doc:`this article </routing/service_container_parameters>`.
    This comes in handy when the regular expression is very complex and used
    repeatedly in your application.

.. index::
    single: Routing; Method requirement

.. _routing-method-requirement:

Adding HTTP Method Requirements
-------------------------------

In addition to the URL, you can also match on the *method* of the incoming
request (i.e. GET, HEAD, POST, PUT, DELETE). Suppose you create an API for
your blog and you have 2 routes: One for displaying a post (on a GET or HEAD
request) and one for updating a post (on a PUT request). This can be
accomplished with the following route configuration:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Controller/BlogApiController.php
        namespace App\Controller;

        // ...

        class BlogApiController extends AbstractController
        {
            /**
             * @Route("/api/posts/{id}", methods={"GET","HEAD"})
             */
            public function show($id)
            {
                // ... return a JSON response with the post
            }

            /**
             * @Route("/api/posts/{id}", methods={"PUT"})
             */
            public function edit($id)
            {
                // ... edit a post
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        api_post_show:
            path:       /api/posts/{id}
            controller: App\Controller\BlogApiController::show
            methods:    GET|HEAD

        api_post_edit:
            path:       /api/posts/{id}
            controller: App\Controller\BlogApiController::edit
            methods:    PUT

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="api_post_show"
                path="/api/posts/{id}"
                controller="App\Controller\BlogApiController::show"
                methods="GET|HEAD"/>

            <route id="api_post_edit"
                path="/api/posts/{id}"
                controller="App\Controller\BlogApiController::edit"
                methods="PUT"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        namespace Symfony\Component\Routing\Loader\Configurator;

        use App\Controller\BlogApiController;

        return function (RoutingConfigurator $routes) {
            $routes->add('api_post_show', '/api/posts/{id}')
                ->controller([BlogApiController::class, 'show'])
                ->methods(['GET', 'HEAD'])
            ;
            $routes->add('api_post_edit', '/api/posts/{id}')
                ->controller([BlogApiController::class, 'edit'])
                ->methods(['PUT'])
            ;

            // or use collection
            $api = $routes->collection('api_post_')
                ->prefix('/api/posts/{id}')
            ;
            $api->add('show')
                ->controller([BlogApiController::class, 'show'])
                ->methods(['GET', 'HEAD'])
            ;
            $api->add('edit')
                ->controller([BlogApiController::class, 'edit'])
                ->methods(['PUT'])
            ;
        };

Despite the fact that these two routes have identical paths
(``/api/posts/{id}``), the first route will match only GET or HEAD requests and
the second route will match only PUT requests. This means that you can display
and edit the post with the same URL, while using distinct controllers for the
two actions.

.. note::

    If no ``methods`` are specified, the route will match on *all* methods.

.. tip::

    If you're using HTML forms and HTTP methods *other* than ``GET`` and ``POST``,
    you'll need to include a ``_method`` parameter to *fake* the HTTP method. See
    :doc:`/form/action_method` for more information.

Adding a Host Requirement
-------------------------

You can also match on the HTTP *host* of the incoming request. For more
information, see :doc:`/routing/hostname_pattern` in the Routing
component documentation.

Adding Dynamic Requirements with Expressions
--------------------------------------------

For really complex requirements, you can use dynamic expressions to match *any*
information on the request. See :doc:`/routing/conditions`.

.. _`PCRE Unicode property`: http://php.net/manual/en/regexp.reference.unicode.php
