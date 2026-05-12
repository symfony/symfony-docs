Routing Configuration Reference
===============================

This reference describes all the keys and options available when defining
:doc:`routes </routing>` in your application using the YAML, XML or PHP
configuration formats (``config/routes.yaml``, ``config/routes.xml``,
``config/routes.php``), as well as their equivalents when using the
``#[Route]`` PHP attribute on controllers.

.. note::

    This page documents the keys used to define **your application's routes**.
    The configuration of the routing component itself (for example
    ``default_uri``, ``utf8``, ``strict_requirements`` and other options under
    the ``framework.router`` key) is documented under the ``router`` section
    of the :doc:`Framework Configuration Reference </reference/configuration/framework>`.

.. deprecated:: 7.4

    The XML format to configure routes is deprecated in Symfony 7.4 and will
    be removed in Symfony 8.0.

.. _reference-routing-route-definition:

Route Definition Keys
---------------------

A route is defined by a unique name (the key in YAML, the ``id`` attribute
in XML, or the first argument of the ``add()`` method in PHP) and the
following options.

.. _reference-routing-path:

path
~~~~

**type**: ``string`` | ``array``

The URL pattern matched by the route. It can include parameters enclosed in
curly braces (e.g. ``/blog/{slug}``) that are passed to the associated
controller. It can also be an array of paths to define a
:ref:`localized route <i18n-routing>` (one path per locale).

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        blog_show:
            path: /blog/{slug}
            controller: App\Controller\BlogController::show

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_show" path="/blog/{slug}"
                   controller="App\Controller\BlogController::show"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes): void {
            $routes->add('blog_show', '/blog/{slug}')
                ->controller([BlogController::class, 'show']);
        };

Read more about :ref:`route parameters <routing-route-parameters>` and
:ref:`inline requirements/defaults <routing-requirements>`.

controller
~~~~~~~~~~

**type**: ``string`` | ``array``

The controller (and optionally the action) to execute when the route
matches. It is stored in the ``_controller`` :ref:`default <reference-routing-defaults>`.

The value uses the ``controller_class::method_name`` syntax, or just
``controller_class`` if the controller defines the ``__invoke()`` method.
In the PHP format, an array such as ``[BlogController::class, 'show']``
is also accepted.

methods
~~~~~~~

**type**: ``string`` | ``array``

The HTTP methods the route responds to. If not defined, the route matches
any HTTP method. Read more about :ref:`matching HTTP methods <routing-matching-http-methods>`.

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        api_post_show:
            path: /api/posts/{id}
            controller: App\Controller\BlogApiController::show
            methods: GET|HEAD

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="api_post_show" path="/api/posts/{id}"
            controller="App\Controller\BlogApiController::show"
            methods="GET|HEAD"/>

    .. code-block:: php

        // config/routes.php
        $routes->add('api_post_show', '/api/posts/{id}')
            ->controller([BlogApiController::class, 'show'])
            ->methods(['GET', 'HEAD']);

schemes
~~~~~~~

**type**: ``string`` | ``array``

The URL schemes (e.g. ``https``) the route responds to. If not defined,
the route matches any scheme. If the generated URL uses a different scheme
than the current request, the route generator produces an absolute URL
instead of a relative one. Read more about :ref:`forcing HTTPS <routing-force-https>`.

host
~~~~

**type**: ``string`` | ``array``

Restricts the route to match a specific HTTP host. Host values can include
:ref:`parameters <routing-route-parameters>` (for multi-tenant applications)
and an array of hosts can be provided to define a different host per
locale. Read more about :doc:`sub-domain routing </routing>`.

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        mobile_homepage:
            path: /
            host: m.example.com
            controller: App\Controller\MainController::mobileHomepage

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="mobile_homepage" path="/" host="m.example.com"
            controller="App\Controller\MainController::mobileHomepage"/>

    .. code-block:: php

        // config/routes.php
        $routes->add('mobile_homepage', '/')
            ->controller([MainController::class, 'mobileHomepage'])
            ->host('m.example.com');

.. _reference-routing-requirements:

requirements
~~~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

Defines the `PHP regular expressions`_ that route parameters (both in the
path and the host) must match. Requirements can also be inlined with the
``{parameter<regexp>}`` syntax. Read more about
:ref:`parameters validation <routing-requirements>`.

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path: /blog/{page}
            controller: App\Controller\BlogController::list
            requirements:
                page: '\d+'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="blog_list" path="/blog/{page}"
            controller="App\Controller\BlogController::list">
            <requirement key="page">\d+</requirement>
        </route>

    .. code-block:: php

        // config/routes.php
        $routes->add('blog_list', '/blog/{page}')
            ->controller([BlogController::class, 'list'])
            ->requirements(['page' => '\d+']);

