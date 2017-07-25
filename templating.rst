.. index::
   single: Templating

Creating and Using Templates
============================

As explained in :doc:`the previous article </controller>`, controllers are
responsible for handling each request that comes into a Symfony application and
the usually end up rendering a template to generate the response contents.

In reality, the controller delegates most of the heavy work to other places so
that code can be tested and reused. When a controller needs to generate HTML,
CSS or any other content, it hands the work off to the templating engine.

In this article, you'll learn how to write powerful templates that can be
used to return content to the user, populate email bodies, and more. You'll
learn shortcuts, clever ways to extend templates and how to reuse template
code.

.. index::
   single: Templating; What is a template?

Templates
---------

A template is simply a text file that can generate any text-based format
(HTML, XML, CSV, LaTeX ...). The most familiar type of template is a *PHP*
template - a text file parsed by PHP that contains a mix of text and PHP code:

.. code-block:: html+php

    <!DOCTYPE html>
    <html>
        <head>
            <title>Welcome to Symfony!</title>
        </head>
        <body>
            <h1><?php echo $page_title ?></h1>

            <ul id="navigation">
                <?php foreach ($navigation as $item): ?>
                    <li>
                        <a href="<?php echo $item->getHref() ?>">
                            <?php echo $item->getCaption() ?>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </body>
    </html>

.. index:: Twig; Introduction

But Symfony packages an even more powerful templating language called `Twig`_.
Twig allows you to write concise, readable templates that are more friendly
to web designers and, in several ways, more powerful than PHP templates:

.. code-block:: html+twig

    <!DOCTYPE html>
    <html>
        <head>
            <title>Welcome to Symfony!</title>
        </head>
        <body>
            <h1>{{ page_title }}</h1>

            <ul id="navigation">
                {% for item in navigation %}
                    <li><a href="{{ item.href }}">{{ item.caption }}</a></li>
                {% endfor %}
            </ul>
        </body>
    </html>

Twig defines three types of special syntax:

``{{ ... }}``
    "Says something": prints a variable or the result of an expression to the
    template.

``{% ... %}``
    "Does something": a **tag** that controls the logic of the template; it is
    used to execute statements such as for-loops for example.

