Routing Configuration Reference
===============================

This reference describes all the options available to define
:doc:`routes </routing>` with PHP attributes and in the YAML, XML and PHP
routing files (e.g. ``config/routes.yaml``).

.. seealso::

    The options of the router itself (``default_uri``, ``strict_requirements``,
    ``utf8``, etc.) are defined under the ``framework.router`` key and are
    explained in the
    :doc:`FrameworkBundle configuration reference </reference/configuration/framework>`.

.. deprecated:: 7.4

    The XML format to configure routes is deprecated in Symfony 7.4.

.. _reference-routing-route-definition:

Route Options
~~~~~~~~~~~~~

Each route has a unique name: the ``name`` argument of the ``#[Route]``
attribute, the key of the route in YAML, the ``id`` attribute in XML or the
first argument of the ``add()`` method in PHP. When a ``#[Route]`` attribute
doesn't define a name, Symfony generates one from the controller class and
method names.

.. _reference-routing-alias:

alias
.....

**type**: ``string`` | ``array`` | ``DeprecatedAlias``

Defines another name for an existing route. In YAML, XML and PHP files, the
alias is a separate route definition whose only options are ``alias`` and
`deprecated`_. In PHP attributes, the ``alias`` argument lists the alternative
names of the route. Read more about :ref:`route aliasing <routing-alias>`.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/ProductController.php
        #[Route('/product/{id}', name: 'product_show', alias: ['product_details'])]
        public function show(int $id): Response
        {
            // ...
        }

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

.. _reference-routing-condition:

condition
.........

**type**: ``string`` **default**: ``null``

An :doc:`expression </reference/formats/expression_language>` that must
return ``true`` for the route to match. Conditions are ignored when generating
URLs. Read more about the variables and functions available in
:ref:`matching expressions <routing-matching-expressions>`.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        #[Route('/posts/{id}', name: 'post_show', condition: "params['id'] < 1000")]
        public function show(int $id): Response
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        post_show:
            path: /posts/{id}
            controller: App\Controller\BlogController::show
            condition: "params['id'] < 1000"

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="post_show" path="/posts/{id}"
            controller="App\Controller\BlogController::show">
            <condition>params['id'] &lt; 1000</condition>
        </route>

    .. code-block:: php

        // config/routes.php
        $routes->add('post_show', '/posts/{id}')
            ->controller([BlogController::class, 'show'])
            ->condition("params['id'] < 1000");

.. _reference-routing-controller:

controller
..........

**type**: ``string`` | ``array``

The controller to execute when the route matches. It's a shortcut for the
``_controller`` value of the `defaults`_ option. PHP attributes don't define
this option because the controller is the method (or the invokable class)
where the attribute is applied.

Use the ``controller_class::method_name`` syntax, or only ``controller_class``
if the controller defines an ``__invoke()`` method. In PHP files, you can also
use an array such as ``[BlogController::class, 'show']``.

.. _reference-routing-defaults:

defaults
........

**type**: ``array`` **default**: ``[]``

The default values of the :ref:`route parameters <routing-route-parameters>`.
It can also define extra parameters that are not part of the path and the
special parameters such as ``_controller``, ``_format`` or ``_locale``
(use the `controller`_, `format`_ and `locale`_ options instead).

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        #[Route(
            '/blog/{page}',
            name: 'blog_list',
            defaults: ['page' => 1, 'title' => 'Hello world!'],
        )]
        public function list(int $page, string $title): Response
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_list:
            path: /blog/{page}
            controller: App\Controller\BlogController::list
            defaults:
                page: 1
                title: 'Hello world!'

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
                'page' => 1,
                'title' => 'Hello world!',
            ]);

.. _reference-routing-deprecated:

deprecated
..........

**type**: ``array``

Marks a route alias as deprecated, so Symfony triggers a deprecation when
using it. This option is only allowed in alias definitions and it accepts
these keys:

``package`` (**required**)
    The name of the package that deprecated the alias (e.g. ``acme/package``).
``version`` (**required**)
    The version of the package that deprecated the alias (e.g. ``1.2``).
``message``
    A custom deprecation message. It must include the ``%alias_id%``
    placeholder, which is replaced by the name of the alias.

In PHP attributes, pass a ``DeprecatedAlias`` object to the `alias`_ option.
Read more about :ref:`deprecating route aliases <routing-alias-deprecation>`.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/ProductController.php
        namespace App\Controller;

        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Attribute\DeprecatedAlias;
        use Symfony\Component\Routing\Attribute\Route;

        class ProductController
        {
            // arguments: alias name, package, version and (optionally) message
            #[Route(
                '/product/{id}',
                name: 'product_details',
                alias: new DeprecatedAlias('product_show', 'acme/package', '1.2'),
            )]
            public function show(int $id): Response
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        product_show:
            alias: product_details
            deprecated:
                package: 'acme/package'
                version: '1.2'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <route id="product_show" alias="product_details">
            <deprecated package="acme/package" version="1.2"/>
        </route>

    .. code-block:: php

        // config/routes.php
        $routes->alias('product_show', 'product_details')
            // the third argument is the message ('' uses the default one)
            ->deprecate('acme/package', '1.2', '');