.. tip::

    The :class:`Symfony\\Component\\Routing\\Requirement\\Requirement` enum
    contains a collection of commonly used regular-expression constants
    (digits, UUIDs, etc.).

.. _reference-routing-defaults:

defaults
~~~~~~~~

**type**: ``array`` **default**: ``[]``

Default values applied to route parameters. They also allow passing
:ref:`extra parameters <routing-route-parameters>` to the controller
that are not part of the path. This is also where special parameters
such as ``_controller``, ``_format``, ``_locale``, ``_fragment``, ``_query``
are stored when not set with the dedicated options described below.

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path: /blog/{page}
            controller: App\Controller\BlogController::list
            defaults:
                page: 1
                title: "Hello world!"

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="blog_list" path="/blog/{page}"
            controller="App\Controller\BlogController::list">
            <default key="page">1</default>
            <default key="title">Hello world!</default>
        </route>

    .. code-block:: php

        // config/routes.php
        $routes->add('blog_list', '/blog/{page}')
            ->controller([BlogController::class, 'list'])
            ->defaults([
                'page'  => 1,
                'title' => 'Hello world!',
            ]);

condition
~~~~~~~~~

**type**: ``string`` **default**: ``null``

An :doc:`expression </reference/formats/expression_language>` evaluated at
match time. The route only matches when the expression returns ``true``.
Conditions are **not** taken into account when generating URLs. The
expression can use the ``context``, ``request`` and ``params`` variables
as well as the ``env()`` and ``service()`` functions. Read more about
:ref:`matching expressions <routing-matching-expressions>`.

locale
~~~~~~

**type**: ``string``

Shortcut for setting the ``_locale`` :ref:`default <reference-routing-defaults>`.

format
~~~~~~

**type**: ``string``

Shortcut for setting the ``_format`` :ref:`default <reference-routing-defaults>`.
Controls the request format (and usually the ``Content-Type`` of the
response).

stateless
~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

When set to ``true``, Symfony reports the use of the HTTP session during
the request. Read more about :ref:`stateless routes <stateless-routing>`.

utf8
~~~~

**type**: ``boolean`` **default**: ``false`` (or the value of
``framework.router.utf8`` when set)

Top-level shortcut for the ``utf8`` route option. When set to ``true``, route
parameters match Unicode characters instead of just ASCII.

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        product_show:
            path: /products/{name}
            controller: App\Controller\ProductController::show
            utf8: true

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="product_show" path="/products/{name}"
            controller="App\Controller\ProductController::show" utf8="true"/>

    .. code-block:: php

        // config/routes.php
        $routes->add('product_show', '/products/{name}')
            ->controller([ProductController::class, 'show'])
            ->utf8(true);

env
~~~

**type**: ``string`` | ``array``

Restricts the route to the given :ref:`configuration environment(s) <configuration-environments>`.
The route is only registered when the current environment matches one of
the given values. In YAML, this option is typically expressed using the
``when@env`` syntax.

.. versionadded:: 7.4

    The ability to pass an array of environments to the ``env`` argument
    was introduced in Symfony 7.4.

priority
~~~~~~~~

**type**: ``integer`` **default**: ``0``

Routes with a higher ``priority`` value are evaluated before routes with
a lower value.

options
~~~~~~~

**type**: ``array``

Low-level options passed to the compiled route, such as ``utf8`` (which
overrides the global ``framework.router.utf8`` setting for a specific
route). See the
:doc:`Framework Configuration Reference </reference/configuration/framework>`
for details.

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        some_route:
            path: /{slug}
            controller: App\Controller\DefaultController::show
            options:
                utf8: true

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="some_route" path="/{slug}"
            controller="App\Controller\DefaultController::show">
            <option key="utf8">true</option>
        </route>

    .. code-block:: php

        // config/routes.php
        $routes->add('some_route', '/{slug}')
            ->controller([DefaultController::class, 'show'])
            ->options(['utf8' => true]);

alias
~~~~~

