.. index::
   single: Templating

Creating and Using Templates
============================

As you know, the :doc:`controller </book/controller>` is responsible for
handling each request that comes into a Symfony application. In reality,
the controller delegates most of the heavy work to other places so that
code can be tested and reused. When a controller needs to generate HTML,
CSS or any other content, it hands the work off to the **templating engine**.
In this chapter, you'll learn how to write powerful templates that can be
used to return content to the user, populate email bodies, and more. You'll
learn shortcuts, clever ways to extend templates and how to reuse template
code.

.. seealso::

    How to render templates is covered in the
    :ref:`Controller chapter <controller-rendering-templates>` of the book.

.. index::
   single: Templating; What is a template?
   single: Templating; PHP templating engine
   single: Templating; Twig templating engine

PHP versus Twig
---------------

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

Throughout this chapter, template examples will be shown in both Twig and PHP.

.. note::

   The Twig engine is mandatory to use the ``web_profiler`` (as well as
   many third-party bundles).

.. note::

    If you *do* choose to not use Twig and you disable it, you'll need it
    implement your own exception handler via the ``kernel.exception`` event.
    See cookbook article :doc:`/cookbook/templating/PHP`.

.. index::
   single: Templating; Template formats
   single: Templating; Rendering different template formats

.. _template-formats:

Template Suffix
---------------

Every template name also has two extensions that specify the *format* and
*engine* for that template. Templates are a generic way to render content in
*any* format. And while in most cases you'll use templates to render HTML
content, a template can just as easily generate JavaScript, CSS, XML or any
other format you can dream of.

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
generate.  In reality, this is nothing more than a naming convention and the
template isn't actually rendered differently based on its format.

.. seealso::

    How to render templates is covered in the
    :ref:`Controller chapter <controller-rendering-templates>` of the book.

In many cases, you may want to allow a single controller to render multiple
different formats based on the "request format". For that reason, a common
pattern is to do the following::

    public function indexAction(Request $request)
    {
        $format = $request->getRequestFormat();

        return $this->render('article/index.'.$format.'.twig');
    }

The ``getRequestFormat()`` method on the ``Request`` object defaults to ``html``,
but can return any other format based on the format requested by the user.
The request format is most often managed by the routing, where a route can
be configured so that ``/contact`` sets the request format to ``html`` while
``/contact.xml`` sets the format to ``xml``. For more information, see the
:ref:`Advanced Routing Example <advanced-routing-example>` section in the
Routing chapter.

.. index::
   single: Templating; The templating service

.. _book-templating-engine:

Configuring and Using the ``templating`` Service
------------------------------------------------

The heart of the template system in Symfony is the ``templating`` engine.
This special object is responsible for rendering templates (determines how
Symfony parses the template) and returning their content. When you render a
template in a controller, for example, you're actually using the
``templating`` engine service. For example::

    return $this->render('article/index.html.twig');

is equivalent to::

    use Symfony\Component\HttpFoundation\Response;

    $engine = $this->container->get('templating');
    $content = $engine->render('article/index.html.twig');

    return $response = new Response($content);

.. _template-configuration:

The templating engine (or "service") is preconfigured to work automatically
inside Symfony. It can, of course, be configured further in the default
application configuration file::

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating: { engines: ['twig'] }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:templating>
                    <framework:engine>twig</framework:engine>
                </framework:templating>
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...

            'templating' => array(
                'engines' => array('twig'),
            ),
        ));

Several configuration options are available and are covered in the reference
section of the doc inside :doc:`/reference/configuration/framework`.

.. index::
   single: Templating; Global template variables

Global Template Variables
-------------------------

During each request, Symfony will set a global template variable ``app``
in both Twig and PHP template engines by default. The ``app`` variable
is a :class:`Symfony\\Bundle\\FrameworkBundle\\Templating\\GlobalVariables`
instance which will give you access to some application specific variables
automatically:

``app.security``
    The security context.
``app.user``
    The current user object.
``app.request``
    The request object.
``app.session``
    The session object.
``app.environment``
    The current environment (``dev``, ``prod``, etc.).
``app.debug``
    ``true`` if in debug mode, ``false`` otherwise.

Practical example::

.. configuration-block::

    .. code-block:: html+twig

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
        <?php endif ?>

.. tip::

    You can add your own global template variables. See the cookbook article
    :doc:`Global Variables </cookbook/templating/global_variables>`.

.. index::
   single: Twig; Introduction
   single: Twig; Twig syntax

Twig Syntax
-----------

Twig defines three types of special syntax:

``{{ ... }}``
    "Says something": prints a variable or the result of an expression to the
    template.

``{% ... %}``
    "Does something": a **tag** that controls the logic of the template; it
    is used to execute flow-control statements such as for-loops for example.

