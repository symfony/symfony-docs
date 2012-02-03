.. index::
   single: Templating

Creating and using Templates
============================

As you know, the :doc:`controller </book/controller>` is responsible for
handling each request that comes into a Symfony2 application. In reality,
the controller delegates the most of the heavy work to other places so that
code can be tested and reused. When a controller needs to generate HTML,
CSS or any other content, it hands the work off to the templating engine.
In this chapter, you'll learn how to write powerful templates that can be
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
                <?php endforeach; ?>
            </ul>
        </body>
    </html>

.. index:: Twig; Introduction

But Symfony2 packages an even more powerful templating language called `Twig`_.
Twig allows you to write concise, readable templates that are more friendly
to web designers and, in several ways, more powerful than PHP templates:

.. code-block:: html+jinja

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

Twig defines two types of special syntax:

* ``{{ ... }}``: "Says something": prints a variable or the result of an
  expression to the template;

* ``{% ... %}``: "Does something": a **tag** that controls the logic of the
  template; it is used to execute statements such as for-loops for example.

.. note::

   There is a third syntax used for creating comments: ``{# this is a comment #}``.
   This syntax can be used across multiple lines like the PHP-equivalent
   ``/* comment */`` syntax.

Twig also contains **filters**, which modify content before being rendered.
The following makes the ``title`` variable all uppercase before rendering
it:

.. code-block:: jinja

    {{ title|upper }}

Twig comes with a long list of `tags`_ and `filters`_ that are available
by default. You can even `add your own extensions`_ to Twig as needed.

.. tip::

    Registering a Twig extension is as easy as creating a new service and tagging
    it with ``twig.extension`` :ref:`tag<reference-dic-tags-twig-extension>`.

As you'll see throughout the documentation, Twig also supports functions
and new functions can be easily added. For example, the following uses a
standard ``for`` tag and the ``cycle`` function to print ten div tags, with
alternating ``odd``, ``even`` classes:

.. code-block:: html+jinja

    {% for i in 0..10 %}
        <div class="{{ cycle(['odd', 'even'], i) }}">
          <!-- some HTML here -->
        </div>
    {% endfor %}

Throughout this chapter, template examples will be shown in both Twig and PHP.

.. sidebar:: Why Twig?

    Twig templates are meant to be simple and won't process PHP tags. This
    is by design: the Twig template system is meant to express presentation,
    not program logic. The more you use Twig, the more you'll appreciate
    and benefit from this distinction. And of course, you'll be loved by
    web designers everywhere.
    
    Twig can also do things that PHP can't, such as true template inheritance
    (Twig templates compile down to PHP classes that inherit from each other),
    whitespace control, sandboxing, and the inclusion of custom functions
    and filters that only affect templates. Twig contains little features
    that make writing templates easier and more concise. Take the following
    example, which combines a loop with a logical ``if`` statement:
    
    .. code-block:: html+jinja
    
        <ul>
            {% for user in users %}
                <li>{{ user.username }}</li>
            {% else %}
                <li>No users found</li>
            {% endfor %}
        </ul>

.. index::
   pair: Twig; Cache

Twig Template Caching
~~~~~~~~~~~~~~~~~~~~~

Twig is fast. Each Twig template is compiled down to a native PHP class
that is rendered at runtime. The compiled classes are located in the
``app/cache/{environment}/twig`` directory (where ``{environment}`` is the
environment, such as ``dev`` or ``prod``) and in some cases can be useful
while debugging. See :ref:`environments-summary` for more information on
environments.

When ``debug`` mode is enabled (common in the ``dev`` environment), a Twig
template will be automatically recompiled when changes are made to it. This
means that during development you can happily make changes to a Twig template
and instantly see the changes without needing to worry about clearing any
cache.

When ``debug`` mode is disabled (common in the ``prod`` environment), however,
you must clear the Twig cache directory so that the Twig templates will
regenerate. Remember to do this when deploying your application.

.. index::
   single: Templating; Inheritance

Template Inheritance and Layouts
--------------------------------

More often than not, templates in a project share common elements, like the
header, footer, sidebar or more. In Symfony2, we like to think about this
problem differently: a template can be decorated by another one. This works
exactly the same as PHP classes: template inheritance allows you to build
a base "layout" template that contains all the common elements of your site
defined as **blocks** (think "PHP class with base methods"). A child template
can extend the base layout and override any of its blocks (think "PHP subclass
that overrides certain methods of its parent class").

First, build a base layout file:

.. configuration-block::

    .. code-block:: html+jinja

        {# app/Resources/views/base.html.twig #}
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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

    .. code-block:: html+php

        <!-- app/Resources/views/base.html.php -->
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php $view['slots']->output('title', 'Test Application') ?></title>
            </head>
            <body>
                <div id="sidebar">
                    <?php if ($view['slots']->has('sidebar'): ?>
                        <?php $view['slots']->output('sidebar') ?>
                    <?php else: ?>
                        <ul>
                            <li><a href="/">Home</a></li>
                            <li><a href="/blog">Blog</a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                <div id="content">
                    <?php $view['slots']->output('body') ?>
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

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/BlogBundle/Resources/views/Blog/index.html.twig #}
        {% extends '::base.html.twig' %}

        {% block title %}My cool blog posts{% endblock %}

        {% block body %}
            {% for entry in blog_entries %}
                <h2>{{ entry.title }}</h2>
                <p>{{ entry.body }}</p>
            {% endfor %}
        {% endblock %}

    .. code-block:: html+php

        <!-- src/Acme/BlogBundle/Resources/views/Blog/index.html.php -->
        <?php $view->extend('::base.html.php') ?>

        <?php $view['slots']->set('title', 'My cool blog posts') ?>

        <?php $view['slots']->start('body') ?>
            <?php foreach ($blog_entries as $entry): ?>
                <h2><?php echo $entry->getTitle() ?></h2>
                <p><?php echo $entry->getBody() ?></p>
            <?php endforeach; ?>
        <?php $view['slots']->stop() ?>

.. note::

   The parent template is identified by a special string syntax
   (``::base.html.twig``) that indicates that the template lives in the
   ``app/Resources/views`` directory of the project. This naming convention is
   explained fully in :ref:`template-naming-locations`.

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
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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

You can use as many levels of inheritance as you want. In the next section,
a common three-level inheritance model will be explained along with how templates
are organized inside a Symfony2 project.

When working with template inheritance, here are some tips to keep in mind:

* If you use ``{% extends %}`` in a template, it must be the first tag in
  that template.

* The more ``{% block %}`` tags you have in your base templates, the better.
  Remember, child templates don't have to define all parent blocks, so create
  as many blocks in your base templates as you want and give each a sensible
  default. The more blocks your base templates have, the more flexible your
  layout will be.

* If you find yourself duplicating content in a number of templates, it probably
  means you should move that content to a ``{% block %}`` in a parent template.
  In some cases, a better solution may be to move the content to a new template
  and ``include`` it (see :ref:`including-templates`).

* If you need to get the content of a block from the parent template, you
  can use the ``{{ parent() }}`` function. This is useful if you want to add
  to the contents of a parent block instead of completely overriding it:

    .. code-block:: html+jinja

        {% block sidebar %}
            <h3>Table of Contents</h3>
            ...
            {{ parent() }}
        {% endblock %}

.. index::
   single: Templating; Naming Conventions
   single: Templating; File Locations

.. _template-naming-locations:

Template Naming and Locations
-----------------------------

By default, templates can live in two different locations:

* ``app/Resources/views/``: The applications ``views`` directory can contain
  application-wide base templates (i.e. your application's layouts) as well as
  templates that override bundle templates (see
  :ref:`overriding-bundle-templates`);

* ``path/to/bundle/Resources/views/``: Each bundle houses its templates in its
  ``Resources/views`` directory (and subdirectories). The majority of templates
  will live inside a bundle.

Symfony2 uses a **bundle**:**controller**:**template** string syntax for
templates. This allows for several different types of templates, each which
lives in a specific location:

* ``AcmeBlogBundle:Blog:index.html.twig``: This syntax is used to specify a
  template for a specific page. The three parts of the string, each separated
  by a colon (``:``), mean the following:

    * ``AcmeBlogBundle``: (*bundle*) the template lives inside the
      ``AcmeBlogBundle`` (e.g. ``src/Acme/BlogBundle``);

    * ``Blog``: (*controller*) indicates that the template lives inside the
      ``Blog`` subdirectory of ``Resources/views``;

    * ``index.html.twig``: (*template*) the actual name of the file is
      ``index.html.twig``.

  Assuming that the ``AcmeBlogBundle`` lives at ``src/Acme/BlogBundle``, the
  final path to the layout would be ``src/Acme/BlogBundle/Resources/views/Blog/index.html.twig``.

* ``AcmeBlogBundle::layout.html.twig``: This syntax refers to a base template
  that's specific to the ``AcmeBlogBundle``. Since the middle, "controller",
  portion is missing (e.g. ``Blog``), the template lives at
  ``Resources/views/layout.html.twig`` inside ``AcmeBlogBundle``.

* ``::base.html.twig``: This syntax refers to an application-wide base template
  or layout. Notice that the string begins with two colons (``::``), meaning
  that both the *bundle* and *controller* portions are missing. This means
  that the template is not located in any bundle, but instead in the root
  ``app/Resources/views/`` directory.

In the :ref:`overriding-bundle-templates` section, you'll find out how each
template living inside the ``AcmeBlogBundle``, for example, can be overridden
by placing a template of the same name in the ``app/Resources/AcmeBlogBundle/views/``
directory. This gives the power to override templates from any vendor bundle.

.. tip::

    Hopefully the template naming syntax looks familiar - it's the same naming
    convention used to refer to :ref:`controller-string-syntax`.

Template Suffix
~~~~~~~~~~~~~~~

The **bundle**:**controller**:**template** format of each template specifies
*where* the template file is located. Every template name also has two extensions
that specify the *format* and *engine* for that template.

* **AcmeBlogBundle:Blog:index.html.twig** - HTML format, Twig engine

* **AcmeBlogBundle:Blog:index.html.php** - HTML format, PHP engine

* **AcmeBlogBundle:Blog:index.css.twig** - CSS format, Twig engine

By default, any Symfony2 template can be written in either Twig or PHP, and
the last part of the extension (e.g. ``.twig`` or ``.php``) specifies which
of these two *engines* should be used. The first part of the extension,
(e.g. ``.html``, ``.css``, etc) is the final format that the template will
generate. Unlike the engine, which determines how Symfony2 parses the template,
this is simply an organizational tactic used in case the same resource needs
to be rendered as HTML (``index.html.twig``), XML (``index.xml.twig``),
or any other format. For more information, read the :ref:`template-formats`
section.

.. note::

   The available "engines" can be configured and even new engines added.
   See :ref:`Templating Configuration<template-configuration>` for more details.

.. index::
   single: Templating; Tags and Helpers
   single: Templating; Helpers

Tags and Helpers
----------------

You already understand the basics of templates, how they're named and how
to use template inheritance. The hardest parts are already behind you. In
this section, you'll learn about a large group of tools available to help
perform the most common template tasks such as including other templates,
linking to pages and including images.

Symfony2 comes bundled with several specialized Twig tags and functions that
ease the work of the template designer. In PHP, the templating system provides
an extensible *helper* system that provides useful features in a template
context.

We've already seen a few built-in Twig tags (``{% block %}`` & ``{% extends %}``)
as well as an example of a PHP helper (``$view['slots']``). Let's learn a
few more.

.. index::
   single: Templating; Including other templates

.. _including-templates:

Including other Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

You'll often want to include the same template or code fragment on several
different pages. For example, in an application with "news articles", the
template code displaying an article might be used on the article detail page,
on a page displaying the most popular articles, or in a list of the latest
articles.

When you need to reuse a chunk of PHP code, you typically move the code to
a new PHP class or function. The same is true for templates. By moving the
reused template code into its own template, it can be included from any other
template. First, create the template that you'll need to reuse.

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/ArticleBundle/Resources/views/Article/articleDetails.html.twig #}
        <h2>{{ article.title }}</h2>
        <h3 class="byline">by {{ article.authorName }}</h3>

        <p>
            {{ article.body }}
        </p>

    .. code-block:: html+php

        <!-- src/Acme/ArticleBundle/Resources/views/Article/articleDetails.html.php -->
        <h2><?php echo $article->getTitle() ?></h2>
        <h3 class="byline">by <?php echo $article->getAuthorName() ?></h3>

        <p>
            <?php echo $article->getBody() ?>
        </p>

Including this template from any other template is simple:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/ArticleBundle/Resources/Article/list.html.twig #}
        {% extends 'AcmeArticleBundle::layout.html.twig' %}

        {% block body %}
            <h1>Recent Articles<h1>

            {% for article in articles %}
                {% include 'AcmeArticleBundle:Article:articleDetails.html.twig' with {'article': article} %}
            {% endfor %}
        {% endblock %}

    .. code-block:: html+php

        <!-- src/Acme/ArticleBundle/Resources/Article/list.html.php -->
        <?php $view->extend('AcmeArticleBundle::layout.html.php') ?>

        <?php $view['slots']->start('body') ?>
            <h1>Recent Articles</h1>

            <?php foreach ($articles as $article): ?>
                <?php echo $view->render('AcmeArticleBundle:Article:articleDetails.html.php', array('article' => $article)) ?>
            <?php endforeach; ?>
        <?php $view['slots']->stop() ?>

The template is included using the ``{% include %}`` tag. Notice that the
template name follows the same typical convention. The ``articleDetails.html.twig``
template uses an ``article`` variable. This is passed in by the ``list.html.twig``
template using the ``with`` command.

.. tip::

    The ``{'article': article}`` syntax is the standard Twig syntax for hash
    maps (i.e. an array with named keys). If we needed to pass in multiple
    elements, it would look like this: ``{'foo': foo, 'bar': bar}``.

.. index::
   single: Templating; Embedding action

.. _templating-embedding-controller:

Embedding Controllers
~~~~~~~~~~~~~~~~~~~~~

In some cases, you need to do more than include a simple template. Suppose
you have a sidebar in your layout that contains the three most recent articles.
Retrieving the three articles may include querying the database or performing
other heavy logic that can't be done from within a template.

The solution is to simply embed the result of an entire controller from your
template. First, create a controller that renders a certain number of recent
articles:

.. code-block:: php

    // src/Acme/ArticleBundle/Controller/ArticleController.php

    class ArticleController extends Controller
    {
        public function recentArticlesAction($max = 3)
        {
            // make a database call or other logic to get the "$max" most recent articles
            $articles = ...;

            return $this->render('AcmeArticleBundle:Article:recentList.html.twig', array('articles' => $articles));
        }
    }

The ``recentList`` template is perfectly straightforward:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/ArticleBundle/Resources/views/Article/recentList.html.twig #}
        {% for article in articles %}
            <a href="/article/{{ article.slug }}">
                {{ article.title }}
            </a>
        {% endfor %}

    .. code-block:: html+php

        <!-- src/Acme/ArticleBundle/Resources/views/Article/recentList.html.php -->
        <?php foreach ($articles as $article): ?>
            <a href="/article/<?php echo $article->getSlug() ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach; ?>

.. note::

    Notice that we've cheated and hardcoded the article URL in this example
    (e.g. ``/article/*slug*``). This is a bad practice. In the next section,
    you'll learn how to do this correctly.

To include the controller, you'll need to refer to it using the standard string
syntax for controllers (i.e. **bundle**:**controller**:**action**):

.. configuration-block::

    .. code-block:: html+jinja

        {# app/Resources/views/base.html.twig #}
        ...

        <div id="sidebar">
            {% render "AcmeArticleBundle:Article:recentArticles" with {'max': 3} %}
        </div>

    .. code-block:: html+php

        <!-- app/Resources/views/base.html.php -->
        ...

        <div id="sidebar">
            <?php echo $view['actions']->render('AcmeArticleBundle:Article:recentArticles', array('max' => 3)) ?>
        </div>

Whenever you find that you need a variable or a piece of information that
you don't have access to in a template, consider rendering a controller.
Controllers are fast to execute and promote good code organization and reuse.

.. index::
   single: Templating; Linking to pages

Linking to Pages
~~~~~~~~~~~~~~~~

Creating links to other pages in your application is one of the most common
jobs for a template. Instead of hardcoding URLs in templates, use the ``path``
Twig function (or the ``router`` helper in PHP) to generate URLs based on
the routing configuration. Later, if you want to modify the URL of a particular
page, all you'll need to do is change the routing configuration; the templates
will automatically generate the new URL.

First, link to the "_welcome" page, which is accessible via the following routing
configuration:

.. configuration-block::

    .. code-block:: yaml

        _welcome:
            pattern:  /
            defaults: { _controller: AcmeDemoBundle:Welcome:index }

    .. code-block:: xml

        <route id="_welcome" pattern="/">
            <default key="_controller">AcmeDemoBundle:Welcome:index</default>
        </route>

    .. code-block:: php

        $collection = new RouteCollection();
        $collection->add('_welcome', new Route('/', array(
            '_controller' => 'AcmeDemoBundle:Welcome:index',
        )));

        return $collection;

To link to the page, just use the ``path`` Twig function and refer to the route:

.. configuration-block::

    .. code-block:: html+jinja

        <a href="{{ path('_welcome') }}">Home</a>

    .. code-block:: html+php

        <a href="<?php echo $view['router']->generate('_welcome') ?>">Home</a>

As expected, this will generate the URL ``/``. Let's see how this works with
a more complicated route:

.. configuration-block::

    .. code-block:: yaml

        article_show:
            pattern:  /article/{slug}
            defaults: { _controller: AcmeArticleBundle:Article:show }

    .. code-block:: xml

        <route id="article_show" pattern="/article/{slug}">
            <default key="_controller">AcmeArticleBundle:Article:show</default>
        </route>

    .. code-block:: php

        $collection = new RouteCollection();
        $collection->add('article_show', new Route('/article/{slug}', array(
            '_controller' => 'AcmeArticleBundle:Article:show',
        )));

        return $collection;

In this case, you need to specify both the route name (``article_show``) and
a value for the ``{slug}`` parameter. Using this route, let's revisit the
``recentList`` template from the previous section and link to the articles
correctly:

.. configuration-block::

    .. code-block:: html+jinja

        {# src/Acme/ArticleBundle/Resources/views/Article/recentList.html.twig #}
        {% for article in articles %}
            <a href="{{ path('article_show', { 'slug': article.slug }) }}">
                {{ article.title }}
            </a>
        {% endfor %}

    .. code-block:: html+php

        <!-- src/Acme/ArticleBundle/Resources/views/Article/recentList.html.php -->
        <?php foreach ($articles in $article): ?>
            <a href="<?php echo $view['router']->generate('article_show', array('slug' => $article->getSlug()) ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach; ?>

.. tip::

    You can also generate an absolute URL by using the ``url`` Twig function:

    .. code-block:: html+jinja

        <a href="{{ url('_welcome') }}">Home</a>

    The same can be done in PHP templates by passing a third argument to
    the ``generate()`` method:

    .. code-block:: html+php

        <a href="<?php echo $view['router']->generate('_welcome', array(), true) ?>">Home</a>

.. index::
   single: Templating; Linking to assets

Linking to Assets
~~~~~~~~~~~~~~~~~

Templates also commonly refer to images, Javascript, stylesheets and other
assets. Of course you could hard-code the path to these assets (e.g. ``/images/logo.png``),
but Symfony2 provides a more dynamic option via the ``assets`` Twig function:

.. configuration-block::

    .. code-block:: html+jinja

        <img src="{{ asset('images/logo.png') }}" alt="Symfony!" />

        <link href="{{ asset('css/blog.css') }}" rel="stylesheet" type="text/css" />

    .. code-block:: html+php

        <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>" alt="Symfony!" />

        <link href="<?php echo $view['assets']->getUrl('css/blog.css') ?>" rel="stylesheet" type="text/css" />

The ``asset`` function's main purpose is to make your application more portable.
If your application lives at the root of your host (e.g. http://example.com),
then the rendered paths should be ``/images/logo.png``. But if your application
lives in a subdirectory (e.g. http://example.com/my_app), each asset path
should render with the subdirectory (e.g. ``/my_app/images/logo.png``). The
``asset`` function takes care of this by determining how your application is
being used and generating the correct paths accordingly.

Additionally, if you use the ``asset`` function, Symfony can automatically
append a query string to your asset, in order to guarantee that updated static
assets won't be cached when deployed. For example, ``/images/logo.png`` might
look like ``/images/logo.png?v2``. For more information, see the :ref:`ref-framework-assets-version`
configuration option.

.. index::
   single: Templating; Including stylesheets and Javascripts
   single: Stylesheets; Including stylesheets
   single: Javascripts; Including Javascripts

Including Stylesheets and Javascripts in Twig
---------------------------------------------

No site would be complete without including Javascript files and stylesheets.
In Symfony, the inclusion of these assets is handled elegantly by taking
advantage of Symfony's template inheritance.

.. tip::

    This section will teach you the philosophy behind including stylesheet
    and Javascript assets in Symfony. Symfony also packages another library,
    called Assetic, which follows this philosophy but allows you to do much
    more interesting things with those assets. For more information on 
    using Assetic see :doc:`/cookbook/assetic/asset_management`.


Start by adding two blocks to your base template that will hold your assets:
one called ``stylesheets`` inside the ``head`` tag and another called ``javascripts``
just above the closing ``body`` tag. These blocks will contain all of the
stylesheets and Javascripts that you'll need throughout your site:

.. code-block:: html+jinja

    {# 'app/Resources/views/base.html.twig' #}
    <html>
        <head>
            {# ... #}

            {% block stylesheets %}
                <link href="{{ asset('/css/main.css') }}" type="text/css" rel="stylesheet" />
            {% endblock %}
        </head>
        <body>
            {# ... #}

            {% block javascripts %}
                <script src="{{ asset('/js/main.js') }}" type="text/javascript"></script>
            {% endblock %}
        </body>
    </html>

That's easy enough! But what if you need to include an extra stylesheet or
Javascript from a child template? For example, suppose you have a contact
page and you need to include a ``contact.css`` stylesheet *just* on that
page. From inside that contact page's template, do the following:

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Contact/contact.html.twig #}
    {% extends '::base.html.twig' %}

    {% block stylesheets %}
        {{ parent() }}
        
        <link href="{{ asset('/css/contact.css') }}" type="text/css" rel="stylesheet" />
    {% endblock %}
    
    {# ... #}

In the child template, you simply override the ``stylesheets`` block and 
put your new stylesheet tag inside of that block. Of course, since you want
to add to the parent block's content (and not actually *replace* it), you
should use the ``parent()`` Twig function to include everything from the ``stylesheets``
block of the base template.

You can also include assets located in your bundles' ``Resources/public`` folder.
You will need to run the ``php app/console assets:install target [--symlink]``
command, which moves (or symlinks) files into the correct location. (target
is by default "web").

.. code-block:: html+jinja

   <link href="{{ asset('bundles/acmedemo/css/contact.css') }}" type="text/css" rel="stylesheet" />

The end result is a page that includes both the ``main.css`` and ``contact.css``
stylesheets.

Global Template Variables
-------------------------

During each request, Symfony2 will set a global template variable ``app``
in both Twig and PHP template engines by default.  The ``app`` variable
is a :class:`Symfony\\Bundle\\FrameworkBundle\\Templating\\GlobalVariables`
instance which will give you access to some application specific variables
automatically:

* ``app.security`` - The security context.
* ``app.user`` - The current user object.
* ``app.request`` - The request object.
* ``app.session`` - The session object.
* ``app.environment`` - The current environment (dev, prod, etc).
* ``app.debug`` - True if in debug mode. False otherwise.

.. configuration-block::

    .. code-block:: html+jinja

        <p>Username: {{ app.user.username }}</p>
        {% if app.debug %}
            <p>Request method: {{ app.request.method }}</p>
            <p>Application Environment: {{ app.environment }}</p>
        {% endif %}

    .. code-block:: html+php

        <p>Username: <?php echo $app->getUser()->getUsername() ?></p>
        <?php if ($app->getDebug()): ?>
            <p>Request method: <?php echo $app->getRequest()->getMethod() ?></p>
            <p>Application Environment: <?php echo $app->getEnvironment() ?></p>
        <?php endif; ?>

.. tip::

    You can add your own global template variables. See the cookbook example
    on :doc:`Global Variables</cookbook/templating/global_variables>`.

.. index::
   single: Templating; The templating service

Configuring and using the ``templating`` Service
------------------------------------------------

The heart of the template system in Symfony2 is the templating ``Engine``.
This special object is responsible for rendering templates and returning
their content. When you render a template in a controller, for example,
you're actually using the templating engine service. For example:

.. code-block:: php

    return $this->render('AcmeArticleBundle:Article:index.html.twig');

is equivalent to

.. code-block:: php

    $engine = $this->container->get('templating');
    $content = $engine->render('AcmeArticleBundle:Article:index.html.twig');

    return $response = new Response($content);

.. _template-configuration:

The templating engine (or "service") is preconfigured to work automatically
inside Symfony2. It can, of course, be configured further in the application
configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating: { engines: ['twig'] }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:templating>
            <framework:engine id="twig" />
        </framework:templating>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'templating'      => array(
                'engines' => array('twig'),
            ),
        ));

Several configuration options are available and are covered in the
:doc:`Configuration Appendix</reference/configuration/framework>`.

.. note::

   The ``twig`` engine is mandatory to use the webprofiler (as well as many
   third-party bundles).

.. index::
    single; Template; Overriding templates

.. _overriding-bundle-templates:

Overriding Bundle Templates
---------------------------

The Symfony2 community prides itself on creating and maintaining high quality
bundles (see `KnpBundles.com`_) for a large number of different features.
Once you use a third-party bundle, you'll likely need to override and customize
one or more of its templates.

Suppose you've included the imaginary open-source ``AcmeBlogBundle`` in your
project (e.g. in the ``src/Acme/BlogBundle`` directory). And while you're
really happy with everything, you want to override the blog "list" page to
customize the markup specifically for your application. By digging into the
``Blog`` controller of the ``AcmeBlogBundle``, you find the following::

    public function indexAction()
    {
        $blogs = // some logic to retrieve the blogs

        $this->render('AcmeBlogBundle:Blog:index.html.twig', array('blogs' => $blogs));
    }

When the ``AcmeBlogBundle:Blog:index.html.twig`` is rendered, Symfony2 actually
looks in two different locations for the template:

#. ``app/Resources/AcmeBlogBundle/views/Blog/index.html.twig``
#. ``src/Acme/BlogBundle/Resources/views/Blog/index.html.twig``

To override the bundle template, just copy the ``index.html.twig`` template
from the bundle to ``app/Resources/AcmeBlogBundle/views/Blog/index.html.twig``
(the ``app/Resources/AcmeBlogBundle`` directory won't exist, so you'll need
to create it). You're now free to customize the template.

This logic also applies to base bundle templates. Suppose also that each
template in ``AcmeBlogBundle`` inherits from a base template called
``AcmeBlogBundle::layout.html.twig``. Just as before, Symfony2 will look in
the following two places for the template:

#. ``app/Resources/AcmeBlogBundle/views/layout.html.twig``
#. ``src/Acme/BlogBundle/Resources/views/layout.html.twig``

Once again, to override the template, just copy it from the bundle to
``app/Resources/AcmeBlogBundle/views/layout.html.twig``. You're now free to
customize this copy as you see fit.

If you take a step back, you'll see that Symfony2 always starts by looking in
the ``app/Resources/{BUNDLE_NAME}/views/`` directory for a template. If the
template doesn't exist there, it continues by checking inside the
``Resources/views`` directory of the bundle itself. This means that all bundle
templates can be overridden by placing them in the correct ``app/Resources``
subdirectory.

.. _templating-overriding-core-templates:

.. index::
    single; Template; Overriding exception templates

Overriding Core Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

Since the Symfony2 framework itself is just a bundle, core templates can be
overridden in the same way. For example, the core ``TwigBundle`` contains
a number of different "exception" and "error" templates that can be overridden
by copying each from the ``Resources/views/Exception`` directory of the
``TwigBundle`` to, you guessed it, the
``app/Resources/TwigBundle/views/Exception`` directory.

.. index::
   single: Templating; Three-level inheritance pattern

Three-level Inheritance
-----------------------

One common way to use inheritance is to use a three-level approach. This
method works perfectly with the three different types of templates we've just
covered:

* Create a ``app/Resources/views/base.html.twig`` file that contains the main
  layout for your application (like in the previous example). Internally, this
  template is called ``::base.html.twig``;

* Create a template for each "section" of your site. For example, an ``AcmeBlogBundle``,
  would have a template called ``AcmeBlogBundle::layout.html.twig`` that contains
  only blog section-specific elements;

    .. code-block:: html+jinja

        {# src/Acme/BlogBundle/Resources/views/layout.html.twig #}
        {% extends '::base.html.twig' %}

        {% block body %}
            <h1>Blog Application</h1>

            {% block content %}{% endblock %}
        {% endblock %}

* Create individual templates for each page and make each extend the appropriate
  section template. For example, the "index" page would be called something
  close to ``AcmeBlogBundle:Blog:index.html.twig`` and list the actual blog posts.

    .. code-block:: html+jinja

        {# src/Acme/BlogBundle/Resources/views/Blog/index.html.twig #}
        {% extends 'AcmeBlogBundle::layout.html.twig' %}

        {% block content %}
            {% for entry in blog_entries %}
                <h2>{{ entry.title }}</h2>
                <p>{{ entry.body }}</p>
            {% endfor %}
        {% endblock %}

Notice that this template extends the section template -(``AcmeBlogBundle::layout.html.twig``)
which in-turn extends the base application layout (``::base.html.twig``).
This is the common three-level inheritance model.

When building your application, you may choose to follow this method or simply
make each page template extend the base application template directly
(e.g. ``{% extends '::base.html.twig' %}``). The three-template model is
a best-practice method used by vendor bundles so that the base template for
a bundle can be easily overridden to properly extend your application's base
layout.

.. index::
   single: Templating; Output escaping

Output Escaping
---------------

When generating HTML from a template, there is always a risk that a template
variable may output unintended HTML or dangerous client-side code. The result
is that dynamic content could break the HTML of the resulting page or allow
a malicious user to perform a `Cross Site Scripting`_ (XSS) attack. Consider
this classic example:

.. configuration-block::

    .. code-block:: jinja

        Hello {{ name }}

    .. code-block:: html+php

        Hello <?php echo $name ?>

Imagine that the user enters the following code as his/her name::

    <script>alert('hello!')</script>

Without any output escaping, the resulting template will cause a JavaScript
alert box to pop up::

    Hello <script>alert('hello!')</script>

And while this seems harmless, if a user can get this far, that same user
should also be able to write JavaScript that performs malicious actions
inside the secure area of an unknowing, legitimate user.

The answer to the problem is output escaping. With output escaping on, the
same template will render harmlessly, and literally print the ``script``
tag to the screen::

    Hello &lt;script&gt;alert(&#39;helloe&#39;)&lt;/script&gt;

The Twig and PHP templating systems approach the problem in different ways.
If you're using Twig, output escaping is on by default and you're protected.
In PHP, output escaping is not automatic, meaning you'll need to manually
escape where necessary.

Output Escaping in Twig
~~~~~~~~~~~~~~~~~~~~~~~

If you're using Twig templates, then output escaping is on by default. This
means that you're protected out-of-the-box from the unintentional consequences
of user-submitted code. By default, the output escaping assumes that content
is being escaped for HTML output.

In some cases, you'll need to disable output escaping when you're rendering
a variable that is trusted and contains markup that should not be escaped.
Suppose that administrative users are able to write articles that contain
HTML code. By default, Twig will escape the article body. To render it normally,
add the ``raw`` filter: ``{{ article.body|raw }}``.

You can also disable output escaping inside a ``{% block %}`` area or
for an entire template. For more information, see `Output Escaping`_ in
the Twig documentation.

Output Escaping in PHP
~~~~~~~~~~~~~~~~~~~~~~

Output escaping is not automatic when using PHP templates. This means that
unless you explicitly choose to escape a variable, you're not protected. To
use output escaping, use the special ``escape()`` view method::

    Hello <?php echo $view->escape($name) ?>

By default, the ``escape()`` method assumes that the variable is being rendered
within an HTML context (and thus the variable is escaped to be safe for HTML).
The second argument lets you change the context. For example, to output something
in a JavaScript string, use the ``js`` context:

.. code-block:: js

    var myMsg = 'Hello <?php echo $view->escape($name, 'js') ?>';

.. index::
   single: Templating; Formats

.. _template-formats:

Debugging
---------

.. versionadded:: 2.0.9
    This feature is available as of Twig ``1.5.x``, which was first shipped
    with Symfony 2.0.9.

When using PHP, you can use ``var_dump()`` if you need to quickly find the
value of a variable passed. This is useful, for example, inside your controller.
The same can be achieved when using Twig by using the debug extension. This
needs to be enabled in the config:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            acme_hello.twig.extension.debug:
                class:        Twig_Extension_Debug
                tags:
                     - { name: 'twig.extension' }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <services>
            <service id="acme_hello.twig.extension.debug" class="Twig_Extension_Debug">
                <tag name="twig.extension" />
            </service>
        </services>

    .. code-block:: php

        // app/config/config.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('Twig_Extension_Debug');
        $definition->addTag('twig.extension');
        $container->setDefinition('acme_hello.twig.extension.debug', $definition);

Template parameters can then be dumped using the ``dump`` function:

.. code-block:: html+jinja

    {# src/Acme/ArticleBundle/Resources/views/Article/recentList.html.twig #}

    {{ dump(articles) }}

    {% for article in articles %}
        <a href="/article/{{ article.slug }}">
            {{ article.title }}
        </a>
    {% endfor %}


The variables will only be dumped if Twig's ``debug`` setting (in ``config.yml``)
is ``true``. By default this means that the variables will be dumped in the
``dev`` environment but not the ``prod`` environment.

Template Formats
----------------

Templates are a generic way to render content in *any* format. And while in
most cases you'll use templates to render HTML content, a template can just
as easily generate JavaScript, CSS, XML or any other format you can dream of.

For example, the same "resource" is often rendered in several different formats.
To render an article index page in XML, simply include the format in the
template name:

* *XML template name*: ``AcmeArticleBundle:Article:index.xml.twig``
* *XML template filename*: ``index.xml.twig``

In reality, this is nothing more than a naming convention and the template
isn't actually rendered differently based on its format.

In many cases, you may want to allow a single controller to render multiple
different formats based on the "request format". For that reason, a common
pattern is to do the following:

.. code-block:: php

    public function indexAction()
    {
        $format = $this->getRequest()->getRequestFormat();
    
        return $this->render('AcmeBlogBundle:Blog:index.'.$format.'.twig');
    }

The ``getRequestFormat`` on the ``Request`` object defaults to ``html``,
but can return any other format based on the format requested by the user.
The request format is most often managed by the routing, where a route can
be configured so that ``/contact`` sets the request format to ``html`` while
``/contact.xml`` sets the format to ``xml``. For more information, see the
:ref:`Advanced Example in the Routing chapter <advanced-routing-example>`.

To create links that include the format parameter, include a ``_format``
key in the parameter hash:

.. configuration-block::

    .. code-block:: html+jinja

        <a href="{{ path('article_show', {'id': 123, '_format': 'pdf'}) }}">
            PDF Version
        </a>

    .. code-block:: html+php

        <a href="<?php echo $view['router']->generate('article_show', array('id' => 123, '_format' => 'pdf')) ?>">
            PDF Version
        </a>

Final Thoughts
--------------

The templating engine in Symfony is a powerful tool that can be used each time
you need to generate presentational content in HTML, XML or any other format.
And though templates are a common way to generate content in a controller,
their use is not mandatory. The ``Response`` object returned by a controller
can be created with our without the use of a template:

.. code-block:: php

    // creates a Response object whose content is the rendered template
    $response = $this->render('AcmeArticleBundle:Article:index.html.twig');

    // creates a Response object whose content is simple text
    $response = new Response('response content');

Symfony's templating engine is very flexible and two different template
renderers are available by default: the traditional *PHP* templates and the
sleek and powerful *Twig* templates. Both support a template hierarchy and
come packaged with a rich set of helper functions capable of performing
the most common tasks.

Overall, the topic of templating should be thought of as a powerful tool
that's at your disposal. In some cases, you may not need to render a template,
and in Symfony2, that's absolutely fine.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/templating/PHP`
* :doc:`/cookbook/controller/error_pages`

.. _`Twig`: http://twig.sensiolabs.org
.. _`KnpBundles.com`: http://knpbundles.com
.. _`Cross Site Scripting`: http://en.wikipedia.org/wiki/Cross-site_scripting
.. _`Output Escaping`: http://twig.sensiolabs.org/doc/api.html#escaper-extension
.. _`tags`: http://twig.sensiolabs.org/doc/tags/index.html
.. _`filters`: http://twig.sensiolabs.org/doc/filters/index.html
.. _`add your own extensions`: http://twig.sensiolabs.org/doc/extensions.html
