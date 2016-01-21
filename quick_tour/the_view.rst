The View
========

After reading the first part of this tutorial, you have decided that Symfony
was worth another 10 minutes. In this second part, you will learn more about
`Twig`_, the fast, flexible and secure template engine for PHP applications.
Twig makes your templates more readable and concise; it also makes them
more friendly for web designers.

Getting Familiar with Twig
--------------------------

The official `Twig documentation`_ is the best resource to learn everything
about this template engine. This section just gives you a quick overview
of its main concepts.

A Twig template is a text file that can generate any type of content (HTML,
CSS, JavaScript, XML, CSV, LaTeX, etc.) Twig elements are separated from
the rest of the template contents using any of these delimiters:

``{{ ... }}``
    Prints the content of a variable or the result of evaluating an expression;

``{% ... %}``
    Controls the logic of the template; it is used for example to execute
    ``for`` loops and ``if`` statements.

``{# ... #}``
    Allows including comments inside templates. Contrary to HTML comments,
    they aren't included in the rendered template.

Below is a minimal template that illustrates a few basics, using two variables
``page_title`` and ``navigation``, which would be passed into the template:

.. code-block:: html+twig

    <!DOCTYPE html>
    <html>
        <head>
            <title>{{ page_title }}</title>
        </head>
        <body>
            <h1>{{ page_title }}</h1>

            <ul id="navigation">
                {% for item in navigation %}
                    <li><a href="{{ item.url }}">{{ item.label }}</a></li>
                {% endfor %}
            </ul>
        </body>
    </html>

To render a template in Symfony, use the ``render`` method from within a
controller. If the template needs variables to generate its contents, pass
them as an array using the second optional argument::

    $this->render('default/index.html.twig', array(
        'variable_name' => 'variable_value',
    ));

Variables passed to a template can be strings, arrays or even objects. Twig
abstracts the difference between them and lets you access "attributes" of
a variable with the dot (``.``) notation. The following code listing shows
how to display the content of a variable passed by the controller depending
on its type:

.. code-block:: twig

    {# 1. Simple variables #}
    {# $this->render('template.html.twig', array(
           'name' => 'Fabien')
       ) #}
    {{ name }}

    {# 2. Arrays #}
    {# $this->render('template.html.twig', array(
           'user' => array('name' => 'Fabien'))
       ) #}
    {{ user.name }}

    {# alternative syntax for arrays #}
    {{ user['name'] }}

    {# 3. Objects #}
    {# $this->render('template.html.twig', array(
           'user' => new User('Fabien'))
       ) #}
    {{ user.name }}
    {{ user.getName }}

    {# alternative syntax for objects #}
    {{ user.name() }}
    {{ user.getName() }}

Decorating Templates
--------------------

More often than not, templates in a project share common elements, like
the well-known header and footer. Twig solves this problem elegantly with
a concept called "template inheritance". This feature allows you to build
a base template that contains all the common elements of your site and
defines "blocks" of contents that child templates can override.

The ``index.html.twig`` template uses the ``extends`` tag to indicate that
it inherits from the ``base.html.twig`` template:

.. code-block:: html+twig

    {# app/Resources/views/default/index.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <h1>Welcome to Symfony!</h1>
    {% endblock %}

Open the ``app/Resources/views/base.html.twig`` file that corresponds to
the ``base.html.twig`` template and you'll find the following Twig code:

.. code-block:: html+twig

    {# app/Resources/views/base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8" />
            <title>{% block title %}Welcome!{% endblock %}</title>
            {% block stylesheets %}{% endblock %}
            <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
        </head>
        <body>
            {% block body %}{% endblock %}
            {% block javascripts %}{% endblock %}
        </body>
    </html>

The ``{% block %}`` tags tell the template engine that a child template
may override those portions of the template. In this example, the
``index.html.twig`` template overrides the ``body`` block, but not the
``title`` block, which will display the default content defined in the
``base.html.twig`` template.

Using Tags, Filters and Functions
---------------------------------

One of the best features of Twig is its extensibility via tags, filters
and functions. Take a look at the following sample template that uses filters
extensively to modify the information before displaying it to the user:

.. code-block:: twig

    <h1>{{ article.title|capitalize }}</h1>

    <p>{{ article.content|striptags|slice(0, 255) }} ...</p>

    <p>Tags: {{ article.tags|sort|join(", ") }}</p>

    <p>Activate your account before {{ 'next Monday'|date('M j, Y') }}</p>

Don't forget to check out the official `Twig documentation`_ to learn everything
about filters, functions and tags.

Including other Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

The best way to share a snippet of code between several templates is to
create a new template fragment that can then be included from other templates.

Imagine that we want to display ads on some pages of our application. First,
create a ``banner.html.twig`` template:

.. code-block:: twig

    {# app/Resources/views/ads/banner.html.twig #}
    <div id="ad-banner">
        ...
    </div>

To display this ad on any page, include the ``banner.html.twig`` template
using the ``include()`` function:

.. code-block:: html+twig

    {# app/Resources/views/default/index.html.twig #}
    {% extends 'base.html.twig' %}

    {% block body %}
        <h1>Welcome to Symfony!</h1>

        {{ include('ads/banner.html.twig') }}
    {% endblock %}

Embedding other Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~

And what if you want to embed the result of another controller in a template?
That's very useful when working with Ajax, or when the embedded template
needs some variable not available in the main template.

Suppose you've created a ``topArticlesAction`` controller method to display
the most popular articles of your website. If you want to "render" the result
of that method (usually some HTML content) inside the ``index`` template,
use the ``render()`` function:

.. code-block:: twig

    {# app/Resources/views/index.html.twig #}
    {{ render(controller('AppBundle:Default:topArticles')) }}

Here, the ``render()`` and ``controller()`` functions use the special
``AppBundle:Default:topArticles`` syntax to refer to the ``topArticlesAction``
action of the ``Default`` controller (the ``AppBundle`` part will be explained
later)::

    // src/AppBundle/Controller/DefaultController.php

    class DefaultController extends Controller
    {
        public function topArticlesAction()
        {
            // look for the most popular articles in the database
            $articles = ...;

            return $this->render('default/top_articles.html.twig', array(
                'articles' => $articles,
            ));
        }

        // ...
    }

Creating Links between Pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Creating links between pages is a must for web applications. Instead of
hardcoding URLs in templates, the ``path`` function knows how to generate
URLs based on the routing configuration. That way, all your URLs can be
easily updated by just changing the configuration:

.. code-block:: html+twig

    <a href="{{ path('homepage') }}">Return to homepage</a>

The ``path`` function takes the route name as the first argument and you
can optionally pass an array of route parameters as the second argument.

.. tip::

    The ``url`` function is very similar to the ``path`` function, but generates
    *absolute* URLs, which is very handy when rendering emails and RSS files:
    ``<a href="{{ url('homepage') }}">Visit our website</a>``.

Including Assets: Images, JavaScripts and Stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What would the Internet be without images, JavaScripts and stylesheets?
Symfony provides the ``asset`` function to deal with them easily:

.. code-block:: twig

    <link href="{{ asset('css/blog.css') }}" rel="stylesheet" type="text/css" />

    <img src="{{ asset('images/logo.png') }}" />

The ``asset()`` function looks for the web assets inside the ``web/`` directory.
If you store them in another directory, read :doc:`this article </cookbook/assetic/asset_management>`
to learn how to manage web assets.

Using the ``asset`` function, your application is more portable. The reason
is that you can move the application root directory anywhere under your
web root directory without changing anything in your template's code.

Final Thoughts
--------------

Twig is simple yet powerful. Thanks to layouts, blocks, templates and action
inclusions, it is very easy to organize your templates in a logical and
extensible way.

You have only been working with Symfony for about 20 minutes, but you can
already do pretty amazing stuff with it. That's the power of Symfony. Learning
the basics is easy and you will soon learn that this simplicity is hidden
under a very flexible architecture.

But I'm getting ahead of myself. First, you need to learn more about the
controller and that's exactly the topic of the :doc:`next part of this tutorial
<the_controller>`. Ready for another 10 minutes with Symfony?

.. _Twig: http://twig.sensiolabs.org/
.. _Twig documentation: http://twig.sensiolabs.org/documentation