``{# ... #}``
    "Comment something": it's the equivalent of the PHP ``/* comment */``
    syntax. It's used to add single or multi-line comments. The content of
    the comments isn't included in the rendered pages.

.. seealso::

    Twig official documentation is available `here`_.

Twig also contains **filters**, which modify content before being rendered.
The following makes the ``title`` variable all uppercase before rendering
it::

.. code-block:: html+twig

    {{ title|upper }}

Twig Extensions
~~~~~~~~~~~~~~~

`Twig extensions`_ are packages that add new features to Twig. Twig comes with
several types of extensions:

* ``Twig_Extension_Core``
* ``Twig_Extension_Escaper``
* ``Twig_Extension_Sandbox``
* ``Twig_Extension_Profiler``
* ``Twig_Extension_Optimizer``

``core`` extension defines all the core features of Twig. There are four
``core`` extensions - we have already seen two::

* `functions`_: Content generation
* `filters`_: Value transformation
* `tags`_: DSL language construct
* `tests`_: Boolean decision

For example, the following uses a standard ``for`` tag and the ``cycle()``
function to print ten HTML ``<div>`` tags, with alternating ``odd``, ``even``
classes::

.. code-block:: html+twig

    {% for i in 0..10 %}
        <div class="{{ cycle(['odd', 'even'], i) }}">
          <!-- some HTML here -->
        </div>
    {% endfor %}

You can `add your own extensions`_ to Twig as needed. Registering a
Twig extension is as easy as creating a new service and tagging it with
``twig.extension`` :ref:`tag <reference-dic-tags-twig-extension>`.

.. index::
   single: Twig; Why Twig?

Why Twig?
~~~~~~~~~

Twig templates are meant to be simple and won't process PHP tags. This
is by design: **the Twig template system is meant to express presentation,
not program logic**. The more you use Twig, the more you'll appreciate
and benefit from this distinction. And of course, you'll be loved by
web designers everywhere.

Twig can also do things that PHP can't, such as whitespace control,
sandboxing, automatic HTML escaping, manual contextual output escaping,
and the inclusion of custom functions and filters that only affect templates.
Twig contains little features that make writing templates easier and more
concise. Take the following example, which combines a loop with a logical
``if`` statement:

.. code-block:: html+twig

    <ul>
        {% for user in users if user.active %}
            <li>{{ user.username }}</li>
        {% else %}
            <li>No users found</li>
        {% endfor %}
    </ul>

.. index::
   single: Twig; Caching
   single: Template; Caching

Twig Template Caching
~~~~~~~~~~~~~~~~~~~~~

Twig is fast. Each Twig template is compiled down to a native PHP class
that is rendered at runtime. The compiled classes are located in the
``app/cache/{environment}/twig`` directory (where ``{environment}`` is the
environment, such as ``dev`` or ``prod``) and in some cases can be useful
while debugging. See :ref:`environments-summary` section for more information
on environments.

When ``debug`` mode is enabled (common in the ``dev`` environment), a Twig
template will be automatically recompiled when changes are made to it. This
means that during development you can happily make changes to a Twig template
and instantly see the changes without needing to worry about clearing any
cache.

When ``debug`` mode is disabled (common in the ``prod`` environment), however,
you must clear the Twig cache directory so that the Twig templates will
regenerate. Remember to do this when deploying your application. You can do
that by using ``cache:clear`` Console command::

.. code-block:: bash

    $ php app/console cache:clear --env=prod --no-debug

.. index::
   single: Templating; Twig and PHP Inheritance
   single: Twig; Inheritance

.. _twig-inheritance:

Template Inheritance and Layouts
--------------------------------

.. note::

    Though the discussion about template inheritance will be in terms of Twig,
    the philosophy is the same between Twig and PHP templates.

More often than not, templates in a project share common elements, like the
header, footer, sidebar or more. In Symfony, this problem is thought about
differently: a template can be decorated by another one. This works
exactly the same as PHP classes: template inheritance allows you to build
a base "layout" template that contains all the common elements of your site
defined as **blocks** (think "PHP class with base methods"). A child template
can extend the base layout and override any of its blocks (think "PHP subclass
that overrides certain methods of its parent class").

First, build a base layout file::

.. configuration-block::

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

    .. code-block:: html+php

        <!-- app/Resources/views/base.html.php -->
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <title>
                    <?php $view['slots']->output('title', 'Test Application') ?>
                </title>
            </head>
            <body>
                <div id="sidebar">
                    <?php if ($view['slots']->has('sidebar')): ?>
                        <?php $view['slots']->output('sidebar') ?>
                    <?php else: ?>
                        <ul>
                            <li><a href="/">Home</a></li>
                            <li><a href="/blog">Blog</a></li>
                        </ul>
                    <?php endif ?>
                </div>

                <div id="content">
                    <?php $view['slots']->output('body') ?>
                </div>
            </body>
        </html>

This template defines the base HTML skeleton document of a simple two-column
page. In this example, three ``{% block %}`` areas are defined (``title``,
``sidebar`` and ``body``). Each block may be overridden by a child template
or left with its default implementation. This template could also be rendered
directly. In that case the ``title``, ``sidebar`` and ``body`` blocks would
simply retain the default values used in this template.

A child template might look like this::

.. configuration-block::

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

    .. code-block:: html+php

        <!-- app/Resources/views/blog/index.html.php -->
        <?php $view->extend('base.html.php') ?>

        <?php $view['slots']->set('title', 'My cool blog posts') ?>

        <?php $view['slots']->start('body') ?>
            <?php foreach ($blog_entries as $entry): ?>
                <h2><?php echo $entry->getTitle() ?></h2>
                <p><?php echo $entry->getBody() ?></p>
            <?php endforeach ?>
        <?php $view['slots']->stop() ?>

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

**You can use as many levels of inheritance as you want**. In the next section,
a common three-level inheritance model will be explained along with how templates
are organized inside a Symfony project.

When working with template inheritance, here are some tips to keep in mind:

* If you use ``{% extends %}`` tag in a template, it must be the first tag in
  that template;

* The more ``{% block %}`` tags you have in your base templates, the better.
  Remember, child templates don't have to define all parent blocks, so create
  as many blocks in your base templates as you want and give each a sensible
  default. The more blocks your base templates have, the more flexible your
  layout will be;

* If you find yourself duplicating content in a number of templates, it probably
  means you should move that content to a ``{% block %}`` in a parent template.
  In some cases, a better solution may be to move the content to a new template
  and include it using twig ``include()`` function (see
  :ref:`including-templates` of this chapter);

* If you need to get the content of a block from the parent template, you
  can use the ``parent()`` function. This is useful if you want to add
  to the contents of a parent block instead of completely overriding it:

  .. code-block:: html+twig

      {% block sidebar %}
          <h3>Table of Contents</h3>

          {# ... #}

          {{ parent() }}
      {% endblock %}

.. index::
   single: Templating; File locations

.. _template-naming-locations:

Template Locations
------------------

By default, templates can live in two different locations:

``app/Resources/views/``
    Directory can contain:
     * application-wide base templates;
     * application bundle base template;
     * application bundle "section" pages;
     * templates that override third party bundle templates (see
       :ref:`overriding-bundle-templates` section of this chapter).

``path/to/bundle/Resources/views/``
    Directory (and subdirectories) contains bundle's templates. When you plan
    to share your bundle, you should put the templates in the bundle instead
    of the ``app/`` directory.

Most of the templates you'll use live in the ``app/Resources/views/``
directory.

.. index::
   single: Templating; Three-level inheritance pattern

Three-level Inheritance
-----------------------

One common way to use inheritance is to use a three-level approach. This
method works perfectly with the three different types of templates that we
will cover:

#. Create an ``app/Resources/views/base.html.twig`` file that contains the main
   layout for your application. Internally, this template is called
   ``base.html.twig``;

   .. code-block:: html+twig

       {# app/Resources/views/base.html.twig #}
       <!DOCTYPE html>
       <html>
           <head>
               <meta charset="UTF-8">
               <title>{% block title %}Test Application{% endblock %}</title>
           </head>
           <body>
               <div id="content">
                   {% block body %}{% endblock %}
               </div>
           </body>
       </html>

#. Create a template for each "section" of your site. For example, the blog
   functionality would have a template called ``blog/layout.html.twig`` that
   contains only blog section-specific elements. The template lives at
   ``app/Resources/views/blog/layout.html.twig`` (but it could also live at
   ``src/path/to/bundle/Resources/views/blog/layout.html.twig``);

   .. code-block:: html+twig

       {# app/Resources/views/blog/layout.html.twig #}
       {% extends 'base.html.twig' %}

       {% block body %}
           <h1>Blog Application</h1>

           {% block content %}{% endblock %}
       {% endblock %}

#. Create individual templates for each page and make each extend the appropriate
   section template. For example, the "index" page would be called something
   close to ``blog/index.html.twig`` and list the actual blog posts. The
   template lives at ``app/Resources/views/blog/index.html.twig`` (but it
   could also live at ``src/path/to/bundle/Resources/views/blog/index.html.twig``);

   .. code-block:: html+twig

       {# app/Resources/views/blog/index.html.twig #}
       {% extends 'blog/layout.html.twig' %}

       {% block content %}
           {% for entry in blog_entries %}
               <h2>{{ entry.title }}</h2>
               <p>{{ entry.body }}</p>
           {% endfor %}
       {% endblock %}

Notice that this template extends the section template ``blog/layout.html.twig``
which in turn extends the base application layout ``base.html.twig``. This is
the common three-level inheritance model.

When building your application, you may choose to follow this method or simply
make each page template extend the base application template directly
(e.g. ``{% extends 'base.html.twig' %}``). The three-template model is a
best-practice method used by vendor bundles so that the base template for a
bundle can be easily overridden to properly extend your application's base
layout.

.. index::
   single: Templating; Naming conventions

.. _template-referencing-in-bundle:
.. _template-naming-pattern-namespaced-path:

Template Naming Pattern
~~~~~~~~~~~~~~~~~~~~~~~

There are two ways to refer to a template:

* Logical template name;

* Namespace path.

We will look at how to refer to templates that live inside a bundle and
templates that live in the applications ``app/Resources/views/``
directory.

#. For templates that live inside a bundle Symfony uses a simple string
   pattern called the **logical template name**. The pattern has three parts,
   each separated by a colon. This syntax is used to specify a template for
   a specific page of specific "section"::

    **bundle**:**directory**:**filename**

   Let's dissect the three parts of the ``AcmeBlogBundle:Blog:index.html.twig``
   string:

   * ``AcmeBlogBundle``: (*bundle*) the template lives inside the AcmeBlogBundle,
     e.g. ``src/Acme/BlogBundle``;

   * ``Blog``: (*directory*) indicates that the template lives inside the
     ``Blog`` subdirectory of ``src/Acme/BlogBundle/Resources/views/Blog``;

   * ``index.html.twig``: (*filename*) the actual name of the file.

   Assuming that the AcmeBlogBundle lives at ``src/Acme/BlogBundle``, the
   final path to the layout would be
   ``src/Acme/BlogBundle/Resources/views/Blog/index.html.twig``.

   There is "another" syntax used to refer to a base template that's specific to the
   bundle::

    **bundle**::**filename**

   Let's dissect the parts of the `AcmeBlogBundle::layout.html.twig`` string.
   Since the middle, "directory", portion is missing (e.g. ``Blog``), the template
   lives at ``src/Acme/BlogBundle/Resources/views/layout.html.twig`` inside
   AcmeBlogBundle. Yes, there are 2 colons in the middle of the string when the
   "controller" subdirectory part is missing.

#. For templates that live in the applications ``app/Resources/views/``
   directory which can contain application-wide base templates and templates that
   override third party bundle templates (see :ref:`overriding-bundle-templates`
   section of this chapter), we can use two different syntaxes:

   * **the path relative to this directory**: For example, to render or extend
     ``app/Resources/views/base.html.twig``, the ``base.html.twig`` path would
     be used and to render or extend ``app/Resources/views/blog/index.html.twig``,
     the ``blog/index.html.twig`` path would be used.

   * **logical template name**::

        ::**filename**

     For example, to render or extend ``app/Resources/views/base.html.twig``, the
     ``::base.html.twig`` path would be used and to render or extend
     ``app/Resources/views/blog/index.html.twig``, the ``::blog/index.html.twig``
     path would be used.

.. tip::

    Logical template name syntax should look familiar - it's similar to
    the :ref:`logical controller naming convention <controller-string-syntax>`
    used to refer to controllers.

.. versionadded:: 2.2
    **Namespace path** support was introduced in 2.2.

Twig also natively offers a feature called **namespaced paths**, and support
is built-in automatically for all of your bundles.

Take the following paths as an example:

.. code-block:: twig

    {% extends "AppBundle::layout.html.twig" %}
    {{ include('AppBundle:Foo:bar.html.twig') }}

With namespaced paths, the following works as well:

.. code-block:: twig

    {% extends "@App/layout.html.twig" %}
    {{ include('@App/Foo/bar.html.twig') }}

Both paths are valid and functional by default in Symfony.

.. note::

    As an added bonus, the namespaced syntax is faster.

.. index::
    single: Template; Overriding templates

.. _overriding-bundle-templates:

Overriding Bundle Templates
---------------------------

The Symfony community prides itself on creating and maintaining high quality
bundles for a large number of different features (see `KnpBundles.com`_).
Once you use a third-party bundle, you'll likely need to override and customize
one or more of its templates.

Suppose you've installed the imaginary open-source AcmeBlogBundle in your
project. And while you're really happy with everything, you want to override
the blog "list" page to customize the markup specifically for your application.
By digging into the ``Blog`` controller of the AcmeBlogBundle, you find the
following::

    public function indexAction()
    {
        // some logic to retrieve the blogs
        $blogs = ...;

        $this->render(
            'AcmeBlogBundle:Blog:index.html.twig',
            array('blogs' => $blogs)
        );
    }

When the ``AcmeBlogBundle:Blog:index.html.twig`` is rendered, Symfony actually
looks in two different locations for the template:

#. ``app/Resources/AcmeBlogBundle/views/Blog/index.html.twig``
#. ``src/Acme/BlogBundle/Resources/views/Blog/index.html.twig``

To override the bundle template, just copy the ``index.html.twig`` template
from the bundle to ``app/Resources/AcmeBlogBundle/views/Blog/index.html.twig``.
The ``app/Resources/AcmeBlogBundle`` directory won't exist, so you'll need
to create it. You're now free to customize the template.

.. caution::

    If you add a template in a new location, you *may* need to clear your
    cache with the ``cache:clear`` console command, even if you are in debug mode.

This logic also applies to base bundle templates. Suppose also that each
template in AcmeBlogBundle inherits from a base template called
``AcmeBlogBundle::layout.html.twig``. Just as before, Symfony will look in
the following two places for the template:

#. ``app/Resources/AcmeBlogBundle/views/layout.html.twig``
#. ``src/Acme/BlogBundle/Resources/views/layout.html.twig``

Once again, to override the template, just copy it from the bundle to
``app/Resources/AcmeBlogBundle/views/layout.html.twig``. You're now free to
customize this copy as you see fit.

If you take a step back, you'll see that Symfony always starts by looking in
the ``app/Resources/{BUNDLE_NAME}/views/`` directory for a template. If the
template doesn't exist there, it continues by checking inside the
``Resources/views`` directory of the bundle itself. This means that all bundle
templates can be overridden by placing them in the correct ``app/Resources``
subdirectory.

.. seealso::

    You can also override templates and other parts of a third-party bundle
    from within an application bundle by using bundle inheritance. For more
    information, read cookbook article :doc:`/cookbook/bundles/inheritance`.

.. seealso::

    We just looked at how to override templates from third-party bundles but
    we can actually override any part of a third-party bundle. To learn more
    read cookbook article :doc:`/cookbook/bundles/override`

.. _templating-overriding-core-templates:

.. index::
    single: Twig; Overriding exception templates

Overriding Twig Extension Templates
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Since the Symfony Framework itself is just a bundle, twig extension templates
can be overridden in the same way. For example, the core TwigBundle contains
a number of different "exception" and "error" templates that can be overridden
by copying each from the ``Resources/views/Exception`` directory of the
TwigBundle to, you guessed it, the ``app/Resources/TwigBundle/views/Exception``
directory.

.. index::
   single: Templating; Twig Tags and PHP helpers
   single: Twig; Tags
   single: PHP; Templating helpers

Twig Tags and PHP Helpers
-------------------------

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

We will look at Twig ``include()`` function and PHP ``render()`` helper
function.

You'll often want to include the same template or code fragment on several
pages. For example, in an application with "news articles", the
template code displaying an article might be used on the article detail page,
on a page displaying the most popular articles, or in a list of the latest
articles.

When you need to reuse a chunk of PHP code, you typically move the code to
a new PHP class or function. The same is true for templates. By moving the
reused template code into its own template, it can be included from any other
template. First, create the template that you'll need to reuse::

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/article/article_details.html.twig #}
        <h2>{{ article.title }}</h2>
        <h3 class="byline">by {{ article.authorName }}</h3>

        <p>
            {{ article.body }}
        </p>

    .. code-block:: html+php

        <!-- app/Resources/views/article/article_details.html.php -->
        <h2><?php echo $article->getTitle() ?></h2>
        <h3 class="byline">by <?php echo $article->getAuthorName() ?></h3>

        <p>
            <?php echo $article->getBody() ?>
        </p>

Including this template from any other template is simple. The template is
included using the Twig ``{{ include() }}`` function::

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/article/list.html.twig #}
        {% extends 'layout.html.twig' %}

        {% block body %}
            <h1>Recent Articles<h1>

            {% for article in articles %}
                {{ include('article/article_details.html.twig', { 'article': article }) }}
            {% endfor %}
        {% endblock %}

    .. code-block:: html+php

        <!-- app/Resources/article/list.html.php -->
        <?php $view->extend('layout.html.php') ?>

        <?php $view['slots']->start('body') ?>
            <h1>Recent Articles</h1>

            <?php foreach ($articles as $article): ?>
                <?php echo $view->render(
                    'Article/article_details.html.php',
                    array('article' => $article)
                ) ?>
            <?php endforeach ?>
        <?php $view['slots']->stop() ?>

Notice that the template name follows the typical conventions seen earlier.

The ``article_details.html.twig`` template uses an ``article`` variable,
which we pass to it. In this case, you could avoid doing this entirely,
as all of the variables available in ``list.html.twig`` are also available
in ``article_details.html.twig`` (unless you set `with_context`_ to false).

.. tip::

    The ``{'article': article}`` syntax is the standard Twig syntax for **hash
    maps** (i.e. an array with named keys). If you needed to pass in multiple
    elements, it would look like this: ``{'foo': foo, 'bar': bar}``.

.. versionadded:: 2.3
    The `include() function`_ is available since Symfony 2.3. Prior, the
    `{% include %} tag`_ was used.

.. index::
   single: Templating; Embedding controllers

.. _templating-embedding-controller:

Embedding Controllers
~~~~~~~~~~~~~~~~~~~~~

We will look at Twig ``render()`` function and PHP ``render()`` helper function.

In some cases, you need to do more than include a simple template. Suppose
you have a sidebar in your layout that contains the three most recent articles.
Retrieving the three articles may include querying the database or performing
other heavy logic that can't be done from within a template. We need a piece
of information that you don't have access to in a template. The solution is to
simply **render a controller from a template and *embed* the result** in a
template.

First, create a controller that renders a certain number of recent
articles::

    // src/AppBundle/Controller/ArticleController.php
    namespace AppBundle\Controller;

    // ...

    class ArticleController extends Controller
    {
        public function recentArticlesAction($max = 3)
        {
            // make a database call or other logic
            // to get the "$max" most recent articles
            $articles = ...;

            return $this->render(
                'article/recent_list.html.twig',
                array('articles' => $articles)
            );
        }
    }

The ``recent_list.html.twig`` template is perfectly straightforward::

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/article/recent_list.html.twig #}
        {% for article in articles %}
            <a href="/article/{{ article.slug }}">
                {{ article.title }}
            </a>
        {% endfor %}

    .. code-block:: html+php

        <!-- app/Resources/views/article/recent_list.html.php -->
        <?php foreach ($articles as $article): ?>
            <a href="/article/<?php echo $article->getSlug() ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach ?>

.. note::

    Notice that the article URL is hardcoded in this example (e.g.
    ``/article/*slug*``). This is a bad practice. In the
    :ref:`next section <book-templating-pages>` of this chapter, you'll
    learn how to do this correctly.

Twig ``render()`` function renders the controller::

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/base.html.twig #}

        {# ... #}
        <div id="sidebar">
            {{ render(controller(
                'AppBundle:Article:recentArticles',
                { 'max': 3 }
            )) }}
        </div>

    .. code-block:: html+php

        <!-- app/Resources/views/base.html.php -->

        <!-- ... -->
        <div id="sidebar">
            <?php echo $view['actions']->render(
                new \Symfony\Component\HttpKernel\Controller\ControllerReference(
                    'AppBundle:Article:recentArticles',
                    array('max' => 3)
                )
            ) ?>
        </div>

To refer to a controller the *logical controller name* is used (i.e.
**bundle**:**controller**:**action**).

.. note::

    When embedding a result from a controller instead of some other page (a URL),
    you must enable the Symfony `fragments`` configuration inside Symfony
    default configuration file::

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            framework:
                # ...
                fragments: { path: /_fragment }

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xmlns:framework="http://symfony.com/schema/dic/symfony"
                xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                    http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

                <!-- ... -->
                <framework:config>
                    <framework:fragments path="/_fragment" />
                </framework:config>
            </container>

        .. code-block:: php

            // app/config/config.php
            $container->loadFromExtension('framework', array(
                // ...
                'fragments' => array('path' => '/_fragment'),
            ));

.. index::
   single: Templating; Asynchronously Embedding Controllers

.. _book-templating-hinclude:

Asynchronously Embedding Controllers with hinclude.js
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.1
    hinclude.js support was introduced in Symfony 2.1

Controllers can be embedded asynchronously using the `hinclude.js`_ JavaScript
library. As the embedded content comes from another page (or controller for
that matter), Symfony uses a version of the standard Twig ``render()`` function
called ``render_hinclude()`` to configure ``hinclude`` tags::

.. configuration-block::

    .. code-block:: twig

        {{ render_hinclude(controller('...')) }}
        {{ render_hinclude(url('...')) }}

    .. code-block:: php

        <?php echo $view['actions']->render(
            new ControllerReference('...'),
            array('renderer' => 'hinclude')
        ) ?>

        <?php echo $view['actions']->render(
            $view['router']->generate('...'),
            array('renderer' => 'hinclude')
        ) ?>

.. note::

    `hinclude.js`_ needs to be included in your page to work.

.. versionadded:: 2.2
    Default templates per ``render_hinclude()`` function was introduced in
    Symfony 2.2.

Default templates for ``render_hinclude()`` function (while loading or if
JavaScript is disabled) can be set globally in application default
configuration file::

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        framework:
            # ...
            templating:
                hinclude_default_template: hinclude.html.twig

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <!-- ... -->
            <framework:config>
                <framework:templating hinclude-default-template="hinclude.html.twig" />
            </framework:config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('framework', array(
            // ...
            'templating' => array(
                'hinclude_default_template' => array(
                    'hinclude.html.twig',
                ),
            ),
        ));

Of course global default template can be overridden::

.. configuration-block::

    .. code-block:: twig

        {{ render_hinclude(controller('...'),  {
            'default': 'default/content.html.twig'
        }) }}

    .. code-block:: php

        <?php echo $view['actions']->render(
            new ControllerReference('...'),
            array(
                'renderer' => 'hinclude',
                'default'  => 'default/content.html.twig',
            )
        ) ?>

You can also specify a string to display as the default content::

.. configuration-block::

    .. code-block:: twig

        {{ render_hinclude(controller('...'), {'default': 'Loading...'}) }}

    .. code-block:: php

        <?php echo $view['actions']->render(
            new ControllerReference('...'),
            array(
                'renderer' => 'hinclude',
                'default'  => 'Loading...',
            )
        ) ?>

.. index::
   single: Templating; Genrating URLs inside templates

.. _book-templating-pages:

Linking to Pages
~~~~~~~~~~~~~~~~

We will look at Twig ``path()`` and ``url()`` functions and PHP ``generate()``
helper function.

The most common place to generate a URL is from within a template when
linking between pages in an application. Instead of hardcoding URLs in
templates we just need to specify a route name. Later, if we want to
modify the URL of a particular page, all we'll need to do is change the
routing configuration; the templates will automatically generate the new URL.

We can generate two types od URL:

* relative URLs using ``path()`` function
* absolute URLs using ``url()`` function

First, we will look at relative URLs. Create a route named "_welcome" and
associate it with a controller::

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Controller/WelcomeController.php

        // ...
        use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

        class WelcomeController extends Controller
        {
            /**
             * @Route("/", name="_welcome")
             */
            public function indexAction()
            {
                // ...
            }
        }

    .. code-block:: yaml

        # app/config/routing.yml
        _welcome:
            path:     /
            defaults: { _controller: AppBundle:Welcome:index }

    .. code-block:: xml

        <!-- app/config/routing.yml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing
                http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="_welcome" path="/">
                <default key="_controller">AppBundle:Welcome:index</default>
            </route>
        </routes>

    .. code-block:: php

        // app/config/routing.php
        use Symfony\Component\Routing\Route;
        use Symfony\Component\Routing\RouteCollection;

        $collection = new RouteCollection();
        $collection->add('_welcome', new Route('/', array(
            '_controller' => 'AppBundle:Welcome:index',
        )));

        return $collection;

Now, inside Twig template link to the page, by using the ``path()`` Twig function
and refer to the route::

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ path('_welcome') }}">
            Welcome!
        </a>

    .. code-block:: html+php

        <a href="<?php echo $view['router']->generate('_welcome') ?>">
            Welcome!
        </a>

As expected, this will generate the URL ``/``. Now, for a more complicated
route::

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
a value for the ``{slug}`` parameter. This is done using **hash maps**
(i.e.an array with named keys) as second attribute to the ``path()`` function.

Here we will revisit the ``recent_list.html.twig`` template from the
:ref:`embedded controllers <templating-embedding-controller>` section where we
hard-coded the articles URL and correct this bad practice by linking to the
articles correctly::

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/article/recent_list.html.twig #}
        {% for article in articles %}
            <a href="{{ path('article_show', {'slug': article.slug}) }}">
                {{ article.title }}
            </a>
        {% endfor %}

    .. code-block:: html+php

        <!-- app/Resources/views/Article/recent_list.html.php -->
        <?php foreach ($articles in $article): ?>
            <a href="<?php echo $view['router']->generate('article_show', array(
                'slug' => $article->getSlug(),
            )) ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach ?>


Now, we will look at absolute URLs. Absolute URLs are generated using Twig
``url()`` function::

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ url('blog_show', {'slug': 'my-blog-post'}) }}">
          Read this blog post.
        </a>

    .. code-block:: html+php

        <?php
        use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
        ?>

        <a href="<?php echo $view['router']->generate('blog_show', array(
            'slug' => 'my-blog-post',
        ), UrlGeneratorInterface::ABSOLUTE_URL) ?>">
            Read this blog post.
        </a>