.. _reference-routing-env:

env
...

**type**: ``string`` | ``array`` **default**: ``null``

Registers the route only in the given
:ref:`configuration environments <configuration-environments>`. This option
only exists in PHP attributes. In the other formats, wrap the routes with
``when@<env>`` (YAML) or ``<when env="...">`` (XML), or check the value of
``$routes->env()`` (PHP).

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/DefaultController.php
        #[Route('/tools', name: 'tools', env: ['dev', 'test'])]
        public function developerTools(): Response
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        when@dev:
            tools:
                path: /tools
                controller: App\Controller\DefaultController::developerTools

    .. code-block:: xml

        <!-- config/routes.xml -->
        <when env="dev">
            <route id="tools" path="/tools"
                controller="App\Controller\DefaultController::developerTools"/>
        </when>

    .. code-block:: php

        // config/routes.php
        if ('dev' === $routes->env()) {
            $routes->add('tools', '/tools')
                ->controller([DefaultController::class, 'developerTools']);
        }

.. versionadded:: 7.4

    The ability to pass an array of environments to the ``env`` option was
    introduced in Symfony 7.4.

.. _reference-routing-format:

format
......

**type**: ``string`` **default**: ``null``

A shortcut for the ``_format`` value of the `defaults`_ option. It sets the
:ref:`request format <routing-format-parameter>`, which is used, for
example, to set the ``Content-Type`` of the response.

.. _reference-routing-host:

host
....

**type**: ``string`` | ``array`` **default**: ``null``

