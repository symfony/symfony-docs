Creating and Using Templates
============================

A template is the best way to organize and render HTML from inside your application,
whether you need to render HTML from a :doc:`controller </controller>` or generate
the :doc:`contents of an email </mailer>`. Templates in Symfony are created with
Twig: a flexible, fast, and secure template engine.

.. _twig-language:

Twig Templating Language
------------------------

The `Twig`_ templating language allows you to write concise, readable templates
that are more friendly to web designers and, in several ways, more powerful than
PHP templates. Take a look at the following Twig template example. Even if it's
the first time you see Twig, you probably understand most of it:

.. code-block:: html+twig

    <!DOCTYPE html>
    <html>
        <head>
            <title>Welcome to Symfony!</title>
        </head>
        <body>
            <h1>{{ page_title }}</h1>

            {% if user.isLoggedIn %}
                Hello {{ user.name }}!
            {% endif %}

            {# ... #}
        </body>
    </html>

Twig syntax is based on these three constructs:

* ``{{ ... }}``, used to display the content of a variable or the result of
  evaluating an expression;
* ``{% ... %}``, used to run some logic, such as a conditional or a loop;
* ``{# ... #}``, used to add comments to the template (unlike HTML comments,
  these comments are not included in the rendered page).

You can't run PHP code inside Twig templates, but Twig provides utilities to
run some logic in the templates. For example, **filters** modify content before
being rendered, like the ``upper`` filter to uppercase contents:

.. code-block:: twig

    {{ title|upper }}

Twig comes with a long list of `tags`_, `filters`_ and `functions`_ that are
available by default. In Symfony applications you can also use these
:doc:`Twig filters and functions defined by Symfony </reference/twig_reference>`
and you can :ref:`create your own Twig filters and functions <templates-twig-extension>`.

Twig is fast in the ``prod`` :ref:`environment <configuration-environments>`
(because templates are compiled into PHP and cached automatically), but
convenient to use in the ``dev`` environment (because templates are recompiled
automatically when you change them).

Twig Configuration
~~~~~~~~~~~~~~~~~~

Twig has several configuration options to define things like the format used
to display numbers and dates, the template caching, etc. Read the
:doc:`Twig configuration reference </reference/configuration/twig>` to learn about them.

Creating Templates
------------------

Before explaining in detail how to create and render templates, look at the
following example for a quick overview of the whole process. First, you need to
create a new file in the ``templates/`` directory to store the template contents:

.. code-block:: html+twig

    {# templates/user/notifications.html.twig #}
    <h1>Hello {{ user_first_name }}!</h1>
    <p>You have {{ notifications|length }} new notifications.</p>

Then, create a :doc:`controller </controller>` that renders this template and
passes to it the needed variables::

    // src/Controller/UserController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;

    class UserController extends AbstractController
    {
        // ...

        public function notifications(): Response
        {
            // get the user information and notifications somehow
            $userFirstName = '...';
            $userNotifications = ['...', '...'];

            // the template path is the relative file path from `templates/`
            return $this->render('user/notifications.html.twig', [
                // this array defines the variables passed to the template,
                // where the key is the variable name and the value is the variable value
                // (Twig recommends using snake_case variable names: 'foo_bar' instead of 'fooBar')
                'user_first_name' => $userFirstName,
                'notifications' => $userNotifications,
            ]);
        }
    }

Template Naming
~~~~~~~~~~~~~~~

Symfony recommends the following for template names:

* Use `snake case`_ for filenames and directories (e.g. ``blog_posts.html.twig``,
  ``admin/default_theme/blog/index.html.twig``, etc.);
* Define two extensions for filenames (e.g. ``index.html.twig`` or
  ``blog_posts.xml.twig``) being the first extension (``html``, ``xml``, etc.)
  the final format that the template will generate.

Although templates usually generate HTML contents, they can generate any
text-based format. That's why the two-extension convention simplifies the way
templates are created and rendered for multiple formats.

Template Location
~~~~~~~~~~~~~~~~~

Templates are stored by default in the ``templates/`` directory. When a service
or controller renders the ``product/index.html.twig`` template, they are actually
referring to the ``<your-project>/templates/product/index.html.twig`` file.

The default templates directory is configurable with the
:ref:`twig.default_path <config-twig-default-path>` option and you can add more
template directories :ref:`as explained later <templates-namespaces>` in this article.

Template Variables
~~~~~~~~~~~~~~~~~~

A common need for templates is to print the values stored in the templates
passed from the controller or service. Variables usually store objects and
arrays instead of strings, numbers and boolean values. That's why Twig provides
quick access to complex PHP variables. Consider the following template:

.. code-block:: html+twig

    <p>{{ user.name }} added this comment on {{ comment.publishedAt|date }}</p>

The ``user.name`` notation means that you want to display some information
(``name``) stored in a variable (``user``). Is ``user`` an array or an object?
Is ``name`` a property or a method? In Twig this doesn't matter.

When using the ``foo.bar`` notation, Twig tries to get the value of the variable
in the following order:

#. ``$foo['bar']`` (array and element);
#. ``$foo->bar`` (object and public property);
#. ``$foo->bar()`` (object and public method);
#. ``$foo->getBar()`` (object and *getter* method);
#. ``$foo->isBar()`` (object and *isser* method);
#. ``$foo->hasBar()`` (object and *hasser* method);
#. If none of the above exists, use ``null`` (or throw a ``Twig\Error\RuntimeError``
   exception if the :ref:`strict_variables <config-twig-strict-variables>`
   option is enabled).

This allows to evolve your application code without having to change the
template code (you can start with array variables for the application proof of
concept, then move to objects with methods, etc.)

.. _templates-link-to-pages:

Linking to Pages
~~~~~~~~~~~~~~~~

Instead of writing the link URLs by hand, use the ``path()`` function to
generate URLs based on the :ref:`routing configuration <routing-creating-routes>`.

Later, if you want to modify the URL of a particular page, all you'll need to do
is change the routing configuration: the templates will automatically generate
the new URL.

Consider the following routing configuration:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Controller/BlogController.php
        namespace App\Controller;

        // ...
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Annotation\Route;

        class BlogController extends AbstractController
        {
            #[Route('/', name: 'blog_index')]
            public function index(): Response
            {
                // ...
            }

            #[Route('/article/{slug}', name: 'blog_post')]
            public function show(string $slug): Response
            {
                // ...
            }
        }

    .. code-block:: yaml

        # config/routes.yaml
        blog_index:
            path:       /
            controller: App\Controller\BlogController::index

        blog_post:
            path:       /article/{slug}
            controller: App\Controller\BlogController::show

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_index"
                path="/"
                controller="App\Controller\BlogController::index"/>

            <route id="blog_post"
                path="/article/{slug}"
                controller="App\Controller\BlogController::show"/>
        </routes>

    .. code-block:: php

        // config/routes.php
        use App\Controller\BlogController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes): void {
            $routes->add('blog_index', '/')
                ->controller([BlogController::class, 'index'])
            ;

            $routes->add('blog_post', '/articles/{slug}')
                ->controller([BlogController::class, 'show'])
            ;
        };

