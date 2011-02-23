.. index::
   single: Templating

Creating and using Templates
============================

As you know, the :doc:`controller </guides/controller>` is responsible for
handling each request that comes into your Symfony application. In reality,
the controller delegates the most of the heavy work to other places so that
code can be tested and reused. When a controller needs to generate HTML,
CSS or any other content, it hands the work of to the templating engine.
In this guide, we'll learn how to write powerful templates that can be
used to return content to the user, populate email bodies, and more. You'll
learn shortcuts, clever ways to extend templates and how to reuse template
code.

.. index::
   single: Templating; What is a template

Templates
---------

A template is simply a text file that can generate any text-based format
(HTML, XML, CSV, LaTeX ...). The most familiar type of template is a *PHP*
template - a text file parsed by PHP that contains a mix of text and PHP code::

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

But Symfony2 packages an even more powerful templating language called `Twig`_
Twig allows you to write concise, readable templates that are more friendly
to web designers:

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

Twig contains defines two types of special syntax:

* ``{{ ... }}``: "Says something": prints a variable or the result of an
  expression to the template;

* ``{% ... %}``: "Does something": a **tag** that controls the logic of the
  template; it is used to execute statements such as for-loops for example.

.. note::

   There is a third syntax used for creating comments: ``{# this is a comment #}``.
   This syntax can even be used across lines like the PHP-equivalent ``/* comment */``
   syntax.

Twig also contains **filters**, which modify content before being rendered.
The following would call ``strtoupper()`` on the ``title`` variable before
rendering it:

.. code-block:: jinja

    {{ title | upper }}

Twig comes with a long list of `tags`_ and `filters`_ that are available
by default. You can even `add your own extensions`_ to Twig as needed.

As you'll see throughout the documentation, Twig also supports functions
and new functions can be easily added. For example, the following uses a
standard ``if`` tag and the ``cycle`` function to print ten div tags, with
alternating ``odd``, ``even`` classes:

.. code-block:: jinja

    {% for i in 0..10 %}
      <div class="{{ cycle(['odd', 'even'], i) }}">
        <!-- some HTML here -->
      </div>
    {% endfor %}

Throughout this guide, template examples will be shown in both Twig and PHP.

.. sidebar:: Why Twig?

    Twig templates are meant to be simple and won't process PHP tags. This
    is by design: the Twig template system is meant to express presentation,
    not program logic. The more you use Twig, the more you'll appreciate
    and benefit from this distinction. And of course, you'll be loved by
    web designers everywhere.
    
    Twig can also do things that PHP can't, such as true template inheritance
    (Twig templates compiled down to PHP classes that extend from each other),
    whitespace control, sandboxing, and the inclusion of custom functions
    and filters that only affect templates. Twig contains little features
    that make writing templates easier and more concise. Take the following
    example from the Twig documentation, which combines a loop with a logical
    ``if`` statement:
    
    .. code-block:: jinja
    
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

And Twig is fast. Each Twig template is compiled down to a native PHP class
that is rendered at runtime. The compiled classes are located in the
``app/cache/{environment}/twig`` directory (where ``{environment}`` is the
environment, such as ``dev`` or ``prod``) and in some cases can be useful
while debugging. See :ref:`environments-summary` for more information on
environments.

When ``debug`` mode is enabled (common in the ``dev`` environment) a Twig
template will be automatically recompiled when changes are made to it. This
means that during development you can happily make changes to a Twig template
and instantly see its changes without needing to worry about clearing any
cache.

When ``debug`` mode is disabled (common in the ``prod`` environment), however,
you must clear the Twig cache directory so that the Twig templates will
regenerate. Remember to do this when deploying your application.

.. index::
   single: Templating; Inheritance

Template Inheritance and Layouts
--------------------------------

More often than not, templates in a project share common elements, like the
header, footer, sidebar or more. In Symfony, we like to think about this
problem differently: a template can be decorated by another one. This works
exactly the same as PHP classes: template inheritance allows you to build
a base "layout" template that contains all the common elements of your site
defined as **blocks** (think "PHP class with base methods"). A child template
can extend the base layout and override any of its blocks.

First, let's build a base layout file:

.. configuration-block::

    .. code-block:: jinja

        {# app/views/layout.html.twig #}
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

    .. code-block:: php

        <!-- app/views/layout.html.php -->
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php echo $view['slots']->output('title', 'Test Application') ?></title>
            </head>
            <body>
                <div id="sidebar">
                    <?php if ($view['slots']->has('sidebar'): ?>
                        <?php echo $view['slots']->output('sidebar') ?>
                    <?php else: ?>
                        <ul>
                            <li><a href="/">Home</a></li>
                            <li><a href="/blog">Blog</a></li>
                        </ul>
                    <?php endif; ?>
                </div>

                <div id="content">
                    <?php echo $view['slots']->output('body') ?>
                </div>
            </body>
        </html>

.. note::

    Though we'll talk about template inheritance in terms of Twig, the philosophy
    is the same between Twig and PHP templates.

This template defines the base HTML skeleton document of a simple two-column
page. In this example, three ``{% block %}`` areas are defined (``title``,
``sidebar`` and ``body``). Each block may be overridden by a child template
or left with its default implementation. This template could also be rendered
directly. In that case the ``title``, ``sidebar`` and ``body`` blocks would
simply retain the default values used in this template.

A child template might look like this:

.. configuration-block::

    .. code-block:: jinja

        {# src/Sensio/BlogBundle/Resources/views/Blog/index.html.twig #}
        {% extends '::layout.html.twig' %}

        {% block title %}My cool blog posts{% endblock %}

        {% block body %}
            {% for entry in blog_entries %}
                <h2>{{ entry.title }}</h2>
                <p>{{ entry.body }}</p>
            {% endfor %}
        {% endblock %}

    .. code-block:: php

        <!-- src/Sensio/BlogBundle/Resources/views/Blog/index.html.php -->
        <?php $view->extend('::layout.html.php') ?>

        <?php $view['slots']->set('title', 'My cool blog posts') ?>

        <?php $view['slots']->start('body') ?>
            <?php foreach ($blog_entries as $entry): ?>
                <h2><?php echo $entry->getTitle() ?></h2>
                <p><?php echo $entry->getBody() ?></p>
            <?php endforeach; ?>
        <?php $view['slots']->stop() ?>

.. note::
    The parent template is identified by a special string syntax (``::layout.html.twig``)
    that indicates that the template lives in the ``app/views`` directory.
    This naming convention will be explained fully in :ref:`template-naming-locations`.

The key to template inheritance is the ``{% extends %}`` tag. This tells
the templating engine to first evaluate the base template, which sets up
the layout and defines several blocks. The child template is then rendered,
at which point the ``title`` and ``body`` blocks of the parent are replaced
by those from the child. Depending on the value of ``blog_entries``, the
output might look like this::

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
we'll go over a common three-level inheritance model and explain how templates
are organized inside a Symfony project.

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
  can use the ``{% parent %}`` tag. This is useful if you want to add to
  the contents of a parent block instead of completely overriding it:

    .. code-block:: jinja

        {% block sidebar %}
            <h3>Table of Contents</h3>
            ...
            {% parent %}
        {% endblock %}

.. index::
   single: Templating; Naming Conventions
   single: Templating; File Locations

.. _template-naming-locations:

Template Naming and Locations
-----------------------------

By default, templates can live in two different locations:

* ``app/views/`` The applications ``views`` directory can contain application-wide
  base templates (i.e. your application's layouts) as well as templates that
  override bundle templates (see `Overriding Bundle Templates`);

* ``MyBundle/Resources/views/`` Each bundle houses its templates in its
  ``Resources/views`` directory. The majority of templates will live inside
  a bundle.

Symfony uses a **bundle**:**controller**:**template** string syntax for
templates. This allows for several different types of templates, each which
live in a specific location:

* ``BlogBundle:Blog:index.html.twig``: This syntax is used to specify a template
  for a specific page. The value of the controller portion (``Blog``) indicates
  that the template will be located in the ``Resources/views/Blog`` directory
  of the ``BlogBundle``.

* ``BlogBundle::layout.html.twig``: This syntax refers to a base template that's
  specific to the ``BlogBundle``. The controller portion is absent, indicating
  that the template will be located in the ``Resources/views`` directory
  of the ``BlogBundle``.

* ``::layout.html.twig``: This syntax refers to an application-wide base template
  or layout. The *bundle* and *controller* portions are missing. This means
  that the template is not located in any bundle, but instead in the root
  ``app/views/`` directory.

In the :ref:`overiding-bundle-templates` section, you'll find out how each
bundle template can be overridden by placing a template in the ``app/views/``
directory. This gives the power to override templates from any vendor bundle.

Template Suffix
~~~~~~~~~~~~~~~

The **bundle**:**controller**:**template** format of each template specifies
*where*  the template file is located. Every template name also has two extensions
that specify the *format* and *renderer* for that template.

* **BlogBundle:Blog:index.html.twig** - HTML format, Twig renderer

* **BlogBundle:Blog:index.html.php** - HTML format, PHP renderer

* **BlogBundle:Blog:index.css.twig** - CSS format, Twig renderer

By default, Symfony2 templates can be written in either Twig or PHP, and
the first part of the extension specifies which of these two *renderers*
should be used. The second part of the extension, (e.g. HTML, CSS, etc)
is the end format that the template will generate. Unlike the renderer, this
is simply an organizational tactic in case the same content every needs to
be rendered as HTML (index.html.twig), XML (index.xml.twig), or any other
format. For more information, read the :ref:`template-formats` section.

.. tip::

    Hopefully the template naming syntax looks familiar - it's the same naming
    convention used to refer to `controllers </en/controllers>`.

.. tip::

    Recall that a particular bundle could live in one of several different
    places. For example, the ``BlogBundle`` could actually live at
    ``src/Sensio/BlogBundle``, ``src/Bundle/VendorName/BlogBundle``,
    or some other location. So, the true location of a template called
    ``BlogBundle:Blog:index.html.twig`` depends on the location of ``BlogBundle``.

.. index::
   single: Templating; Tags and Helpers
   single: Templating; Helpers

Tags and Helpers
----------------

You already understand the basics of templates, how they're named and how
to use template inheritance. The hardest parts are already behind us. In
this section, we'll talk about a large group of tools available to help
perform the most common templat tasks such as including other templates,
linking to pages and including images.

Symfony2 comes bundled with several specialized Twig tags and functions that
ease the work of the template designer. In PHP, the templating system provides
an extensible *helper* system that provides useful features in a template
context.

We've already seen a few built-in Twig tags (``{{ block }}`` & ``{{ extends }}``)
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
on a page displaying the most popular articles, or in the list of the latest
articles.

When you need to reuse a chunk of PHP code, you typically move the code to
a new PHP class or function. The same is true for templates. By moving the
reused template code into its own template, we can include it from any other
template. First, let's create the template that we need to reuse.

.. configuration-block::

    .. code-block:: jinja

        {# src/Sensio/ArticleBundle/Resources/Article/articleDetails.html.twig #}
        <h1>{{ article.title }}</h1>
        <h3 class="byline">by {{ article.authorName }}</h3>

        <p>
          {{ article.body }}
        </p>

    .. code-block:: php

        <!-- src/Sensio/ArticleBundle/Resources/Article/articleDetails.html.php -->
        <h2><?php echo $article->getTitle() ?></h2>
        <h3 class="byline">by <?php echo $article->getAuthorName() ?></h3>

        <p>
          <?php echo $article->getBody() ?>
        </p>

Including this template from any other template is simple:

.. configuration-block::

    .. code-block:: jinja

        {# src/Sensio/ArticleBundle/Resources/Article/list.html.twig #}
        {% extends 'ArticleBundle::layout.html.twig' %}

        {% block body %}
            <h1>Recent Articles<h1>

            {% for article in articles %}
                {% include 'ArticleBundle:Article:articleDetails.html.twig' with {'article': article} %}
            {% endfor %}
        {% endblock %}

    .. code-block:: php

        <!-- src/Sensio/ArticleBundle/Resources/Article/list.html.php -->
        <?php $view->extend('ArticleBundle::layout.html.php') ?>

        <?php $view['slots']->start('body') ?>
            <h1>Recent Articles</h1>

            <?php foreach ($articles as $article): ?>
                <?php echo $view->render('ArticleBundle:Article:articleDetails.php', array('article' => $article)) ?>
            <?php endforeach; ?>
        <?php $view['slots']->stop() ?>

The template is included using the ``{% include %}`` tag. Notice that the
template name follows the same typical convention. The ``articleDetails.html.twig``
template uses an ``article`` variable. This is passed in by the ``list.html.twig``
using the ``with`` command.

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
we have a sidebar in our layout that contains the three most recent articles.
Retrieving the three articles may include querying the database or performing
other heavy logic that can't be done from within a template.

An easy solution is to simply embed the result of an entire controller from
your template. First, let's create a controller that renders a certain number
of recent articles:

.. code-block:: php

    // src/Sensio/ArticleBundle/Controller/ArticleController.php

    class ArticleController extends Controller
    {
        public function recentArticlesAction($max = 3)
        {
            // make a database call or other logic to get the "$max" most recent articles
            $articles = ...;

            return $this->render('ArticleBundle:Article:recentList.html.twig', array('articles' => $articles));
        }
    }

The ``recentList`` template is perfectly straightforward:

.. configuration-block::

    .. code-block:: jinja

        {# src/Sensio/ArticleBundle/Resources/views/Article/recentList.html.twig #}
        {% for article in articles %}
          <a href="/article/{{ article.slug }}">
              {{ article.title }}
          </a>
        {% endfor %}

    .. code-block:: php

        <!-- src/Sensio/ArticleBundle/Resources/views/Article/recentList.html.php -->
        <?php foreach ($articles in $article): ?>
            <a href="/article/<?php echo $article->getSlug() ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach; ?>

.. note::

    Notice that we've cheated and hardcoded the article URL in this example
    (e.g. ``/article/*slug*``). This is a bad practice. In the next section,
    we'll show you how to do this right.

To include the controller, we'll need to refer to it using the standard string
syntax for controllers (i.e. **bundle**:**controller**:**action**):

.. configuration-block::

    .. code-block:: jinja

        {# app/views/layout.html.twig #}
        ...

        <div id="sidebar">
            {% render "ArticleBundle:Article:recentArticles" with {'max': 3} %}
        </div>

    .. code-block:: php

        <!-- app/views/layout.html.php -->
        ...

        <div id="sidebar">
            <?php echo $view['actions']->render('ArticleBundle:Article:recentArticles', array('max' => 3)) ?>
        </div>

Whenever you find that you need a variable or a piece of information that you
don't have access to in a template, consider rendering a controller. Controllers
are fast to execute and promote good code reuse.

.. index::
   single: Templating; Linking to pages

Linking to Pages
~~~~~~~~~~~~~~~~

Creating links to other pages in your application is one of the most common
jobs for a template. Instead of hardcoding URLs in templates, we'll use the
``path`` Twig function (or the ``router`` helper in PHP) to generate URLs
based on the routing configuration. Later, if you want to modify the URL
of a particular page, all you'll need to do is change the routing configuration;
the templates will automatically generate the new URL.

First, let's link to the "homepage", which is accessible via the following
routing configuration:

.. configuration-block::

    .. code-block:: yaml

        homepage:
            pattern:  /
            defaults: { _controller: FrameworkBundle:Default:index }

    .. code-block:: xml

        <route id="homepage" pattern="/">
            <default key="_controller">FrameworkBundle:Default:index</default>
        </route>

    .. code-block:: php

        $collection = new RouteCollection();
        $collection->add('homepage', new Route('/', array(
            '_controller' => 'FrameworkBundle:Default:index',
        )));

        return $collection;

To link to the page, just use the ``path`` Twig function and refer to the route:

.. configuration-block::

    .. code-block:: jinja

        <a href="{{ path('homepage') }}">Home</a>

    .. code-block:: php

        <a href="<?php echo $view['router']->generate('homepage') ?>">Home</a>

As expected, this will generate the URL ``/``. Let's see how this works with
a more complicated route:

.. configuration-block::

    .. code-block:: yaml

        article_show:
            pattern:  /article/{slug}
            defaults: { _controller: ArticleBundle:Article:show }

    .. code-block:: xml

        <route id="article_show" pattern="/article/{slug}">
            <default key="_controller">ArticleBundle:Article:show</default>
        </route>

    .. code-block:: php

        $collection = new RouteCollection();
        $collection->add('article_show', new Route('/article/{slug}', array(
            '_controller' => 'ArticleBundle:Article:show',
        )));

        return $collection;

In this case, we need to specify both the route name (``article_show``) and
a value for the ``{slug}`` parameter. Using this route, let's revisit the
``recentList`` template from the previous section and link to the articles
correctly:

.. configuration-block::

    .. code-block:: jinja

        {# src/Sensio/ArticleBundle/Resources/views/Article/recentList.html.twig #}
        {% for article in articles %}
          <a href="{{ path('article_show', { 'slug': article.slug }) }}">
              {{ article.title }}
          </a>
        {% endfor %}

    .. code-block:: php

        <!-- src/Sensio/ArticleBundle/Resources/views/Article/recentList.html.php -->
        <?php foreach ($articles in $article): ?>
            <a href="<?php echo $view['router']->generate('article_show', array('slug' => $article->getSlug()) ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach; ?>

.. tip::

    You can also generate an absolute URL by using the ``url`` Twig function:

    .. code-block:: jinja

        <a href="{{ url('homepage') }}">Home</a>

    The same can be done in PHP templates by passing a third argument to
    the ``generate()`` method:

    .. code-block:: php

        <a href="<?php echo $view['router']->generate('homepage', array(), true) ?>">Home</a>

.. index::
   single: Templating; Linking to assets

Linking to Assets
~~~~~~~~~~~~~~~~~

Templates also commonly refer to images, Javascript, stylesheets and other
assets. Of course you could hard-coded these the paths to these assets
(e.g. ``/images/logo.png``), but Symfony2 provides a more dynamic option
via the ``assets`` Twig function:

.. configuration-block::

    .. code-block:: jinja

        <img src="{{ asset('images/logo.png') }}" alt="Symfony!" />

        <link href="{{ asset('css/blog.css') }}" rel="stylesheet" type="text/css" />

    .. code-block:: php

        <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>" alt="Symfony!" />

        <link href="<?php echo $view['assets']->getUrl('css/blog.css') ?>" rel="stylesheet" type="text/css" />

The ``asset`` function's main purpose is to make your application more portable.
If your application lives at the root of your host (e.g. http://example.com),
then the rendered paths should be ``/images/logo.png``. But if your application
lives in a subdirectory (e.g. http://example.com/my_app), each asset path
should render with the subdirectory (e.g. ``/my_app/images/logo.png``). The
``asset`` function takes care of this by determining how your application is
being used and generating the correct paths accordingly.

.. index::
   single: Templating; The templating service

Configuring and using the ``templating`` Service
------------------------------------------------

The heart of the template system in Symfony2 is the templating ``Engine``.
This special object is responsible for rendering templates and returning
their content. When you render a template in a controller, for example,
you're actually using the templating engine service:

.. code-block:: php

    return $this->render('ArticleBundle:Article:index.html.twig');

is equivalent to

.. code-block:: php

    // get the templating engine object and then render a template
    $engine = $this->container->get('templating');
    $content = $engine->render('ArticleBundle:Article:index.html.twig');

    return $response = $this->createResponse($content);

The templating engine (or "service") is preconfigured to work automatically
inside Symfony2. It can, of course, be configured further in the application
configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        app.config:
            # ...
            templating: {}

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <app:config ...>
            <!-- ... -->
            <app:templating />
        </app:config>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('app', 'config', array(
            // ...
          'templating'    => array(),
        ));

Several configuration options are available and are covered in the
:ref:`Configuration Appendix<appendix-templating-configuration>`.

.. index::
    single; Template; Overriding templates

.. _overiding-bundle-templates:

Overriding Bundle Templates
---------------------------

One of the best features of Symfony is a bundle system that encourages the
organization of components in a way that makes them easy to reuse in other
projects or distribute as open source libraries. In fact, the Symfony community
prides itself on creating and maintaining high quality bundles for a large
number of different features. To find out more about the open source bundles
that are available, visit `Symfony2Bundles.org`_

In Symfony2, almost every part of a bundle can be overridden so that you can
use and customize it for your specific application. Templates are no exception.

Suppose you've included the imaginary open-source ``BlogBundle`` in your
project. And while you're really happy with everything, you want to override
the blog "list" page to customize the markup specifically for your application.
By digging into the ``Blog`` controller of the ``BlogBundle``, you find the
following:

.. code-block:: php

    public function indexAction()
    {
        $blogs = // some logic to retrieve the blogs

        $this->render('BlogBundle:Blog:index.html.twig', array('blogs' => $blogs));
    }

We learned in the :ref:`template-naming-locations` section that the template
in question lives at ``Resources/views/Blog/index.html.twig`` inside the
``BlogBundle``. To override the bundle template, copy the ``index.html.twig``
template to ``app/views/BlogBundle/Blog/index.html.twig`` (the ``BlogBundle``
directory might not exist). Now, when you render the ``BlogBundle:Blog:index.html.twig``
template, Symfony2 will look first for the template at ``app/views/BlogBundle/Blog/index.html.twig``
before looking inside ``BlogBundle``. You're know free to customize the template
for your application.

Suppose also that each template in ``BlogBundle`` inherits from a template
called ``BlogBundle::layout.html.twig``. By default, this template lives at
``Resources/views/layout.html.twig``. To override it, copy it to
``app/views/BlogBundle/layout.html.twig``.

If you take a step back, you'll see that Symfony always starts by looking
in the *app/views/***bundle-name**/ directory for a template. If the template
doesn't exist there, it continues by checking inside the ``Resources/views``
directory of the bundle itself. This means that all bundle templates can
be overridden by placing them in the correct ``app/views`` subdirectory.

.. _templating-overriding-core-templates:

.. index::
    single; Template; Overriding exception templates

Overriding Core Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

Since the Symfony2 framework itself is just a bundle, core templates can be
overridden in the same way. For example, the core ``FrameworkBundle`` contains
a number of different "exception" and "error" templates that can be overridden
by copying each from the ``Resources/views/Exception`` directory of the
``FrameworkBundle`` to, you guessed it, the ``app/views/FrameworkBundle/Exception``
directory.

.. index::
   single: Templating; Three-level inheritance pattern

Three-level Inheritance
-----------------------

One common way to use inheritance is to use a three-level approach. This
method works perfectly with the three different types of templates we've just
covered:

* Create a ``app/views/layout.html.twig`` file that contains the main layout
  for your application (like in the previous example). Internally, this template
  is called ``::layout.html.twig``;

* Create a template for each "section" of your site. For example, a ``BlogBundle``,
  would have a template called ``BlogBundle::layout.html.twig`` that contains
  only blog section-specific elements;

    .. code-block:: jinja

        {# src/Sensio/BlogBundle/Resources/views/layout.html.twig #}
        {% extends '::layout.html.twig' %}

        {% block body %}
            <h1>Blog Application</h1>

            {% block content %}{% endblock %}
        {% endblock %}

* Create individual templates for each page and make each extend the appropriate
  section template. For example, the "index" page would be called something
  close to ``BlogBundle:Blog:index.html.twig`` and list the actual blog posts.

    .. code-block:: jinja

        {# src/Sensio/BlogBundle/Resources/views/Blog/index.html.twig #}
        {% extends 'BlogBundle::layout.html.twig' %}

        {% block content %}
            {% for entry in blog_entries %}
                <h2>{{ entry.title }}</h2>
                <p>{{ entry.body }}</p>
            {% endfor %}
        {% endblock %}

Notice that this template extends the section template -(``BlogBundle::layout.html.twig``)
which in-turn extends the base application layout (``::layout.html.twig``).
This is the common three-level inheritance model.

When building your application, you may choose to follow this method or simply
make each page template extend the base application template directly
(e.g. ``{% extends '::layout.html.twig' %}``). The three-template model is
a best-practice method used by vendor bundles so that the base template for
a bundle can be easily overriden to properly extend your application's base
layout.

.. index::
   single: Templating; Output escapgin

Output Escaping
---------------

When generating HTML from a template, there is awlays a risk that a template
variable may output unintended HTML or dangerous client-side code. The result
is that dynamic content could break the HTML of the resulting page or allow
a malicious user to perform a `Cross Site Scripting`_ (XSS) attack. Consider
this classic example:

.. configuration-block::

    .. code-block:: jinja

        Hello {{ name }}

    .. code-block:: php

        Hello <?php echo $name ?>

Imagine that the user enters the following code as his/her name::

    <script>alert('hello!')</script>

Without any output escapging, the resulting template will cause a JavaScript
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
of user-submitted code.

In some cases, you'll need to disable output escaping when you're rendering
a variable that is trusted and contains markup that should not be escaped.
Suppose that administrative users are able to write articles that contain
HTML code. By default, Twig will escape the article body. To render it normally,
add the ``raw`` filter: ``{{ article.body | raw }}``.

You can also to disable output escaping inside a ``{{ block }}`` area or
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

Template Formats
----------------

Templates are a generic way to render content in *any* format. And while in
most cases you'll use templates to render HTML content, a template can just
as easily generate JavaScript, CSS, XML or any other format you can dream of.

For example, the same "resource" is often rendered in several different formats.
To render an article index page in XML, simply include the format in the
template name:

*XML template name*: ``ArticleBundle:Article:index.xml.twig``
*XML template filename*: ``index.xml.twig``

In reality, this is nothing more than a naming convention and the template
isn't actually rendered differently based on its format.

In many cases, you may want to allow a single controller to render multiple
different formats based on the "request format". For that reason, a common
pattern is to do the following:

.. code-block::php

    public function indexAction()
    {
        $format = $this->get('request')->getRequestFormat();
    
        return $this->render('BlogBundle:Blog:index.'.$format.'.twig');
    }

The ``getRequestFormat`` on the ``Request`` object defaults to ``html``,
but can return any other format based on the format requested by the user.
The request format is most often managed by the routing, where a route can
be configured so that ``/contact`` sets the request format to ``html`` while
``/contact.xml`` sets the format to ``xml``. For more information, see the
:doc:`Routing</guides/routing>` guide.

Final Thoughts
--------------

The templating engine in Symfony is a powerful tool that can be used each time
you need to generate presentational content in HTML, XML or any other format.
And though templates are a common way to generate content in a controller,
their use is not mandatory. The ``Response`` object returned by a controller
can be created with our without the use of a template:

.. code-block::php

    // creates a Response object whose content is the rendered template
    $response = $this->render('ArticleBundle:Article:index.html.twig');

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

.. _`Twig`: http://www.twig-project.org
.. _`Symfony2Bundles.org`: http://symfony2bundles.org
.. _`Cross Site Scripting`: http://en.wikipedia.org/wiki/Cross-site_scripting
.. _`Output Escaping`: http://www.twig-project.org
.. _`tags`: http://www.twig-project.org/doc/templates.html#comments
.. _`filters`: http://www.twig-project.org/doc/templates.html#list-of-built-in-filters
.. _`add your own extensions`: http://www.twig-project.org/doc/advanced.html