The HTTP host that the route must match. It can include
:ref:`route parameters <routing-route-parameters>` (e.g.
``{subdomain}.example.com``). Pass an array to define a different host per
locale (this isn't supported in PHP attributes).

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/MainController.php
        #[Route('/', name: 'mobile_homepage', host: 'm.example.com')]
        public function mobileHomepage(): Response
        {
            // ...
        }

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

.. _reference-routing-locale:

locale
......

**type**: ``string`` **default**: ``null``

A shortcut for the ``_locale`` value of the `defaults`_ option. It sets the
:ref:`locale <routing-locale-parameter>` of the request.

.. _reference-routing-methods:

methods
.......

**type**: ``string`` | ``array`` **default**: ``[]``

The HTTP methods that the route responds to. When empty, the route matches
any method. Read more about
:ref:`matching HTTP methods <routing-matching-http-methods>`.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogApiController.php
        #[Route('/api/posts/{id}', name: 'api_post_show', methods: ['GET', 'HEAD'])]
        public function show(int $id): Response
        {
            // ...
        }

    .. code-block:: yaml

        # config/routes.yaml
        api_post_show:
            path: /api/posts/{id}
            controller: App\Controller\BlogApiController::show
            methods: [GET, HEAD]

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

.. _reference-routing-options:

options
.......

**type**: ``array`` **default**: ``[]``

Low-level options of the route. The built-in ones are ``compiler_class`` (the
class that compiles the route, which is
:class:`Symfony\\Component\\Routing\\RouteCompiler` by default) and ``utf8``
(set it with the `utf8`_ option instead).

.. _reference-routing-path:

path
....

**type**: ``string`` | ``array`` **required**

The URL path that the route matches. It can include
:ref:`route parameters <routing-route-parameters>` wrapped in curly braces
(e.g. ``/blog/{slug}``), with optional inline
:ref:`requirements <routing-requirements>` and default values (e.g.
``/blog/{page<\d+>?1}``). Pass an array to define a different path per locale
and create :ref:`localized routes <i18n-routing>`.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Attribute\Route;

        class BlogController extends AbstractController
        {
            #[Route('/blog/{slug}', name: 'blog_show')]
            public function show(string $slug): Response
            {
                // ...
            }
        }

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

        return static function (RoutingConfigurator $routes): void {
            $routes->add('blog_show', '/blog/{slug}')
                ->controller([BlogController::class, 'show']);
        };

.. _reference-routing-priority:

priority
........

**type**: ``integer`` **default**: ``0``

Symfony evaluates routes with a higher priority before routes with a lower
priority. This option only exists in PHP attributes. In the other formats,
routes are evaluated in the order in which they are defined::

    // src/Controller/BlogController.php
    // without the priority, the 'blog_show' route defined before this one
    // (with the '/blog/{slug}' path) would match the '/blog/list' URL
    #[Route('/blog/list', name: 'blog_list', priority: 2)]
    public function list(): Response
    {
        // ...
    }

.. _reference-routing-requirements:

requirements
............

**type**: ``array`` **default**: ``[]``

The `PHP regular expressions`_ that route parameters (in the path and in the
host) must match. The
:class:`Symfony\\Component\\Routing\\Requirement\\Requirement` enum defines
constants for the most common requirements (digits, UUIDs, etc.). Read more
about :ref:`parameters validation <routing-requirements>`.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        #[Route('/blog/{page}', name: 'blog_list', requirements: ['page' => '\d+'])]
        public function list(int $page): Response
        {
            // ...
        }

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

.. _reference-routing-schemes:

schemes
.......

**type**: ``string`` | ``array`` **default**: ``[]``

The URL schemes (e.g. ``https``) that the route responds to. When empty, the
route matches any scheme. When the current request uses a different scheme,
the generated URLs of this route are absolute and use the right scheme. Read
more about :ref:`forcing HTTPS <routing-force-https>`.

.. _reference-routing-stateless:

stateless
.........

**type**: ``boolean`` **default**: ``false``

When ``true``, using the session during the request throws an exception
(if ``kernel.debug`` is enabled) or logs a warning (otherwise). Read more
about :ref:`stateless routes <stateless-routing>`.

.. _reference-routing-utf8:

utf8
....

**type**: ``boolean`` **default**: ``framework.router.utf8``

A shortcut for the ``utf8`` value of the `options`_ option. When ``true``,
route parameters and requirements match UTF-8 characters instead of single
bytes.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/ProductController.php
        #[Route('/products/{name}', name: 'product_show', utf8: true)]
        public function show(string $name): Response
        {
            // ...
        }

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
            ->utf8();

.. _reference-routing-imports:

Route Import Options
~~~~~~~~~~~~~~~~~~~~

Imports load the routes defined in another resource (a file, a directory of
controllers with PHP attributes, etc.) and apply some common configuration to
all of them. In PHP attributes, the equivalent is to add a ``#[Route]``
attribute to the controller class. Read more about
:ref:`route groups and prefixes <routing-route-groups>`.

In addition to the options explained below, imports accept these route
options: `condition`_, `controller`_, `defaults`_, `format`_, `host`_,
`locale`_, `methods`_, `options`_, `requirements`_, `schemes`_, `stateless`_
and `utf8`_. Their values are applied to every imported route and replace the
values that those routes define for the same options (or for the same keys, in
the case of ``defaults``, ``options`` and ``requirements``).

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        controllers:
            resource: ../src/Controller/
            type: attribute
            exclude: '../src/Controller/{Debug*Controller.php}'
            prefix: /blog
            trailing_slash_on_root: false
            name_prefix: blog_
            requirements:
                _locale: 'en|es|fr'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <import resource="../src/Controller/"
                type="attribute"
                exclude="../src/Controller/{Debug*Controller.php}"
                prefix="/blog"
                trailing-slash-on-root="false"
                name-prefix="blog_">
                <requirement key="_locale">en|es|fr</requirement>
            </import>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return static function (RoutingConfigurator $routes): void {
            // the arguments are: resource, type, ignore errors and exclude
            $routes->import(
                '../src/Controller/',
                'attribute',
                false,
                '../src/Controller/{Debug*Controller.php}'
            )
                // the second argument is the 'trailing_slash_on_root' option
                ->prefix('/blog', false)
                ->namePrefix('blog_')
                ->requirements(['_locale' => 'en|es|fr']);
        };

.. _reference-routing-exclude:

exclude
.......

**type**: ``string`` | ``array`` **default**: ``null``

One or more glob patterns of the files to ignore when importing the
`resource`_.

.. _reference-routing-name-prefix:

name_prefix
...........

**type**: ``string`` **default**: ``null``

A string added at the beginning of the name of all imported routes. It's
called ``name-prefix`` in XML and it's set with the ``namePrefix()`` method
in PHP.

.. _reference-routing-prefix:

prefix
......

**type**: ``string`` | ``array`` **default**: ``''``

A string added at the beginning of the path of all imported routes. Pass an
array to define a different prefix per locale and create
:ref:`localized routes <i18n-routing>`.

.. _reference-routing-resource:

resource
........

**type**: ``string`` **required**

The resource to import: the path of a file or directory, a glob pattern or
any other value supported by the route loaders.

.. _reference-routing-trailing-slash-on-root:

trailing_slash_on_root
......................

**type**: ``boolean`` **default**: ``true``

When the path of an imported route is ``/``, adding the `prefix`_ to it
results in a trailing slash (e.g. ``/blog/``). Set this option to ``false``
to remove that trailing slash (e.g. ``/blog``). It's called
``trailing-slash-on-root`` in XML and it's set with the second argument of
the ``prefix()`` method in PHP.

.. _reference-routing-type:

type
....

**type**: ``string`` **default**: ``null``

The type of the imported `resource`_. It's only needed when Symfony can't
guess the loader from the resource (e.g. ``attribute`` to load the PHP
attributes of controller classes).

.. _`PHP regular expressions`: https://php.net/manual/book.pcre