**type**: ``string`` (standalone route) | ``array`` (``#[Route]`` attribute)

Declares an alternate name for a route. Using the standalone YAML/XML/PHP
syntax, define a new route whose only property is ``alias`` pointing to
the existing route name. In the ``#[Route]`` PHP attribute, pass a list of
alias names. Read more about :ref:`route aliasing <routing-alias>`.

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        product_details:
            alias: product_show

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="product_details" alias="product_show"/>

    .. code-block:: php

        // config/routes.php
        $routes->alias('product_details', 'product_show');

deprecated
~~~~~~~~~~

**type**: ``array``

This option is only available on alias routes (defined together with the
``alias`` key) and marks the alias as deprecated. Available sub-keys:

``package``
    **type**: ``string`` **required**

    The Composer package that triggers the deprecation.

``version``
    **type**: ``string`` **required**

    The version of the package since which the alias is deprecated.

``message``
    **type**: ``string``

    A custom deprecation message. Must contain the ``%alias_id%``
    placeholder, which is replaced with the deprecated alias name.

Read more about :ref:`deprecating route aliases <routing-alias-deprecation>`.

.. _reference-routing-imports:

Route Imports
-------------

Instead of defining every route individually, you can import routes from
another resource (a file, a directory containing PHP attributes, etc.).
Imports also share configuration between routes: all options defined on
the import are applied to every imported route.

resource
~~~~~~~~

**type**: ``string`` **required**

The resource to import (the path to a file, a directory, or any string
the matching loader can resolve).

type
~~~~

**type**: ``string``

The type of the imported resource. It is only needed when the loader
cannot be inferred from the file extension. Common values are ``attribute``
(to load PHP attributes from controller classes), ``yaml``, ``xml`` and
``php``.

prefix
~~~~~~

**type**: ``string`` | ``array``

A string prepended to the path of every imported route. When an array is
given, the prefix is applied per locale to create
:ref:`localized routes <i18n-routing>`.

name_prefix
~~~~~~~~~~~

**type**: ``string``

A string prepended to the name of every imported route. In the XML format,
the attribute is spelled ``name-prefix``. In PHP, use the ``namePrefix()``
method.

trailing_slash_on_root
~~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

When the imported route has an empty path and a prefix is defined, Symfony
adds a trailing slash (for example ``/blog/``). Set this option to
``false`` to avoid the trailing slash (``/blog``). In the XML format, the
attribute is spelled ``trailing-slash-on-root``.

exclude
~~~~~~~

**type**: ``string`` | ``array``

One or more PHP glob patterns identifying files or subdirectories to
exclude from the import. This option only works when the ``resource``
value is itself a glob string.

Shared route options on imports
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All the options documented in the
:ref:`Route Definition Keys <reference-routing-route-definition>` section
(``host``, ``schemes``, ``methods``, ``controller``, ``locale``, ``format``,
``stateless``, ``utf8``, ``condition``, ``defaults``, ``requirements``,
``options``, ``env``, ``priority``) can also be applied on an import. The
corresponding values are merged into every imported route (for example,
``requirements`` defined on the import are added to each imported route).

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        controllers:
            resource: ../src/Controller/
            type: attribute
            prefix: /blog
            name_prefix: blog_
            requirements:
                _locale: 'en|es|fr'
            trailing_slash_on_root: false
            exclude: '../src/Controller/{Debug*Controller.php}'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="../src/Controller/"
                type="attribute"
                prefix="/blog"
                name-prefix="blog_"
                trailing-slash-on-root="false"
                exclude="../src/Controller/{Debug*Controller.php}">
                <requirement key="_locale">en|es|fr</requirement>
            </import>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return static function (RoutingConfigurator $routes): void {
            $routes->import(
                    '../src/Controller/',
                    'attribute',
                    false,
                    '../src/Controller/{Debug*Controller.php}'
                )
                // false disables the trailing slash on root
                ->prefix('/blog', false)
                ->namePrefix('blog_')
                ->requirements(['_locale' => 'en|es|fr']);
        };

.. _reference-routing-php-attributes:

PHP Route Attribute
-------------------

When using PHP attributes, routes are declared with
:class:`Symfony\\Component\\Routing\\Attribute\\Route` on top of controller
classes and/or methods. The attribute accepts the same options as the
keys documented above, plus:

``name``
    ``string``: the name of the generated route. When omitted, Symfony
    generates a default name based on the controller FQCN and action.

``alias``
    ``array`` | ``Symfony\Component\Routing\Attribute\DeprecatedAlias``:
    alternate names for the route. Use a ``DeprecatedAlias`` instance to
    deprecate the alias. See :ref:`route aliasing <routing-alias>`.

Minimal example::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    class BlogController extends AbstractController
    {
        #[Route('/blog/{page}', name: 'blog_list', requirements: ['page' => '\d+'], methods: ['GET'])]
        public function list(int $page = 1): Response
        {
            // ...
        }
    }

Placing a ``#[Route]`` attribute on the controller **class** makes its
values (typically ``path`` prefix, ``name`` prefix, ``requirements``,
``host``, etc.) apply to every method-level ``#[Route]`` attribute declared
inside the class. Read more about :ref:`route groups and prefixes <routing-route-groups>`.

.. _`PHP regular expressions`: https://www.php.net/manual/en/book.pcre.php