``{# ... #}``
    "Comment something": it's the equivalent of the PHP ``/* comment */`` syntax.
    It's used to add single or multi-line comments. The content of the comments
    isn't included in the rendered pages.

Twig also contains **filters**, which modify content before being rendered.
The following makes the ``title`` variable all uppercase before rendering
it:

.. code-block:: twig

    {{ title|upper }}

Twig comes with a long list of `tags`_, `filters`_ and `functions`_ that are available
by default. You can even add your own *custom* filters, functions (and more) via
a :doc:`Twig Extension </templating/twig_extension>`.

Twig code will look similar to PHP code, with subtle, nice differences. The following
example uses a standard ``for`` tag and the ``cycle()`` function to print ten div tags,
with alternating ``odd``, ``even`` classes:

.. code-block:: html+twig

    {% for i in 1..10 %}
        <div class="{{ cycle(['even', 'odd'], i) }}">
          <!-- some HTML here -->
        </div>
    {% endfor %}

Throughout this article, template examples will be shown in both Twig and PHP.

.. sidebar:: Why Twig?

    Twig templates are meant to be simple and won't process PHP tags. This
    is by design: the Twig template system is meant to express presentation,
    not program logic. The more you use Twig, the more you'll appreciate
    and benefit from this distinction. And of course, you'll be loved by
    web designers everywhere.

    Twig can also do things that PHP can't, such as whitespace control,
    sandboxing, automatic HTML escaping, manual contextual output escaping,
    and the inclusion of custom functions and filters that only affect templates.
    Twig contains little features that make writing templates easier and more concise.
    Take the following example, which combines a loop with a logical ``if``
    statement:

    .. code-block:: html+twig

        <ul>
            {% for user in users if user.active %}
                <li>{{ user.username }}</li>
            {% else %}
                <li>No users found</li>
            {% endfor %}
        </ul>

.. index::
   pair: Twig; Cache

Twig Template Caching
~~~~~~~~~~~~~~~~~~~~~

Twig is fast because each template is compiled to a native PHP class and cached.
But don't worry: this happens automatically and doesn't require *you* to do anything.
And while you're developing, Twig is smart enough to re-compile your templates after
you make any changes. That means Twig is fast in production, but easy to use while
developing.

.. index::
   single: Templating; Inheritance

.. _twig-inheritance:

Template Inheritance and Layouts
--------------------------------

More often than not, templates in a project share common elements, like the
header, footer, sidebar or more. In Symfony, this problem is thought about
differently: a template can be decorated by another one. This works
exactly the same as PHP classes: template inheritance allows you to build
a base "layout" template that contains all the common elements of your site
defined as **blocks** (think "PHP class with base methods"). A child template
can extend the base layout and override any of its blocks (think "PHP subclass
that overrides certain methods of its parent class").

First, build a base layout file:

.. code-block:: html+twig

    {# app/Resources/views/base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8">
            <title>{% block title %}Test Application{% endblock %}</title>
        </head>
        <body>
            <div id="sidebar">
                {% block sidebar %}
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/blog">Blog</a></li>
                    </ul>
                {% endblock %}
            </div>

            <div id="content">
                {% block body %}{% endblock %}
            </div>
        </body>
    </html>

.. note::

    Though the discussion about template inheritance will be in terms of Twig,
    the philosophy is the same between Twig and PHP templates.

This template defines the base HTML skeleton document of a simple two-column
page. In this example, three ``{% block %}`` areas are defined (``title``,
``sidebar`` and ``body``). Each block may be overridden by a child template
or left with its default implementation. This template could also be rendered
directly. In that case the ``title``, ``sidebar`` and ``body`` blocks would
simply retain the default values used in this template.

A child template might look like this:

.. code-block:: html+twig

    {# app/Resources/views/blog/index.html.twig #}
    {% extends 'base.html.twig' %}

    {% block title %}My cool blog posts{% endblock %}

    {% block body %}
        {% for entry in blog_entries %}
            <h2>{{ entry.title }}</h2>
            <p>{{ entry.body }}</p>
        {% endfor %}
    {% endblock %}

.. note::

   The parent template is stored in ``app/Resources/views/``, so its path is
   simply ``base.html.twig``. The template naming conventions are explained
   fully in :ref:`template-naming-locations`.

The key to template inheritance is the ``{% extends %}`` tag. This tells
the templating engine to first evaluate the base template, which sets up
the layout and defines several blocks. The child template is then rendered,
at which point the ``title`` and ``body`` blocks of the parent are replaced
by those from the child. Depending on the value of ``blog_entries``, the
output might look like this:

.. code-block:: html

    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8">
            <title>My cool blog posts</title>
        </head>
        <body>
            <div id="sidebar">
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/blog">Blog</a></li>
                </ul>
            </div>

            <div id="content">
                <h2>My first post</h2>
                <p>The body of the first post.</p>

                <h2>Another post</h2>
                <p>The body of the second post.</p>
            </div>
        </body>
    </html>

Notice that since the child template didn't define a ``sidebar`` block, the
value from the parent template is used instead. Content within a ``{% block %}``
tag in a parent template is always used by default.

.. tip::

    You can use as many levels of inheritance as you want! See :doc:`/templating/inheritance`
    for more info.

When working with template inheritance, here are some tips to keep in mind:

* If you use ``{% extends %}`` in a template, it must be the first tag in
  that template;

* The more ``{% block %}`` tags you have in your base templates, the better.
  Remember, child templates don't have to define all parent blocks, so create
  as many blocks in your base templates as you want and give each a sensible
  default. The more blocks your base templates have, the more flexible your
  layout will be;

* If you find yourself duplicating content in a number of templates, it probably
  means you should move that content to a ``{% block %}`` in a parent template.
  In some cases, a better solution may be to move the content to a new template
  and ``include`` it (see :ref:`including-templates`);

* If you need to get the content of a block from the parent template, you
  can use the ``{{ parent() }}`` function. This is useful if you want to add
  to the contents of a parent block instead of completely overriding it:

  .. code-block:: html+twig

      {% block sidebar %}
          <h3>Table of Contents</h3>

          {# ... #}

          {{ parent() }}
      {% endblock %}

.. index::
   single: Templating; Naming conventions
   single: Templating; File locations

.. _template-naming-locations:

Template Naming and Locations
-----------------------------

By default, templates can live in two different locations:

``app/Resources/views/``
    The application's ``views`` directory can contain application-wide base templates
    (i.e. your application's layouts and templates of the application bundle) as
    well as templates that override third party bundle templates
    (see :doc:`/templating/overriding`).

``vendor/path/to/CoolBundle/Resources/views/``
    Each third party bundle houses its templates in its ``Resources/views/``
    directory (and subdirectories). When you plan to share your bundle, you should
    put the templates in the bundle instead of the ``app/`` directory.

Most of the templates you'll use live in the ``app/Resources/views/``
directory. The path you'll use will be relative to this directory. For example,
to render/extend ``app/Resources/views/base.html.twig``, you'll use the
``base.html.twig`` path and to render/extend
``app/Resources/views/blog/index.html.twig``, you'll use the
``blog/index.html.twig`` path.

.. _template-referencing-in-bundle:

Referencing Templates in a Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

*If* you need to refer to a template that lives in a bundle, Symfony uses the
Twig namespaced syntax (``@BundleName/directory/filename.html.twig``). This allows
for several types of templates, each which lives in a specific location:

* ``@AcmeBlog/Blog/index.html.twig``: This syntax is used to specify a
  template for a specific page. The three parts of the string, each separated
  by a slash (``/``), mean the following:

  * ``@AcmeBlog``: is the bundle name without the ``Bundle`` suffix. This template
    lives in the AcmeBlogBundle (e.g. ``src/Acme/BlogBundle``);

  * ``Blog``: (*directory*) indicates that the template lives inside the
    ``Blog`` subdirectory of ``Resources/views/``;

  * ``index.html.twig``: (*filename*) the actual name of the file is
    ``index.html.twig``.

  Assuming that the AcmeBlogBundle lives at ``src/Acme/BlogBundle``, the
  final path to the layout would be ``src/Acme/BlogBundle/Resources/views/Blog/index.html.twig``.

* ``@AcmeBlog/layout.html.twig``: This syntax refers to a base template
  that's specific to the AcmeBlogBundle. Since the middle, "directory", portion
  is missing (e.g. ``Blog``), the template lives at
  ``Resources/views/layout.html.twig`` inside AcmeBlogBundle.

In the :doc:`/templating/overriding` section, you'll find out how each
template living inside the AcmeBlogBundle, for example, can be overridden
by placing a template of the same name in the ``app/Resources/AcmeBlogBundle/views/``
directory. This gives the power to override templates from any vendor bundle.

Template Suffix
~~~~~~~~~~~~~~~

Every template name also has two extensions that specify the *format* and
*engine* for that template.

========================  ======  ======
Filename                  Format  Engine
========================  ======  ======
``blog/index.html.twig``  HTML    Twig
``blog/index.html.php``   HTML    PHP
``blog/index.css.twig``   CSS     Twig
========================  ======  ======

By default, any Symfony template can be written in either Twig or PHP, and
the last part of the extension (e.g. ``.twig`` or ``.php``) specifies which
of these two *engines* should be used. The first part of the extension,
(e.g. ``.html``, ``.css``, etc) is the final format that the template will
generate. Unlike the engine, which determines how Symfony parses the template,
this is simply an organizational tactic used in case the same resource needs
to be rendered as HTML (``index.html.twig``), XML (``index.xml.twig``),
or any other format. For more information, read the :doc:`/templating/formats`
section.

.. note::

   The available "engines" can be configured and even new engines added.
   See :ref:`Templating Configuration <template-configuration>` for more details.

.. index::
   single: Templating; Tags and helpers
   single: Templating; Helpers

Tags and Helpers
----------------

You already understand the basics of templates, how they're named and how
to use template inheritance. The hardest parts are already behind you. In
this section, you'll learn about a large group of tools available to help
perform the most common template tasks such as including other templates,
linking to pages and including images.

Symfony comes bundled with several specialized Twig tags and functions that
ease the work of the template designer. In PHP, the templating system provides
an extensible *helper* system that provides useful features in a template
context.

You've already seen a few built-in Twig tags (``{% block %}`` & ``{% extends %}``)
as well as an example of a PHP helper (``$view['slots']``). Here you will learn a
few more.

.. index::
   single: Templating; Including other templates

.. _including-templates:

Including other Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

You'll often want to include the same template or code fragment on several
pages. For example, in an application with "news articles", the
template code displaying an article might be used on the article detail page,
on a page displaying the most popular articles, or in a list of the latest
articles.

When you need to reuse a chunk of PHP code, you typically move the code to
a new PHP class or function. The same is true for templates. By moving the
reused template code into its own template, it can be included from any other
template. First, create the template that you'll need to reuse.

.. code-block:: html+twig

    {# app/Resources/views/article/article_details.html.twig #}
    <h2>{{ article.title }}</h2>
    <h3 class="byline">by {{ article.authorName }}</h3>

    <p>
        {{ article.body }}
    </p>

Including this template from any other template is simple:

.. code-block:: html+twig

    {# app/Resources/views/article/list.html.twig #}
    {% extends 'layout.html.twig' %}

    {% block body %}
        <h1>Recent Articles<h1>

        {% for article in articles %}
            {{ include('article/article_details.html.twig', { 'article': article }) }}
        {% endfor %}
    {% endblock %}

The template is included using the ``{{ include() }}`` function. Notice that the
template name follows the same typical convention. The ``article_details.html.twig``
template uses an ``article`` variable, which we pass to it. In this case,
you could avoid doing this entirely, as all of the variables available in
``list.html.twig`` are also available in ``article_details.html.twig`` (unless
you set `with_context`_ to false).

.. tip::

    The ``{'article': article}`` syntax is the standard Twig syntax for hash
    maps (i.e. an array with named keys). If you needed to pass in multiple
    elements, it would look like this: ``{'foo': foo, 'bar': bar}``.

.. versionadded:: 2.3
    The `include() function`_ is available since Symfony 2.3. Prior, the
    `{% include %} tag`_ was used.

.. index::
   single: Templating; Linking to pages

.. _templating-pages:

Linking to Pages
~~~~~~~~~~~~~~~~

Creating links to other pages in your application is one of the most common
jobs for a template. Instead of hardcoding URLs in templates, use the ``path``
Twig function (or the ``router`` helper in PHP) to generate URLs based on
the routing configuration. Later, if you want to modify the URL of a particular
page, all you'll need to do is change the routing configuration: the templates
will automatically generate the new URL.

First, link to the "welcome" page, which is accessible via the following routing
configuration:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/WelcomeController.php

        // ...
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class WelcomeController extends Controller
        {
            /**
             * @Route("/", name="welcome")
             */
            public function indexAction()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        welcome:
            path:     /
            defaults: { _controller: AppBundle:Welcome:index }

    .. code-block:: xml

        <!-- app/config/routing.yml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="welcome" path="/">
                <default key="_controller">AppBundle:Welcome:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\Route;
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->add('welcome', new Route('/', array(
            '_controller' => 'AppBundle:Welcome:index',
        )));

        return $collection;

To link to the page, just use the ``path()`` Twig function and refer to the route:

.. code-block:: html+twig

    <a href="{{ path('welcome') }}">Home</a>

As expected, this will generate the URL ``/``. Now, for a more complicated
route:

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/ArticleController.php

        // ...
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class ArticleController extends Controller
        {
            /**
             * @Route("/article/{slug}", name="article_show")
             */
            public function showAction($slug)
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        article_show:
            path:     /article/{slug}
            defaults: { _controller: AppBundle:Article:show }

    .. code-block:: xml

        <!-- app/config/routing.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="article_show" path="/article/{slug}">
                <default key="_controller">AppBundle:Article:show</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\Route;
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->add('article_show', new Route('/article/{slug}', array(
            '_controller' => 'AppBundle:Article:show',
        )));

        return $collection;

In this case, you need to specify both the route name (``article_show``) and
a value for the ``{slug}`` parameter. Using this route, revisit the
``recent_list.html.twig`` template from the previous section and link to the articles
correctly:

.. code-block:: html+twig

    {# app/Resources/views/article/recent_list.html.twig #}
    {% for article in articles %}
        <a href="{{ path('article_show', {'slug': article.slug}) }}">
            {{ article.title }}
        </a>
    {% endfor %}

.. tip::

    You can also generate an absolute URL by using the ``url()`` Twig function:

    .. code-block:: html+twig

        <a href="{{ url('welcome') }}">Home</a>

    The same can be done in PHP templates by passing a third argument to
    the ``generate()`` method:

    .. code-block:: html+php

        <?php
        use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
        ?>

        <a href="<?php echo $view['router']->generate(
            'welcome',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        ) ?>">Home</a>

.. index::
   single: Templating; Linking to assets

.. _templating-assets:

Linking to Assets
~~~~~~~~~~~~~~~~~

Templates also commonly refer to images, JavaScript, stylesheets and other
assets. Of course you could hard-code the path to these assets (e.g. ``/images/logo.png``),
but Symfony provides a more dynamic option via the ``asset()`` Twig function:

.. code-block:: html+twig

    <img src="{{ asset('images/logo.png') }}" alt="Symfony!" />

    <link href="{{ asset('css/blog.css') }}" rel="stylesheet" />

The ``asset()`` function's main purpose is to make your application more portable.
If your application lives at the root of your host (e.g. ``http://example.com``),
then the rendered paths should be ``/images/logo.png``. But if your application
lives in a subdirectory (e.g. ``http://example.com/my_app``), each asset path
should render with the subdirectory (e.g. ``/my_app/images/logo.png``). The
``asset()`` function takes care of this by determining how your application is
being used and generating the correct paths accordingly.

Additionally, if you use the ``asset()`` function, Symfony can automatically
append a query string to your asset, in order to guarantee that updated static
assets won't be loaded from cache after being deployed. For example, ``/images/logo.png`` might
look like ``/images/logo.png?v2``. For more information, see the :ref:`reference-framework-assets-version`
configuration option.

If you need absolute URLs for assets, use the ``absolute_url()`` Twig function
as follows:

.. code-block:: html+jinja

    <img src="{{ absolute_url(asset('images/logo.png')) }}" alt="Symfony!" />

.. index::
   single: Templating; Including stylesheets and JavaScripts
   single: Stylesheets; Including stylesheets
   single: JavaScript; Including JavaScripts

Including Stylesheets and JavaScripts in Twig
---------------------------------------------

No site would be complete without including JavaScript files and stylesheets.
In Symfony, the inclusion of these assets is handled elegantly by taking
advantage of Symfony's template inheritance.

.. tip::

    This section will teach you the philosophy behind including stylesheet
    and JavaScript assets in Symfony. Symfony also packages another library,
    called Assetic, which follows this philosophy but allows you to do much
    more interesting things with those assets. For more information on
    using Assetic see :doc:`/assetic/asset_management`.

Start by adding two blocks to your base template that will hold your assets:
one called ``stylesheets`` inside the ``head`` tag and another called ``javascripts``
just above the closing ``body`` tag. These blocks will contain all of the
stylesheets and JavaScripts that you'll need throughout your site:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/base.html.twig #}
        <html>
            <head>
                {# ... #}

                {% block stylesheets %}
                    <link href="{{ asset('css/main.css') }}" rel="stylesheet" />
                {% endblock %}
            </head>
            <body>
                {# ... #}

                {% block javascripts %}
                    <script src="{{ asset('js/main.js') }}"></script>
                {% endblock %}
            </body>
        </html>

    .. code-block:: php

        // app/Resources/views/base.html.php
        <html>
            <head>
                <?php ... ?>

                <?php $view['slots']->start('stylesheets') ?>
                    <link href="<?php echo $view['assets']->getUrl('css/main.css') ?>" rel="stylesheet" />
                <?php $view['slots']->stop() ?>
            </head>
            <body>
                <?php ... ?>

                <?php $view['slots']->start('javascripts') ?>
                    <script src="<?php echo $view['assets']->getUrl('js/main.js') ?>"></script>
                <?php $view['slots']->stop() ?>
            </body>
        </html>

That's easy enough! But what if you need to include an extra stylesheet or
JavaScript from a child template? For example, suppose you have a contact
page and you need to include a ``contact.css`` stylesheet *just* on that
page. From inside that contact page's template, do the following:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/contact/contact.html.twig #}
        {% extends 'base.html.twig' %}

        {% block stylesheets %}
            {{ parent() }}

            <link href="{{ asset('css/contact.css') }}" rel="stylesheet" />
        {% endblock %}

        {# ... #}

    .. code-block:: php

        // app/Resources/views/contact/contact.html.twig
        <?php $view->extend('base.html.php') ?>

        <?php $view['slots']->start('stylesheets') ?>
            <link href="<?php echo $view['assets']->getUrl('css/contact.css') ?>" rel="stylesheet" />
        <?php $view['slots']->stop() ?>

In the child template, you simply override the ``stylesheets`` block and
put your new stylesheet tag inside of that block. Of course, since you want
to add to the parent block's content (and not actually *replace* it), you
should use the ``parent()`` Twig function to include everything from the ``stylesheets``
block of the base template.

You can also include assets located in your bundles' ``Resources/public`` folder.
You will need to run the ``php app/console assets:install target [--symlink]``
command, which copies (or symlinks) files into the correct location. (target
is by default "web").

.. code-block:: html+twig

    <link href="{{ asset('bundles/acmedemo/css/contact.css') }}" rel="stylesheet" />

The end result is a page that includes ``main.js`` and both the ``main.css`` and ``contact.css``
stylesheets.

Referencing the Request, User or Session
----------------------------------------

Symfony also gives you a global ``app`` variable in Twig that can be used to access
the current user, the Request and more.

See :doc:`/templating/app_variable` for details.

Output Escaping
---------------

Twig performs automatic "output escaping" when rendering any content in order to
protect you from Cross Site Scripting (XSS) attacks.

Suppose ``description`` equals ``I <3 this product``:

.. code-block:: twig

    <!-- output escaping is on automatically -->
    {{ description }} <!-- I &lt;3 this product -->

    <!-- disable output escaping with the raw filter -->
    {{ description|raw }} <!-- I <3 this product -->

.. caution::

    PHP templates do not automatically escape content.

For more details, see :doc:`/templating/escaping`.

Final Thoughts
--------------

The templating system is just *one* of the many tools in Symfony. And its job is
simple: allow us to render dynamic & complex HTML output so that this can ultimately
be returned to the user, sent in an email or something else.

Keep Going!
-----------

Before diving into the rest of Symfony, check out the :doc:`configuration system </configuration>`.

Learn more
----------

.. toctree::
    :hidden:

    configuration

.. toctree::
    :maxdepth: 1
    :glob:

    /templating/*

.. _`Twig`: http://twig.sensiolabs.org
.. _`tags`: http://twig.sensiolabs.org/doc/tags/index.html
.. _`filters`: http://twig.sensiolabs.org/doc/filters/index.html
.. _`functions`: http://twig.sensiolabs.org/doc/functions/index.html
.. _`add your own extensions`: http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
.. _`with_context`: http://twig.sensiolabs.org/doc/functions/include.html
.. _`include() function`: http://twig.sensiolabs.org/doc/functions/include.html
.. _`{% include %} tag`: http://twig.sensiolabs.org/doc/tags/include.html