Linking to Pages of Different Formats
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To create links that include the special routing ``_format` parameter,
include a ``_format`` key in the parameter hash::

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ path('article_show', {'id': 123, '_format': 'pdf'}) }}">
            PDF Version
        </a>

    .. code-block:: html+php

        <a href="<?php echo $view['router']->generate('article_show', array(
            'id' => 123,
            '_format' => 'pdf',
        )) ?>">
            PDF Version
        </a>

.. seealso::

    To learn about special routing parameters like `_format` read
    :ref:`section <advanced-routing-example>` od the Routing chapter.

.. index::
   single: Templating; Linking to assets

.. _book-templating-assets:

Linking to Assets
~~~~~~~~~~~~~~~~~

Templates also commonly refer to images, JavaScript, stylesheets and other
assets. Of course you could hard-code the path to these assets (e.g.
``/images/logo.png``), but Symfony provides a more dynamic option via the
``asset()`` Twig function::

.. configuration-block::

    .. code-block:: html+twig

        <img src="{{ asset('images/logo.png') }}" alt="Symfony!" />

        <link href="{{ asset('css/blog.css') }}" rel="stylesheet" />

    .. code-block:: html+php

        <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>" alt="Symfony!" />

        <link href="<?php echo $view['assets']->getUrl('css/blog.css') ?>" rel="stylesheet" />

The ``asset()`` function's main purpose is to make application more portable.
If application lives at the root of the host (e.g. ``http://example.com``),
then the rendered paths should be ``/images/logo.png``. But if application
lives in a subdirectory (e.g. ``http://example.com/my_app``), each asset path
should render with the subdirectory (e.g. ``/my_app/images/logo.png``). The
``asset()`` function takes care of this by determining how application is
being used and generating the correct paths accordingly.

Additionally, if you use the ``asset()`` function, Symfony can automatically
append a query string to your asset, in order to guarantee that updated static
assets won't be cached when deployed. For example, ``/images/logo.png`` might
look like ``/images/logo.png?v2``. For more information, see the
:ref:`reference-framework-assets-version` configuration option.

.. index::
   single: Templating; Including stylesheets and JavaScripts
   single: Stylesheets; Including stylesheets
   single: JavaScript; Including JavaScripts

Including Stylesheets and JavaScripts in Twig
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

No site would be complete without including JavaScript files and stylesheets.
In Symfony, the inclusion of these assets is handled elegantly by taking
advantage of Symfony's template inheritance.

.. tip::

    This section will teach you the philosophy behind including stylesheet
    and JavaScript assets in Symfony. Symfony also packages another library,
    called Assetic, which follows this philosophy but allows you to do much
    more interesting things with those assets. For more information on
    using Assetic see cookbook article :doc:`/cookbook/assetic/asset_management`.

Start by adding two blocks to your base template that will hold your assets:
one called ``stylesheets`` inside the ``head`` tag and another called
``javascripts`` just above the closing ``body`` tag. These blocks will contain
all of the stylesheets and JavaScripts that you'll need throughout your site::

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
page. We want to add to the contents of a parent block instead of completely
overriding it. This is done by overriding ``stylesheets`` block, putting
``parent()`` Twig function into it to include everything from the ``stylesheets``
block of the base template, and adding new assets we need just on this page::

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

The end result is a page that includes both the ``main.css`` and ``contact.css``
stylesheets.

Including Assets from Bundles
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Bundle related assets are located in the bundle ``Resources/public`` directory.
This files need to be moved or symlinked into the correct location, by default
``web/`` directory. This can be done by using ``assets:install`` console command::

.. code-block:: bash

    $ php app/console assets:install target [--symlink]

Now, they can be included in a template:

.. code-block:: html+twig

    <link href="{{ asset('bundles/acmedemo/css/contact.css') }}" rel="stylesheet" />

.. index::
   single: Templating; Output escaping
   single: Templating; Cross Site Scripting

Output Escaping
---------------

When generating HTML from a template, there is always a risk that a template
variable may output unintended HTML or dangerous client-side code. The result
is that dynamic content could break the HTML of the resulting page or allow
a malicious user to perform a `Cross Site Scripting`_ (XSS) attack. Consider
this classic example::

.. configuration-block::

    .. code-block:: html+twig

        Hello {{ name }}

    .. code-block:: html+php

        Hello <?php echo $name ?>

Imagine the user enters the following code for their name::

.. code-block:: html

    <script>alert('hello!')</script>

Without any output escaping, the resulting template will cause a JavaScript
alert box to pop up::

.. code-block:: html

    Hello <script>alert('hello!')</script>

And while this seems harmless, if a user can get this far, that same user
should also be able to write JavaScript that performs malicious actions
inside the secure area of an unknowing, legitimate user.

The answer to the problem is output escaping. With output escaping on, the
same template will render harmlessly, and literally print the ``script``
tag to the screen::

.. code-block:: html

    Hello &lt;script&gt;alert(&#39;hello!&#39;)&lt;/script&gt;

The Twig and PHP templating systems approach the problem in different ways.
If you're using Twig, output escaping is on by default and you're protected.
In PHP, output escaping is not automatic, meaning you'll need to manually
escape where necessary.

Output Escaping in Twig
~~~~~~~~~~~~~~~~~~~~~~~

**If you're using Twig templates, then output escaping is on by default**. This
means that you're protected out-of-the-box from the unintentional consequences
of user-submitted code. By default, the output escaping assumes that content
is being escaped for HTML output.

In some cases, you'll need to **disable output escaping** when you're rendering
a variable that is trusted and contains markup that should not be escaped.
Suppose that administrative users are able to write articles that contain
HTML code. By default, Twig will escape the article body.

To render it normally, add the ``raw`` filter::

.. code-block:: twig

    {{ article.body|raw }}

You can also disable output escaping inside a ``{% block %}`` area or
for an entire template. For more information, see `Output Escaping`_ in
the Twig documentation.

Output Escaping in PHP
~~~~~~~~~~~~~~~~~~~~~~

**Output escaping is not automatic when using PHP templates**. This means that
unless you explicitly choose to escape a variable, you're not protected. To
use output escaping, use the special ``escape()`` view method::

.. code-block:: html+php

    Hello <?php echo $view->escape($name) ?>

By default, the ``escape()`` method assumes that the variable is being rendered
within an HTML context (and thus the variable is escaped to be safe for HTML).
The second argument lets you change the context. For example, to output
something in a JavaScript string, use the ``js`` context::

.. code-block:: html+php

    var myMsg = 'Hello <?php echo $view->escape($name, 'js') ?>';

.. index::
   single: Templating; Twig debugging

Debugging
---------

Important, but unrelated to the topic of templating is the second argument
to the ``AppKernel()`` constructor inside front controller being used. This
specifies if the application should run in "debug mode" or not. Regardless of
the environment, a Symfony application can therefore run with debug mode set to
``true`` or ``false``.

Internally, the value of the debug mode becomes the ``kernel.debug``
parameter used inside the service container. If you look inside the
default application configuration file, you'll see the parameter used,
for example, to turn the debug mode on or off when using the Twig::

.. configuration-block::

    .. code-block:: yaml

        twig:
            debug: '%kernel.debug%'
            # ...

    .. code-block:: xml

        <doctrine:dbal logging="%kernel.debug%" />

    .. code-block:: php

        $container->loadFromExtension('twig', array(
            'debug'  => '%kernel.debug%',
            // ...
        ));

When using PHP, you can use :phpfunction:`var_dump` if you need to quickly find
the value of a variable passed. This is useful, for example, inside your
controller. The same can be achieved when using Twig thanks to the Debug
extension. Template parameters can then be dumped using Twig ``dump()``
function, which internally, uses the PHP `var_dump()` function::

.. code-block:: html+twig

    {# app/Resources/views/article/recent_list.html.twig #}
    {{ dump(articles) }}

    {% for article in articles %}
        <a href="/article/{{ article.slug }}">
            {{ article.title }}
        </a>
    {% endfor %}

.. index::
   single: Templating; Twig Syntax Checking

Syntax Checking
---------------

You can check for syntax errors in Twig templates using the ``twig:lint``
console command::

.. code-block:: bash

    # You can check by filename:
    $ php app/console twig:lint app/Resources/views/article/recent_list.html.twig

    # or by directory:
    $ php app/console twig:lint app/Resources/views

Final Thoughts
--------------

The templating engine in Symfony is a powerful tool that can be used each time
you need to generate presentational content in HTML, XML or any other format.
And though templates are a common way to generate content in a controller,
their use is not mandatory. The ``Response`` object returned by a controller
can be created with or without the use of a template::

    // creates a Response object whose content is the rendered template
    $response = $this->render('article/index.html.twig');

    // creates a Response object whose content is simple text
    $response = new Response('response content');

Symfony's templating engine is very flexible and two different template
renderers are available by default: the traditional *PHP* templates and the
sleek and powerful *Twig* templates. Both support a template hierarchy and
come packaged with a rich set of helper functions capable of performing
the most common tasks.

Overall, the topic of templating should be thought of as a powerful tool
that's at your disposal. In some cases, you may not need to render a template,
and in Symfony, that's absolutely fine.

Learn more from the Cookbook
----------------------------

* :doc:`/cookbook/templating/PHP`
* :doc:`/cookbook/templating/namespaced_paths`
* :doc:`/cookbook/controller/error_pages`
* :doc:`/cookbook/templating/twig_extension`
* :doc:`/cookbook/templating/global_variables`
* :doc:`/cookbook/templating/render_without_controller`

.. _`Twig`: http://twig.sensiolabs.org
.. _`here`: http://twig.sensiolabs.org/documentation
.. _`Twig extensions`: http://twig.sensiolabs.org/doc/api.html#using-extensions
.. _`functions`: http://twig.sensiolabs.org/doc/functions/index.html
.. _`filters`: http://twig.sensiolabs.org/doc/filters/index.html
.. _`tags`: http://twig.sensiolabs.org/doc/tags/index.html
.. _`tests`: http://twig.sensiolabs.org/doc/tests/index.html
.. _`add your own extensions`: http://twig.sensiolabs.org/doc/advanced.html
.. _`with_context`: http://twig.sensiolabs.org/doc/functions/include.html
.. _`include() function`: http://twig.sensiolabs.org/doc/functions/include.html
.. _`{% include %} tag`: http://twig.sensiolabs.org/doc/tags/include.html
.. _`hinclude.js`: http://mnot.github.io/hinclude/
.. _`KnpBundles.com`: http://knpbundles.com
.. _`Cross Site Scripting`: https://en.wikipedia.org/wiki/Cross-site_scripting
.. _`Output Escaping`: http://twig.sensiolabs.org/doc/api.html#escaper-extension