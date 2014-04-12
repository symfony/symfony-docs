The View
========

After reading the first part of this tutorial, you have decided that Symfony2
was worth another 10 minutes. In this second part, you will learn more about
`Twig`_, the fast, flexible, and secure template engine for PHP. Twig makes your
templates more readable and concise; it also makes them more friendly for web
designers.

Getting familiar with Twig
--------------------------

The official `Twig documentation`_ is the best resource to learn everything
about this new template engine. This section just gives you a quick overview of
its main concepts.

A Twig template is a text file that can generate any type of content (HTML, CSS,
JavaScript, XML, CSV, LaTeX, ...). Twig elements are separated from the rest of
the template contents using any of these delimiters:

* ``{{ ... }}``: prints the content of a variable or the result of an expression;

* ``{% ... %}``: controls the logic of the template; it is used for example to
  execute ``for`` loops and ``if`` statements;

* ``{# ... #}``: allows including comments inside templates.

Below is a minimal template that illustrates a few basics, using two variables
``page_title`` and ``navigation``, which would be passed into the template:

.. code-block:: html+jinja

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

To render a template in Symfony, use the ``render`` method from within a controller
and pass the variables needed as an array using the optional second argument::

    $this->render('AcmeDemoBundle:Demo:hello.html.twig', array(
        'name' => $name,
    ));

Variables passed to a template can be strings, arrays, or even objects. Twig
abstracts the difference between them and lets you access "attributes" of a
variable with the dot (``.``) notation. The following code listing shows how to
display the content of a variable depending on the type of the variable passed
by the controller:

.. code-block:: jinja

    {# 1. Simple variables #}
    {# array('name' => 'Fabien') #}
    {{ name }}

    {# 2. Arrays #}
    {# array('user' => array('name' => 'Fabien')) #}
    {{ user.name }}

    {# alternative syntax for arrays #}
    {{ user['name'] }}

    {# 3. Objects #}
    {# array('user' => new User('Fabien')) #}
    {{ user.name }}
    {{ user.getName }}

    {# alternative syntax for objects #}
    {{ user.name() }}
    {{ user.getName() }}

Decorating Templates
--------------------

More often than not, templates in a project share common elements, like the
well-known header and footer. Twig solves this problem elegantly with a concept
called "template inheritance". This feature allows you to build a base "layout"
template that contains all the common elements of your site and defines "blocks"
that child templates can override.

The ``hello.html.twig`` template uses the ``extends`` tag to indicate that it
inherits from the common ``layout.html.twig`` template:

.. code-block:: html+jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig #}
    {% extends "AcmeDemoBundle::layout.html.twig" %}

    {% block title "Hello " ~ name %}

    {% block content %}
        <h1>Hello {{ name }}!</h1>
    {% endblock %}

The ``AcmeDemoBundle::layout.html.twig`` notation sounds familiar, doesn't it?
It is the same notation used to reference a regular template. The ``::`` part
simply means that the controller element is empty, so the corresponding file
is directly stored under the ``Resources/views/`` directory of the bundle.

Now, simplify the ``layout.html.twig`` template:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/layout.html.twig #}
    <div>
        {% block content %}
        {% endblock %}
    </div>

The ``{% block %}`` tags tell the template engine that a child template may
override those portions of the template. In this example, the ``hello.html.twig``
template overrides the ``content`` block, meaning that the "Hello Fabien" text
is rendered inside the ``<div>`` element.

Using Tags, Filters, and Functions
----------------------------------

One of the best feature of Twig is its extensibility via tags, filters, and
functions. Take a look at the following sample template that uses filters
extensively to modify the information before displaying it to the user:

.. code-block:: jinja

    <h1>{{ article.title|trim|capitalize }}</h1>

    <p>{{ article.content|striptags|slice(0, 1024) }}</p>

    <p>Tags: {{ article.tags|sort|join(", ") }}</p>

    <p>Next article will be published on {{ 'next Monday'|date('M j, Y')}}</p>

Don't forget to check out the official `Twig documentation`_ to learn everything
about filters, functions and tags.

Including other Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

The best way to share a snippet of code between several templates is to create a
new template fragment that can then be included from other templates.

First, create an ``embedded.html.twig`` template:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/embedded.html.twig #}
    Hello {{ name }}

And change the ``index.html.twig`` template to include it:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig #}
    {% extends "AcmeDemoBundle::layout.html.twig" %}

    {# override the body block from embedded.html.twig #}
    {% block content %}
        {{ include("AcmeDemoBundle:Demo:embedded.html.twig") }}
    {% endblock %}

Embedding other Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~

And what if you want to embed the result of another controller in a template?
That's very useful when working with Ajax, or when the embedded template needs
some variable not available in the main template.

Suppose you've created a ``topArticlesAction`` controller method to display the
most popular articles of your website. If you want to "render" the result of
that method (e.g. ``HTML``) inside the ``index`` template, use the ``render``
function:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/index.html.twig #}
    {{ render(controller("AcmeDemoBundle:Demo:topArticles", {'num': 10})) }}

Here, the ``AcmeDemoBundle:Demo:topArticles`` string refers to the
``topArticlesAction`` action of the ``Demo`` controller, and the ``num``
argument is made available to the controller::

    // src/Acme/DemoBundle/Controller/DemoController.php

    class DemoController extends Controller
    {
        public function topArticlesAction($num)
        {
            // look for the $num most popular articles in the database
            $articles = ...;

            return $this->render('AcmeDemoBundle:Demo:topArticles.html.twig', array(
                'articles' => $articles,
            ));
        }

        // ...
    }

Creating Links between Pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Creating links between pages is a must for web applications. Instead of
hardcoding URLs in templates, the ``path`` function knows how to generate
URLs based on the routing configuration. That way, all your URLs can be easily
updated by just changing the configuration:

.. code-block:: html+jinja

    <a href="{{ path('_demo_hello', { 'name': 'Thomas' }) }}">Greet Thomas!</a>

The ``path`` function takes the route name and an array of parameters as
arguments. The route name is the key under which routes are defined and the
parameters are the values of the variables defined in the route pattern::

    // src/Acme/DemoBundle/Controller/DemoController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    // ...

    /**
     * @Route("/hello/{name}", name="_demo_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

.. tip::

    The ``url`` function is very similar to the ``path`` function, but generates
    *absolute* URLs, which is very handy when rendering emails and RSS files:
    ``{{ url('_demo_hello', {'name': 'Thomas'}) }}``.

Including Assets: Images, JavaScripts and Stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What would the Internet be without images, JavaScripts, and stylesheets?
Symfony2 provides the ``asset`` function to deal with them easily:

.. code-block:: jinja

    <link href="{{ asset('css/blog.css') }}" rel="stylesheet" type="text/css" />

    <img src="{{ asset('images/logo.png') }}" />

The ``asset`` function's main purpose is to make your application more portable.
Thanks to this function, you can move the application root directory anywhere
under your web root directory without changing anything in your template's
code.

Final Thoughts
--------------

Twig is simple yet powerful. Thanks to layouts, blocks, templates and action
inclusions, it is very easy to organize your templates in a logical and
extensible way. However, if you're not comfortable with Twig, you can always
use PHP templates inside Symfony without any issues.

You have only been working with Symfony2 for about 20 minutes, but you can
already do pretty amazing stuff with it. That's the power of Symfony2. Learning
the basics is easy, and you will soon learn that this simplicity is hidden
under a very flexible architecture.

But I'm getting ahead of myself. First, you need to learn more about the controller
and that's exactly the topic of the :doc:`next part of this tutorial <the_controller>`.
Ready for another 10 minutes with Symfony2?

.. _Twig:               http://twig.sensiolabs.org/
.. _Twig documentation: http://twig.sensiolabs.org/documentation