Use the ``path()`` Twig function to link to these pages and pass the route name
as the first argument and the route parameters as the optional second argument:

.. code-block:: html+twig

    <a href="{{ path('blog_index') }}">Homepage</a>

    {# ... #}

    {% for post in blog_posts %}
        <h1>
            <a href="{{ path('blog_post', {slug: post.slug}) }}">{{ post.title }}</a>
        </h1>

        <p>{{ post.excerpt }}</p>
    {% endfor %}

The ``path()`` function generates relative URLs. If you need to generate
absolute URLs (for example when rendering templates for emails or RSS feeds),
use the ``url()`` function, which takes the same arguments as ``path()``
(e.g. ``<a href="{{ url('blog_index') }}"> ... </a>``).

.. _templates-link-to-assets:

Linking to CSS, JavaScript and Image Assets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If a template needs to link to a static asset (e.g. an image), Symfony provides
an ``asset()`` Twig function to help generate that URL. First, install the
``asset`` package:

.. code-block:: terminal

    $ composer require symfony/asset

You can now use the ``asset()`` function:

.. code-block:: html+twig

    {# the image lives at "public/images/logo.png" #}
    <img src="{{ asset('images/logo.png') }}" alt="Symfony!"/>

    {# the CSS file lives at "public/css/blog.css" #}
    <link href="{{ asset('css/blog.css') }}" rel="stylesheet"/>

    {# the JS file lives at "public/bundles/acme/js/loader.js" #}
    <script src="{{ asset('bundles/acme/js/loader.js') }}"></script>

The ``asset()`` function's main purpose is to make your application more portable.
If your application lives at the root of your host (e.g. ``https://example.com``),
then the rendered path should be ``/images/logo.png``. But if your application
lives in a subdirectory (e.g. ``https://example.com/my_app``), each asset path
should render with the subdirectory (e.g. ``/my_app/images/logo.png``). The
``asset()`` function takes care of this by determining how your application is
being used and generating the correct paths accordingly.

.. tip::

    The ``asset()`` function supports various cache busting techniques via the
    :ref:`version <reference-framework-assets-version>`,
    :ref:`version_format <reference-assets-version-format>`, and
    :ref:`json_manifest_path <reference-assets-json-manifest-path>` configuration options.

If you need absolute URLs for assets, use the ``absolute_url()`` Twig function
as follows:

.. code-block:: html+twig

    <img src="{{ absolute_url(asset('images/logo.png')) }}" alt="Symfony!"/>

    <link rel="shortcut icon" href="{{ absolute_url('favicon.png') }}">

Build, Versioning & More Advanced CSS, JavaScript and Image Handling
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For help building, versioning and minifying your JavaScript and
CSS assets in a modern way, read about :doc:`Symfony's Webpack Encore </frontend>`.

.. _twig-app-variable:

The App Global Variable
~~~~~~~~~~~~~~~~~~~~~~~

Symfony creates a context object that is injected into every Twig template
automatically as a variable called ``app``. It provides access to some
application information:

.. code-block:: html+twig

    <p>Username: {{ app.user.username ?? 'Anonymous user' }}</p>
    {% if app.debug %}
        <p>Request method: {{ app.request.method }}</p>
        <p>Application Environment: {{ app.environment }}</p>
    {% endif %}

The ``app`` variable (which is an instance of :class:`Symfony\\Bridge\\Twig\\AppVariable`)
gives you access to these variables:

``app.user``
    The :ref:`current user object <create-user-class>` or ``null`` if the user
    is not authenticated.
``app.request``
    The :class:`Symfony\\Component\\HttpFoundation\\Request` object that stores
    the current :ref:`request data <accessing-request-data>` (depending on your
    application, this can be a :ref:`sub-request <http-kernel-sub-requests>`
    or a regular request).
``app.session``
    The :class:`Symfony\\Component\\HttpFoundation\\Session\\Session` object that
    represents the current :doc:`user's session </session>` or ``null`` if there is none.
``app.flashes``
    An array of all the :ref:`flash messages <flash-messages>` stored in the session.
    You can also get only the messages of some type (e.g. ``app.flashes('notice')``).
``app.environment``
    The name of the current :ref:`configuration environment <configuration-environments>`
    (``dev``, ``prod``, etc).
``app.debug``
    True if in :ref:`debug mode <debug-mode>`. False otherwise.
``app.token``
    A :class:`Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface`
    object representing the security token.
``app.current_route``
    The name of the route associated with the current request or ``null`` if no
    request is available (equivalent to ``app.request.attributes.get('_route')``)
``app.current_route_parameters``
    An array with the parameters passed to the route of the current request or an
    empty array if no request is available (equivalent to ``app.request.attributes.get('_route_params')``)
``app.locale``
    The locale used in the current :ref:`locale switcher <locale-switcher>` context.

.. versionadded:: 6.2

    The ``app.current_route`` and ``app.current_route_parameters`` variables
    were introduced in Symfony 6.2.

.. versionadded:: 6.3

    The ``app.locale`` variable was introduced in Symfony 6.3.

In addition to the global ``app`` variable injected by Symfony, you can also
inject variables automatically to all Twig templates as explained in the next
section.

.. _templating-global-variables:

Global Variables
~~~~~~~~~~~~~~~~

Twig allows you to automatically inject one or more variables into all
templates. These global variables are defined in the ``twig.globals`` option
inside the main Twig configuration file:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            globals:
                ga_tracking: 'UA-xxxxx-x'

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:global key="ga_tracking">UA-xxxxx-x</twig:global>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig): void {
            // ...

            $twig->global('ga_tracking')->value('UA-xxxxx-x');
        };

Now, the variable ``ga_tracking`` is available in all Twig templates, so you
can use it without having to pass it explicitly from the controller or service
that renders the template:

.. code-block:: html+twig

    <p>The Google tracking code is: {{ ga_tracking }}</p>

In addition to static values, Twig global variables can also reference services
from the :doc:`service container </service_container>`. The main drawback is
that these services are not loaded lazily. In other words, as soon as Twig is
loaded, your service is instantiated, even if you never use that global
variable.

To define a service as a global Twig variable, prefix the service ID string
with the ``@`` character, which is the usual syntax to :ref:`refer to services
in container parameters <service-container-parameters>`:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            globals:
                # the value is the service's id
                uuid: '@App\Generator\UuidGenerator'

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig
                https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:global key="uuid" id="App\Generator\UuidGenerator" type="service"/>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig): void {
            // ...

            $twig->global('uuid')->value(service('App\Generator\UuidGenerator'));
        };

Now you can use the ``uuid`` variable in any Twig template to access to the
``UuidGenerator`` service:

.. code-block:: twig

    UUID: {{ uuid.generate }}

Twig Components
---------------

Twig components are an alternative way to render templates, where each template
is bound to a "component class". This makes it easier to render and re-use
small template "units" - like an alert, markup for a modal, or a category sidebar.

For more information, see `UX Twig Component`_.

Twig components also have one other superpower: they can become "live", where
they automatically update (via Ajax) as the user interacts with them. For example,
when your user types into a box, your Twig component will re-render via Ajax to
show a list of results!

To learn more, see `UX Live Component`_.

.. _templates-rendering:

Rendering Templates
-------------------

Rendering a Template in Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If your controller extends from the :ref:`AbstractController <the-base-controller-class-services>`,
use the ``render()`` helper::

    // src/Controller/ProductController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;

    class ProductController extends AbstractController
    {
        public function index(): Response
        {
            // ...

            // the `render()` method returns a `Response` object with the
            // contents created by the template
            return $this->render('product/index.html.twig', [
                'category' => '...',
                'promotions' => ['...', '...'],
            ]);

            // the `renderView()` method only returns the contents created by the
            // template, so you can use those contents later in a `Response` object
            $contents = $this->renderView('product/index.html.twig', [
                'category' => '...',
                'promotions' => ['...', '...'],
            ]);

            return new Response($contents);
        }
    }

If your controller does not extend from ``AbstractController``, you'll need to
:ref:`fetch services in your controller <controller-accessing-services>` and
use the ``render()`` method of the ``twig`` service.

.. _templates-template-attribute:

Another option is to use the ``#[Template()]`` attribute on the controller method
to define the template to render::

    // src/Controller/ProductController.php
    namespace App\Controller;

    use Symfony\Bridge\Twig\Attribute\Template;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;

    class ProductController extends AbstractController
    {
        #[Template('product/index.html.twig')]
        public function index(): array
        {
            // ...

            // when using the #[Template()] attribute, you only need to return
            // an array with the parameters to pass to the template (the attribute
            // is the one which will create and return the Response object).
            return [
                'category' => '...',
                'promotions' => ['...', '...'],
            ];
        }
    }

.. versionadded:: 6.2

    The ``#[Template()]`` attribute was introduced in Symfony 6.2.

Rendering a Template in Services
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Inject the ``twig`` Symfony service into your own services and use its
``render()`` method. When using :doc:`service autowiring </service_container/autowiring>`
you only need to add an argument in the service constructor and type-hint it with
the :class:`Twig\\Environment` class::

    // src/Service/SomeService.php
    namespace App\Service;

    use Twig\Environment;

    class SomeService
    {
        public function __construct(
            private Environment $twig,
        ) {
        }

        public function someMethod(): void
        {
            // ...

            $htmlContents = $this->twig->render('product/index.html.twig', [
                'category' => '...',
                'promotions' => ['...', '...'],
            ]);
        }
    }

Rendering a Template in Emails
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Read the docs about the :ref:`mailer and Twig integration <mailer-twig>`.

.. _templates-render-from-route:

Rendering a Template Directly from a Route
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Although templates are usually rendered in controllers and services, you can
render static pages that don't need any variables directly from the route
definition. Use the special :class:`Symfony\\Bundle\\FrameworkBundle\\Controller\\TemplateController`
provided by Symfony:

.. configuration-block::

    .. code-block:: yaml

        # config/routes.yaml
        acme_privacy:
            path:          /privacy
            controller:    Symfony\Bundle\FrameworkBundle\Controller\TemplateController
            defaults:
                # the path of the template to render
                template:  'static/privacy.html.twig'

                # the response status code (default: 200)
                statusCode: 200

                # special options defined by Symfony to set the page cache
                maxAge:    86400
                sharedAge: 86400

                # whether or not caching should apply for client caches only
                private: true

                # optionally you can define some arguments passed to the template
                context:
                    site_name: 'ACME'
                    theme: 'dark'

    .. code-block:: xml

        <!-- config/routes.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing https://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="acme_privacy"
                path="/privacy"
                controller="Symfony\Bundle\FrameworkBundle\Controller\TemplateController">
                <!-- the path of the template to render -->
                <default key="template">static/privacy.html.twig</default>

                <!-- the response status code (default: 200) -->
                <default key="statusCode">200</default>

                <!-- special options defined by Symfony to set the page cache -->
                <default key="maxAge">86400</default>
                <default key="sharedAge">86400</default>

                <!-- whether or not caching should apply for client caches only -->
                <default key="private">true</default>

                <!-- optionally you can define some arguments passed to the template -->
                <default key="context">
                    <default key="site_name">ACME</default>
                    <default key="theme">dark</default>
                </default>
            </route>
        </routes>

    .. code-block:: php

        // config/routes.php
        use Symfony\Bundle\FrameworkBundle\Controller\TemplateController;
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return function (RoutingConfigurator $routes): void {
            $routes->add('acme_privacy', '/privacy')
                ->controller(TemplateController::class)
                ->defaults([
                    // the path of the template to render
                    'template'  => 'static/privacy.html.twig',

                    // the response status code (default: 200)
                    'statusCode' => 200,

                    // special options defined by Symfony to set the page cache
                    'maxAge'    => 86400,
                    'sharedAge' => 86400,

                    // whether or not caching should apply for client caches only
                    'private' => true,

                    // optionally you can define some arguments passed to the template
                    'context' => [
                        'site_name' => 'ACME',
                        'theme' => 'dark',
                    ]
                ])
            ;
        };

Checking if a Template Exists
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Templates are loaded in the application using a `Twig template loader`_, which
also provides a method to check for template existence. First, get the loader::

    use Twig\Environment;

    class YourService
    {
        // this code assumes that your service uses autowiring to inject dependencies
        // otherwise, inject the service called 'twig' manually
        public function __construct(Environment $twig)
        {
            $loader = $twig->getLoader();
        }
    }

Then, pass the path of the Twig template to the ``exists()`` method of the loader::

    if ($loader->exists('theme/layout_responsive.html.twig')) {
        // the template exists, do something
        // ...
    }

Debugging Templates
-------------------

Symfony provides several utilities to help you debug issues in your templates.

Linting Twig Templates
~~~~~~~~~~~~~~~~~~~~~~

The ``lint:twig`` command checks that your Twig templates don't have any syntax
errors. It's useful to run it before deploying your application to production
(e.g. in your continuous integration server):

.. code-block:: terminal

    # check all the application templates
    $ php bin/console lint:twig

    # you can also check directories and individual templates
    $ php bin/console lint:twig templates/email/
    $ php bin/console lint:twig templates/article/recent_list.html.twig

    # you can also show the deprecated features used in your templates
    $ php bin/console lint:twig --show-deprecations templates/email/

When running the linter inside `GitHub Actions`_, the output is automatically
adapted to the format required by GitHub, but you can force that format too:

.. code-block:: terminal

    $ php bin/console lint:twig --format=github

Inspecting Twig Information
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``debug:twig`` command lists all the information available about Twig
(functions, filters, global variables, etc.). It's useful to check if your
:ref:`custom Twig extensions <templates-twig-extension>` are working properly
and also to check the Twig features added when :ref:`installing packages <symfony-flex>`:

.. code-block:: terminal

    # list general information
    $ php bin/console debug:twig

    # filter output by any keyword
    $ php bin/console debug:twig --filter=date

    # pass a template path to show the physical file which will be loaded
    $ php bin/console debug:twig @Twig/Exception/error.html.twig

.. _twig-dump-utilities:

The Dump Twig Utilities
~~~~~~~~~~~~~~~~~~~~~~~

Symfony provides a :ref:`dump() function <components-var-dumper-dump>` as an
improved alternative to PHP's ``var_dump()`` function. This function is useful
to inspect the contents of any variable and you can use it in Twig templates too.

First, make sure that the VarDumper component is installed in the application:

.. code-block:: terminal

    $ composer require --dev symfony/debug-bundle

Then, use either the ``{% dump %}`` tag or the ``{{ dump() }}`` function
depending on your needs:

.. code-block:: html+twig

    {# templates/article/recent_list.html.twig #}
    {# the contents of this variable are sent to the Web Debug Toolbar
       instead of dumping them inside the page contents #}
    {% dump articles %}

    {% for article in articles %}
        {# the contents of this variable are dumped inside the page contents
           and they are visible on the web page #}
        {{ dump(article) }}

        {# optionally, use named arguments to display them as labels next to
           the dumped contents #}
        {{ dump(blog_posts: articles, user: app.user) }}

        <a href="/article/{{ article.slug }}">
            {{ article.title }}
        </a>
    {% endfor %}

.. versionadded:: 6.3

    The option to use named arguments in ``dump()`` was introduced in Symfony 6.3.

To avoid leaking sensitive information, the ``dump()`` function/tag is only
available in the ``dev`` and ``test`` :ref:`configuration environments <configuration-environments>`.
If you try to use it in the ``prod`` environment, you will see a PHP error.

.. _templates-reuse-contents:

Reusing Template Contents
-------------------------

.. _templates-include:

Including Templates
~~~~~~~~~~~~~~~~~~~

If certain Twig code is repeated in several templates, you can extract it into a
single "template fragment" and include it in other templates. Imagine that the
following code to display the user information is repeated in several places:

.. code-block:: html+twig

    {# templates/blog/index.html.twig #}

    {# ... #}
    <div class="user-profile">
        <img src="{{ user.profileImageUrl }}" alt="{{ user.fullName }}"/>
        <p>{{ user.fullName }} - {{ user.email }}</p>
    </div>

First, create a new Twig template called ``blog/_user_profile.html.twig`` (the
``_`` prefix is optional, but it's a convention used to better differentiate
between full templates and template fragments).

Then, remove that content from the original ``blog/index.html.twig`` template
and add the following to include the template fragment:

.. code-block:: twig

    {# templates/blog/index.html.twig #}

    {# ... #}
    {{ include('blog/_user_profile.html.twig') }}

The ``include()`` Twig function takes as argument the path of the template to
include. The included template has access to all the variables of the template
that includes it (use the `with_context`_ option to control this).

You can also pass variables to the included template. This is useful for example
to rename variables. Imagine that your template stores the user information in a
variable called ``blog_post.author`` instead of the ``user`` variable that the
template fragment expects. Use the following to *rename* the variable:

.. code-block:: twig

    {# templates/blog/index.html.twig #}

    {# ... #}
    {{ include('blog/_user_profile.html.twig', {user: blog_post.author}) }}

.. _templates-embed-controllers:

Embedding Controllers
~~~~~~~~~~~~~~~~~~~~~

:ref:`Including template fragments <templates-include>` is useful to reuse the
same content on several pages. However, this technique is not the best solution
in some cases.

Imagine that the template fragment displays the three most recent blog articles.
To do that, it needs to make a database query to get those articles. When using
the ``include()`` function, you'd need to do the same database query in every
page that includes the fragment. This is not very convenient.

A better alternative is to **embed the result of executing some controller**
with the ``render()`` and ``controller()`` Twig functions.

First, create the controller that renders a certain number of recent articles::

    // src/Controller/BlogController.php
    namespace App\Controller;

    use Symfony\Component\HttpFoundation\Response;
    // ...

    class BlogController extends AbstractController
    {
        public function recentArticles(int $max = 3): Response
        {
            // get the recent articles somehow (e.g. making a database query)
            $articles = ['...', '...', '...'];

            return $this->render('blog/_recent_articles.html.twig', [
                'articles' => $articles
            ]);
        }
    }

Then, create the ``blog/_recent_articles.html.twig`` template fragment (the
``_`` prefix in the template name is optional, but it's a convention used to
better differentiate between full templates and template fragments):

.. code-block:: html+twig

    {# templates/blog/_recent_articles.html.twig #}
    {% for article in articles %}
        <a href="{{ path('blog_show', {slug: article.slug}) }}">
            {{ article.title }}
        </a>
    {% endfor %}

Now you can call to this controller from any template to embed its result:

.. code-block:: html+twig

    {# templates/base.html.twig #}

    {# ... #}
    <div id="sidebar">
        {# if the controller is associated with a route, use the path() or url() functions #}
        {{ render(path('latest_articles', {max: 3})) }}
        {{ render(url('latest_articles', {max: 3})) }}

        {# if you don't want to expose the controller with a public URL,
           use the controller() function to define the controller to execute #}
        {{ render(controller(
            'App\\Controller\\BlogController::recentArticles', {max: 3}
        )) }}
    </div>

.. _fragments-path-config:

When using the ``controller()`` function, controllers are not accessed using a
regular Symfony route but through a special URL used exclusively to serve those
template fragments. Configure that special URL in the ``fragments`` option:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            fragments: { path: /_fragment }

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:fragment path="/_fragment"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->fragments()->path('/_fragment');
        };

.. caution::

    Embedding controllers requires making requests to those controllers and
    rendering some templates as result. This can have a significant impact on
    the application performance if you embed lots of controllers. If possible,
    :doc:`cache the template fragment </http_cache/esi>`.

.. _templates-hinclude:

How to Embed Asynchronous Content with hinclude.js
--------------------------------------------------

Templates can also embed contents asynchronously with the ``hinclude.js``
JavaScript library.

First, include the `hinclude.js`_ library in your page
:ref:`linking to it <templates-link-to-assets>` from the template or adding it
to your application JavaScript :doc:`using Webpack Encore </frontend>`.

As the embedded content comes from another page (or controller for that matter),
Symfony uses a version of the standard ``render()`` function to configure
``hinclude`` tags in templates:

.. code-block:: twig

    {{ render_hinclude(controller('...')) }}
    {{ render_hinclude(url('...')) }}

.. note::

    When using the ``controller()`` function, you must also configure the
    :ref:`fragments path option <fragments-path-config>`.

When JavaScript is disabled or it takes a long time to load you can display a
default content rendering some template:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            # ...
            fragments:
                hinclude_default_template: hinclude.html.twig

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:fragments hinclude-default-template="hinclude.html.twig"/>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            // ...
            $framework->fragments()
                ->hincludeDefaultTemplate('hinclude.html.twig')
            ;
        };

You can define default templates per ``render()`` function (which will override
any global default template that is defined):

.. code-block:: twig

    {{ render_hinclude(controller('...'),  {
        default: 'default/content.html.twig'
    }) }}

Or you can also specify a string to display as the default content:

.. code-block:: twig

    {{ render_hinclude(controller('...'), {default: 'Loading...'}) }}

Use the ``attributes`` option to define the value of hinclude.js options:

.. code-block:: twig

    {# by default, cross-site requests don't use credentials such as cookies, authorization
       headers or TLS client certificates; set this option to 'true' to use them #}
    {{ render_hinclude(controller('...'), {attributes: {'data-with-credentials': 'true'}}) }}

    {# by default, the JavaScript code included in the loaded contents is not run;
       set this option to 'true' to run that JavaScript code #}
    {{ render_hinclude(controller('...'), {attributes: {evaljs: 'true'}}) }}

Template Inheritance and Layouts
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As your application grows you'll find more and more repeated elements between
pages, such as headers, footers, sidebars, etc. :ref:`Including templates <templates-include>`
and :ref:`embedding controllers <templates-embed-controllers>` can help, but
when pages share a common structure, it's better to use **inheritance**.

The concept of `Twig template inheritance`_ is similar to PHP class inheritance.
You define a parent template that other templates can extend from and child
templates can override parts of the parent template.

Symfony recommends the following three-level template inheritance for medium and
complex applications:

* ``templates/base.html.twig``, defines the common elements of all application
  templates, such as ``<head>``, ``<header>``, ``<footer>``, etc.;
* ``templates/layout.html.twig``, extends from ``base.html.twig`` and defines
  the content structure used in all or most of the pages, such as a two-column
  content + sidebar layout. Some sections of the application can define their
  own layouts (e.g. ``templates/blog/layout.html.twig``);
* ``templates/*.html.twig``, the application pages which extend from the main
  ``layout.html.twig`` template or any other section layout.

In practice, the ``base.html.twig`` template would look like this:

.. code-block:: html+twig

    {# templates/base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8">
            <title>{% block title %}My Application{% endblock %}</title>
            {% block stylesheets %}
                <link rel="stylesheet" type="text/css" href="/css/base.css"/>
            {% endblock %}
        </head>
        <body>
            {% block body %}
                <div id="sidebar">
                    {% block sidebar %}
                        <ul>
                            <li><a href="{{ path('homepage') }}">Home</a></li>
                            <li><a href="{{ path('blog_index') }}">Blog</a></li>
                        </ul>
                    {% endblock %}
                </div>

                <div id="content">
                    {% block content %}{% endblock %}
                </div>
            {% endblock %}
        </body>
    </html>

The `Twig block tag`_ defines the page sections that can be overridden in the
child templates. They can be empty, like the ``content`` block or define a default
content, like the ``title`` block, which is displayed when child templates don't
override them.

The ``blog/layout.html.twig`` template could be like this:

.. code-block:: html+twig

    {# templates/blog/layout.html.twig #}
    {% extends 'base.html.twig' %}

    {% block content %}
        <h1>Blog</h1>

        {% block page_contents %}{% endblock %}
    {% endblock %}

The template extends from ``base.html.twig`` and only defines the contents of
the ``content`` block. The rest of the parent template blocks will display their
default contents. However, they can be overridden by the third-level inheritance
template, such as ``blog/index.html.twig``, which displays the blog index:

.. code-block:: html+twig

    {# templates/blog/index.html.twig #}
    {% extends 'blog/layout.html.twig' %}

    {% block title %}Blog Index{% endblock %}

    {% block page_contents %}
        {% for article in articles %}
            <h2>{{ article.title }}</h2>
            <p>{{ article.body }}</p>
        {% endfor %}
    {% endblock %}

This template extends from the second-level template (``blog/layout.html.twig``)
but overrides blocks of different parent templates: ``page_contents`` from
``blog/layout.html.twig`` and ``title`` from ``base.html.twig``.

When you render the ``blog/index.html.twig`` template, Symfony uses three
different templates to create the final contents. This inheritance mechanism
boosts your productivity because each template includes only its unique contents
and leaves the repeated contents and HTML structure to some parent templates.

.. caution::

    When using ``extends``, a child template is forbidden to define template
    parts outside of a block. The following code throws a ``SyntaxError``:

    .. code-block:: html+twig

        {# app/Resources/views/blog/index.html.twig #}
        {% extends 'base.html.twig' %}

        {# the line below is not captured by a "block" tag #}
        <div class="alert">Some Alert</div>

        {# the following is valid #}
        {% block content %}My cool blog posts{% endblock %}

Read the `Twig template inheritance`_ docs to learn more about how to reuse
parent block contents when overriding templates and other advanced features.

Output Escaping
---------------

Imagine that your template includes the ``Hello {{ name }}`` code to display the
user name. If a malicious user sets ``<script>alert('hello!')</script>`` as
their name and you output that value unchanged, the application will display a
JavaScript popup window.

This is known as a `Cross-Site Scripting`_ (XSS) attack. And while the previous
example seems harmless, the attacker could write more advanced JavaScript code
to perform malicious actions.

To prevent this attack, use *"output escaping"* to transform the characters
which have special meaning (e.g. replace ``<`` by the ``&lt;`` HTML entity).
Symfony applications are safe by default because they perform automatic output
escaping:

.. code-block:: html+twig

    <p>Hello {{ name }}</p>
    {# if 'name' is '<script>alert('hello!')</script>', Twig will output this:
       '<p>Hello &lt;script&gt;alert(&#39;hello!&#39;)&lt;/script&gt;</p>' #}

If you are rendering a variable that is trusted and contains HTML contents,
use the `Twig raw filter`_ to disable the output escaping for that variable:

.. code-block:: html+twig

    <h1>{{ product.title|raw }}</h1>
    {# if 'product.title' is 'Lorem <strong>Ipsum</strong>', Twig will output
       exactly that instead of 'Lorem &lt;strong&gt;Ipsum&lt;/strong&gt;' #}

Read the `Twig output escaping docs`_ to learn more about how to disable output
escaping for a block or even an entire template.

.. _templates-namespaces:

Template Namespaces
-------------------

Although most applications store their templates in the default ``templates/``
directory, you may need to store some or all of them in different directories.
Use the ``twig.paths`` option to configure those extra directories. Each path is
defined as a ``key: value`` pair where the ``key`` is the template directory and
the ``value`` is the Twig namespace, which is explained later:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            paths:
                # directories are relative to the project root dir (but you
                # can also use absolute directories)
                'email/default/templates': ~
                'backend/templates': ~

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <!-- directories are relative to the project root dir (but you
                     can also use absolute directories -->
                <twig:path>email/default/templates</twig:path>
                <twig:path>backend/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig): void {
            // ...

            // directories are relative to the project root dir (but you
            // can also use absolute directories)
            $twig->path('email/default/templates', null);
            $twig->path('backend/templates', null);
        };

When rendering a template, Symfony looks for it first in the ``twig.paths``
directories that don't define a namespace and then falls back to the default
template directory (usually, ``templates/``).

Using the above configuration, if your application renders for example the
``layout.html.twig`` template, Symfony will first look for
``email/default/templates/layout.html.twig`` and ``backend/templates/layout.html.twig``.
If any of those templates exists, Symfony will use it instead of using
``templates/layout.html.twig``, which is probably the template you wanted to use.

Twig solves this problem with **namespaces**, which group several templates
under a logic name unrelated to their actual location. Update the previous
configuration to define a namespace for each template directory:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/twig.yaml
        twig:
            # ...
            paths:
                'email/default/templates': 'email'
                'backend/templates': 'admin'

    .. code-block:: xml

        <!-- config/packages/twig.xml -->
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:twig="http://symfony.com/schema/dic/twig"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/twig https://symfony.com/schema/dic/twig/twig-1.0.xsd">

            <twig:config>
                <!-- ... -->
                <twig:path namespace="email">email/default/templates</twig:path>
                <twig:path namespace="admin">backend/templates</twig:path>
            </twig:config>
        </container>

    .. code-block:: php

        // config/packages/twig.php
        use Symfony\Config\TwigConfig;

        return static function (TwigConfig $twig): void {
            // ...

            $twig->path('email/default/templates', 'email');
            $twig->path('backend/templates', 'admin');
        };

Now, if you render the ``layout.html.twig`` template, Symfony will render the
``templates/layout.html.twig`` file. Use the special syntax ``@`` + namespace to
refer to the other namespaced templates (e.g. ``@email/layout.html.twig`` and
``@admin/layout.html.twig``).

.. note::

    A single Twig namespace can be associated with more than one template
    directory. In that case, the order in which paths are added is important
    because Twig will start looking for templates from the first defined path.

Bundle Templates
~~~~~~~~~~~~~~~~

If you :ref:`install packages/bundles <symfony-flex>` in your application, they
may include their own Twig templates (in the ``Resources/views/`` directory of
each bundle). To avoid messing with your own templates, Symfony adds bundle
templates under an automatic namespace created after the bundle name.

For example, the templates of a bundle called ``AcmeFooBundle`` are available
under the ``AcmeFoo`` namespace. If this bundle includes the template
``<your-project>/vendor/acmefoo-bundle/Resources/views/user/profile.html.twig``,
you can refer to it as ``@AcmeFoo/user/profile.html.twig``.

.. tip::

    You can also :ref:`override bundle templates <override-templates>` in case
    you want to change some parts of the original bundle templates.

.. _templates-twig-extension:

Writing a Twig Extension
------------------------

`Twig Extensions`_ allow the creation of custom functions, filters, and more to use
in your Twig templates. Before writing your own Twig extension, check if
the filter/function that you need is already implemented in:

* The `default Twig filters and functions`_;
* The :doc:`Twig filters and functions added by Symfony </reference/twig_reference>`;
* The `official Twig extensions`_ related to strings, HTML, Markdown, internationalization, etc.

Create the Extension Class
~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to create a new filter called ``price`` that formats a number
as currency:

.. code-block:: twig

    {{ product.price|price }}

    {# pass in the 3 optional arguments #}
    {{ product.price|price(2, ',', '.') }}

Create a class that extends ``AbstractExtension`` and fill in the logic::

    // src/Twig/AppExtension.php
    namespace App\Twig;

    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class AppExtension extends AbstractExtension
    {
        public function getFilters(): array
        {
            return [
                new TwigFilter('price', [$this, 'formatPrice']),
            ];
        }

        public function formatPrice(float $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$'.$price;

            return $price;
        }
    }

If you want to create a function instead of a filter, define the
``getFunctions()`` method::

    // src/Twig/AppExtension.php
    namespace App\Twig;

    use Twig\Extension\AbstractExtension;
    use Twig\TwigFunction;

    class AppExtension extends AbstractExtension
    {
        public function getFunctions(): array
        {
            return [
                new TwigFunction('area', [$this, 'calculateArea']),
            ];
        }

        public function calculateArea(int $width, int $length): int
        {
            return $width * $length;
        }
    }

.. tip::

    Along with custom filters and functions, you can also register
    `global variables`_.

Register an Extension as a Service
..................................

Next, register your class as a service and tag it with ``twig.extension``. If you're
using the :ref:`default services.yaml configuration <service-container-services-load-example>`,
you're done! Symfony will automatically know about your new service and add the tag.

You can now start using your filter in any Twig template. Optionally, execute
this command to confirm that your new filter was successfully registered:

.. code-block:: terminal

    # display all information about Twig
    $ php bin/console debug:twig

    # display only the information about a specific filter
    $ php bin/console debug:twig --filter=price

.. _lazy-loaded-twig-extensions:

Creating Lazy-Loaded Twig Extensions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Including the code of the custom filters/functions in the Twig extension class
is the simplest way to create extensions. However, Twig must initialize all
extensions before rendering any template, even if the template doesn't use an
extension.

If extensions don't define dependencies (i.e. if you don't inject services in
them) performance is not affected. However, if extensions define lots of complex
dependencies (e.g. those making database connections), the performance loss can
be significant.

That's why Twig allows decoupling the extension definition from its
implementation. Following the same example as before, the first change would be
to remove the ``formatPrice()`` method from the extension and update the PHP
callable defined in ``getFilters()``::

    // src/Twig/AppExtension.php
    namespace App\Twig;

    use App\Twig\AppRuntime;
    use Twig\Extension\AbstractExtension;
    use Twig\TwigFilter;

    class AppExtension extends AbstractExtension
    {
        public function getFilters(): array
        {
            return [
                // the logic of this filter is now implemented in a different class
                new TwigFilter('price', [AppRuntime::class, 'formatPrice']),
            ];
        }
    }

Then, create the new ``AppRuntime`` class (it's not required but these classes
are suffixed with ``Runtime`` by convention) and include the logic of the
previous ``formatPrice()`` method::

    // src/Twig/AppRuntime.php
    namespace App\Twig;

    use Twig\Extension\RuntimeExtensionInterface;

    class AppRuntime implements RuntimeExtensionInterface
    {
        public function __construct()
        {
            // this simple example doesn't define any dependency, but in your own
            // extensions, you'll need to inject services using this constructor
        }

        public function formatPrice(float $number, int $decimals = 0, string $decPoint = '.', string $thousandsSep = ','): string
        {
            $price = number_format($number, $decimals, $decPoint, $thousandsSep);
            $price = '$'.$price;

            return $price;
        }
    }

If you're using the default ``services.yaml`` configuration, this will already
work! Otherwise, :ref:`create a service <service-container-creating-service>`
for this class and :doc:`tag your service </service_container/tags>` with ``twig.runtime``.

.. _`Twig`: https://twig.symfony.com
.. _`tags`: https://twig.symfony.com/doc/3.x/tags/index.html
.. _`filters`: https://twig.symfony.com/doc/3.x/filters/index.html
.. _`functions`: https://twig.symfony.com/doc/3.x/functions/index.html
.. _`with_context`: https://twig.symfony.com/doc/3.x/functions/include.html
.. _`Twig template loader`: https://twig.symfony.com/doc/3.x/api.html#loaders
.. _`Twig raw filter`: https://twig.symfony.com/doc/3.x/filters/raw.html
.. _`Twig output escaping docs`: https://twig.symfony.com/doc/3.x/api.html#escaper-extension
.. _`snake case`: https://en.wikipedia.org/wiki/Snake_case
.. _`Twig template inheritance`: https://twig.symfony.com/doc/3.x/tags/extends.html
.. _`Twig block tag`: https://twig.symfony.com/doc/3.x/tags/block.html
.. _`Cross-Site Scripting`: https://en.wikipedia.org/wiki/Cross-site_scripting
.. _`GitHub Actions`: https://docs.github.com/en/free-pro-team@latest/actions
.. _`UX Twig Component`: https://symfony.com/bundles/ux-twig-component/current/index.html
.. _`UX Live Component`: https://symfony.com/bundles/ux-live-component/current/index.html
.. _`Twig Extensions`: https://twig.symfony.com/doc/3.x/advanced.html#creating-an-extension
.. _`default Twig filters and functions`: https://twig.symfony.com/doc/3.x/#reference
.. _`official Twig extensions`: https://github.com/twigphp?q=extra
.. _`global variables`: https://twig.symfony.com/doc/3.x/advanced.html#id1
.. _`hinclude.js`: https://mnot.github.io/hinclude/
