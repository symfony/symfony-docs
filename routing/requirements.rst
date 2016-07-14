.. index::
    single: Routing; Requirements

How to Define Route Requirements
================================

:ref:`Route requirements <routing-requirements>` can be used to make a specific route
*only* match under specific conditions. The simplest example involves restricting
a routing ``{wildcard}`` to only match some regular expression:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/BlogController.php
        namespace AppBundle\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\Controller;
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class BlogController extends Controller
        {
            /**
             * @Route("/blog/{page}", name="blog_list", requirements={"page": "\d+"})
             */
            public function listAction($page)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        blog_list:
            path:      /blog/{page}
            defaults:  { _controller: AppBundle:Blog:list }
            requirements:
                page: '\d+'

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_list" path="/blog/{page}">
                <default key="_controller">AppBundle:Blog:list</default>
                <requirement key="page">\d+</requirement>
            </route>

            <!-- ... -->
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog_list', new Route('/blog/{page}', array(
            '_controller' => 'AppBundle:Blog:list',
        ), array(
            'page' => '\d+'
        )));

        // ...

        return $collection;

Thanks to the ``\d+`` requirement (i.e. a "digit" of any length), ``/blog/2`` will
match this route but ``/blog/some-string`` will *not* match.

.. sidebar:: Earlier Routes always Win

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

        // src/AppBundle/Controller/MainController.php

        // ...
        class MainController extends Controller
        {
            /**
             * @Route("/{_locale}", defaults={"_locale": "en"}, requirements={
             *     "_locale": "en|fr"
             * })
             */
            public function homepageAction($_locale)
            {
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        homepage:
            path:      /{_locale}
            defaults:  { _controller: AppBundle:Main:homepage, _locale: en }
            requirements:
                _locale:  en|fr

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="homepage" path="/{_locale}">
                <default key="_controller">AppBundle:Main:homepage</default>
                <default key="_locale">en</default>
                <requirement key="_locale">en|fr</requirement>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/{_locale}', array(
            '_controller' => 'AppBundle:Main:homepage',
            '_locale'     => 'en',
        ), array(
            '_locale' => 'en|fr',
        )));

        return $collection;

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

        // src/AppBundle/Controller/MainController.php
        namespace AppBundle\Controller;

        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
        // ...

        class BlogApiController extends Controller
        {
            /**
             * @Route("/api/posts/{id}")
             * @Method({"GET","HEAD"})
             */
            public function showAction($id)
            {
                // ... return a JSON response with the post
            }

            /**
             * @Route("/api/posts/{id}")
             * @Method("PUT")
             */
            public function editAction($id)
            {
                // ... edit a post
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        api_post_show:
            path:     /api/posts/{id}
            defaults: { _controller: AppBundle:BlogApi:show }
            methods:  [GET, HEAD]

        api_post_edit:
            path:     /api/posts/{id}
            defaults: { _controller: AppBundle:BlogApi:edit }
            methods:  [PUT]

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="api_post_show" path="/api/posts/{id}" methods="GET|HEAD">
                <default key="_controller">AppBundle:BlogApi:show</default>
            </route>

            <route id="api_post_edit" path="/api/posts/{id}" methods="PUT">
                <default key="_controller">AppBundle:BlogApi:edit</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('api_post_show', new Route('/api/posts/{id}', array(
            '_controller' => 'AppBundle:BlogApi:show',
        ), array(), array(), '', array(), array('GET', 'HEAD')));

        $collection->add('api_post_edit', new Route('/api/posts/{id}', array(
            '_controller' => 'AppBundle:BlogApi:edit',
        ), array(), array(), '', array(), array('PUT')));

        return $collection;

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
