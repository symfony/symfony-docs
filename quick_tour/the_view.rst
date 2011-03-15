The View
========

After reading the first part of this tutorial, you have decided that Symfony2
was worth another 10 minutes. Good for you. In this second part, you will
learn more about the Symfony2 template engine, `Twig`_. Twig is a flexible,
fast, and secure template engine for PHP. It makes your templates more
readable and concise; it also makes them more friendly for web designers.

.. note::

    Instead of Twig, you can also use :doc:`PHP </cookbook/templating/PHP>`
    for your templates. Both template engines are supported by Symfony2 and
    have the same level of support.

.. index::
   single: Twig
   single: View; Twig

Twig, a Quick Overview
----------------------

.. tip::

    If you want to learn Twig, we highly recommend you to read its official
    `documentation`_. This section is just a quick overview of the main
    concepts.

A Twig template is a text file that can generate any text-based format (HTML,
XML, CSV, LaTeX, ...). Twig defines two kinds of delimiters:

* ``{{ ... }}``: Outputs a variable or the result of an expression;

* ``{% ... %}``: A tag that controls the logic of the template; it is used to
  execute ``for`` loops or ``if`` statements for instance.

Below is a minimal template that illustrates a few basics:

.. code-block:: html+jinja

    <!DOCTYPE html>
    <html>
        <head>
            <title>My Webpage</title>
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

Variables passed to a template can be strings, arrays, or even objects. Twig
abstracts the difference between them and let's you access "attributes" of a
variable with the dot (``.``) notation:

.. code-block:: jinja

    {# array('name' => 'Fabien') #}
    {{ name }}

    {# array('user' => array('name' => 'Fabien')) #}
    {{ user.name }}

    {# force array lookup #}
    {{ user['name'] }}

    {# array('user' => new User('Fabien')) #}
    {{ user.name }}
    {{ user.getName }}

    {# force method name lookup #}
    {{ user.name() }}
    {{ user.getName() }}

    {# pass arguments to a method #}
    {{ user.date('Y-m-d') }}

.. note::

    It's important to know that the curly braces are not part of the variable
    but the print statement. If you access variables inside tags don't put the
    braces around.

Decorating Templates
--------------------

More often than not, templates in a project share common elements, like the
well-known header and footer. In Symfony2, we like to think about this problem
differently: a template can be decorated by another one. This works exactly
the same as PHP classes: template inheritance allows you to build a base
"layout" template that contains all the common elements of your site and
defines "blocks" that child templates can override.

The ``index.html.twig`` template inherits from ``layout.html.twig``, thanks to
the ``extends`` tag:

.. code-block:: jinja

    {# src/Sensio/HelloBundle/Resources/views/Hello/index.html.twig #}
    {% extends "HelloBundle::layout.html.twig" %}

    {% block content %}
        Hello {{ name }}!
    {% endblock %}

The ``HelloBundle::layout.html.twig`` notation sounds familiar, doesn't it? It
is the same notation used to reference a regular template. The ``::`` part
simply means that the controller element is empty, so the corresponding file
is directly stored under ``views/``.

Now, let's have a look at the ``layout.html.twig`` file:

.. code-block:: jinja

    {% extends "::base.html.twig" %}

    {% block body %}
        <h1>Hello Application</h1>

        {% block content %}{% endblock %}
    {% endblock %}

The ``{% block %}`` tags define two blocks (``body`` and ``content``) that
child templates can fill in. All the block tag does is to tell the template
engine that a child template may override those portions of the template. The
``index.html.twig`` template overrides the ``content`` block. The other one is
defined in a base layout as the layout is itself decorated by another one.
When the bundle part of the template name is empty (``::base.html.twig``),
views are looked for in the ``app/views/`` directory. This directory store
global views for your entire project:

.. code-block:: jinja

    {# app/views/base.html.twig #}
    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>{% block title %}Hello Application{% endblock %}</title>
        </head>
        <body>
            {% block body '' %}
        </body>
    </html>

Tags, Filters, and Functions
----------------------------

One of the best feature of Twig is its extensibility via tags, filters, and
functions; Symfony2 comes bundled with many built-in ones to ease the web
designer work.

Including other Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

The best way to share a snippet of code between several distinct templates is
to define a template that can then be included into another one.

Create a ``hello.html.twig`` template:

.. code-block:: jinja

    {# src/Sensio/HelloBundle/Resources/views/Hello/hello.html.twig #}
    Hello {{ name }}

And change the ``index.html.twig`` template to include it:

.. code-block:: jinja

    {# src/Sensio/HelloBundle/Resources/views/Hello/index.html.twig #}
    {% extends "HelloBundle::layout.html.twig" %}

    {# override the body block from index.html.twig #}
    {% block body %}
        {% include "HelloBundle:Hello:hello.html.twig" %}
    {% endblock %}

Embedding other Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~

And what if you want to embed the result of another controller in a template?
That's very useful when working with Ajax, or when the embedded template needs
some variable not available in the main template.

If you create a ``fancy`` action, and want to include it into the ``index``
template, use the ``render`` tag:

.. code-block:: jinja

    {# src/Sensio/HelloBundle/Resources/views/Hello/index.html.twig #}
    {% render "HelloBundle:Hello:fancy" with { 'name': name, 'color': 'green' } %}

Here, the ``HelloBundle:Hello:fancy`` string refers to the ``fancy`` action of
the ``Hello`` controller, and the argument is used as simulated request path
values::

    // src/Sensio/HelloBundle/Controller/HelloController.php

    class HelloController extends Controller
    {
        public function fancyAction($name, $color)
        {
            // create some object, based on the $color variable
            $object = ...;

            return $this->render('HelloBundle:Hello:fancy.html.twig', array('name' => $name, 'object' => $object));
        }

        // ...
    }

Creating Links between Pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Speaking of web applications, creating links between pages is a must. Instead
of hardcoding URLs in templates, the ``path`` function knows how to generate
URLs based on the routing configuration. That way, all your URLs can be easily
updated by just changing the configuration:

.. code-block:: jinja

    <a href="{{ path('hello', { 'name': 'Thomas' }) }}">Greet Thomas!</a>

The ``path`` function takes the route name and an array of parameters as
arguments. The route name is the main key under which routes are referenced
and the parameters are the values of the placeholders defined in the route
pattern:

.. code-block:: yaml

    # src/Sensio/HelloBundle/Resources/config/routing.yml
    hello: # The route name
        pattern:  /hello/{name}
        defaults: { _controller: HelloBundle:Hello:index }

.. tip::

    The ``url`` function generates *absolute* URLs: ``{{ url('hello', {
    'name': 'Thomas' }) }}``.

Including Assets: images, JavaScripts, and stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What would the Internet be without images, JavaScripts, and stylesheets?
Symfony2 provides the ``asset`` function to deal with them easily:

.. code-block:: jinja

    <link href="{{ asset('css/blog.css') }}" rel="stylesheet" type="text/css" />

    <img src="{{ asset('images/logo.png') }}" />

The ``asset`` function main purpose is to make your application more portable.
Thanks to this function, you can move the application root directory anywhere
under your web root directory without changing anything in your template's
code.

Output Escaping
---------------

Twig is configured to automatically escapes all output by default. Read Twig
`documentation`_ to learn more about output escaping and the Escaper
extension.

Final Thoughts
--------------

Twig is simple yet powerful. Thanks to layouts, blocks, templates and action
inclusions, it is very easy to organize your templates in a logical and
extensible way.

You have only been working with Symfony2 for about 20 minutes, and you can
already do pretty amazing stuff with it. That's the power of Symfony2. Learning
the basics is easy, and you will soon learn that this simplicity is hidden
under a very flexible architecture.

But I get ahead of myself. First, you need to learn more about the controller
and that's exactly the topic of the next part of this tutorial. Ready for
another 10 minutes with Symfony2?

.. _Twig:          http://www.twig-project.org/
.. _documentation: http://www.twig-project.org/documentation
