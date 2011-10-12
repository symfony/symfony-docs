The View
========

After reading the first part of this tutorial, you have decided that Symfony2
was worth another 10 minutes. Great choice! In this second part, you will
learn more about the Symfony2 template engine, `Twig`_. Twig is a flexible,
fast, and secure template engine for PHP. It makes your templates more
readable and concise; it also makes them more friendly for web designers.

.. note::

    Instead of Twig, you can also use :doc:`PHP </cookbook/templating/PHP>`
    for your templates. Both template engines are supported by Symfony2.

Getting familiar with Twig
--------------------------

.. tip::

    If you want to learn Twig, we highly recommend you to read its official
    `documentation`_. This section is just a quick overview of the main
    concepts.

A Twig template is a text file that can generate any type of content (HTML,
XML, CSV, LaTeX, ...). Twig defines two kinds of delimiters:

* ``{{ ... }}``: Prints a variable or the result of an expression;

* ``{% ... %}``: Controls the logic of the template; it is used to execute
  ``for`` loops and ``if`` statements, for example.

Below is a minimal template that illustrates a few basics, using two variables
``page_title`` and ``navigation``, which would be passed into the template:

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


.. tip::

   Comments can be included inside templates using the ``{# ... #}`` delimiter.

To render a template in Symfony, use the ``render`` method from within a controller
and pass it any variables needed in the template::

    $this->render('AcmeDemoBundle:Demo:hello.html.twig', array(
        'name' => $name,
    ));

Variables passed to a template can be strings, arrays, or even objects. Twig
abstracts the difference between them and lets you access "attributes" of a
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

The ``hello.html.twig`` template inherits from ``layout.html.twig``, thanks to
the ``extends`` tag:

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
is directly stored under the ``Resources/views/`` directory.

Now, let's have a look at a simplified ``layout.html.twig``:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/layout.html.twig #}
    <div class="symfony-content">
        {% block content %}
        {% endblock %}
    </div>

The ``{% block %}`` tags define blocks that child templates can fill in. All
the block tag does is to tell the template engine that a child template may
override those portions of the template.

In this example, the ``hello.html.twig`` template overrides the ``content``
block, meaning that the "Hello Fabien" text is rendered inside the ``div.symfony-content``
element.

Using Tags, Filters, and Functions
----------------------------------

One of the best feature of Twig is its extensibility via tags, filters, and
functions. Symfony2 comes bundled with many of these built-in to ease the
work of the template designer.

Including other Templates
~~~~~~~~~~~~~~~~~~~~~~~~~

The best way to share a snippet of code between several distinct templates is
to create a new template that can then be included from other templates.

Create an ``embedded.html.twig`` template:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/embedded.html.twig #}
    Hello {{ name }}

And change the ``index.html.twig`` template to include it:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/hello.html.twig #}
    {% extends "AcmeDemoBundle::layout.html.twig" %}

    {# override the body block from embedded.html.twig #}
    {% block content %}
        {% include "AcmeDemoBundle:Demo:embedded.html.twig" %}
    {% endblock %}

Embedding other Controllers
~~~~~~~~~~~~~~~~~~~~~~~~~~~

And what if you want to embed the result of another controller in a template?
That's very useful when working with Ajax, or when the embedded template needs
some variable not available in the main template.

Suppose you've created a ``fancy`` action, and you want to include it inside
the ``index`` template. To do this, use the ``render`` tag:

.. code-block:: jinja

    {# src/Acme/DemoBundle/Resources/views/Demo/index.html.twig #}
    {% render "AcmeDemoBundle:Demo:fancy" with { 'name': name, 'color': 'green' } %}

Here, the ``AcmeDemoBundle:Demo:fancy`` string refers to the ``fancy`` action
of the ``Demo`` controller. The arguments (``name`` and ``color``) act like
simulated request variables (as if the ``fancyAction`` were handling a whole
new request) and are made available to the controller::

    // src/Acme/DemoBundle/Controller/DemoController.php

    class DemoController extends Controller
    {
        public function fancyAction($name, $color)
        {
            // create some object, based on the $color variable
            $object = ...;

            return $this->render('AcmeDemoBundle:Demo:fancy.html.twig', array('name' => $name, 'object' => $object));
        }

        // ...
    }

Creating Links between Pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Speaking of web applications, creating links between pages is a must. Instead
of hardcoding URLs in templates, the ``path`` function knows how to generate
URLs based on the routing configuration. That way, all your URLs can be easily
updated by just changing the configuration:

.. code-block:: html+jinja

    <a href="{{ path('_demo_hello', { 'name': 'Thomas' }) }}">Greet Thomas!</a>

The ``path`` function takes the route name and an array of parameters as
arguments. The route name is the main key under which routes are referenced
and the parameters are the values of the placeholders defined in the route
pattern::

    // src/Acme/DemoBundle/Controller/DemoController.php
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

    /**
     * @Route("/hello/{name}", name="_demo_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

.. tip::

    The ``url`` function generates *absolute* URLs: ``{{ url('_demo_hello', {
    'name': 'Thomas' }) }}``.

Including Assets: images, JavaScripts, and stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What would the Internet be without images, JavaScripts, and stylesheets?
Symfony2 provides the ``asset`` function to deal with them easily:

.. code-block:: jinja

    <link href="{{ asset('css/blog.css') }}" rel="stylesheet" type="text/css" />

    <img src="{{ asset('images/logo.png') }}" />

The ``asset`` function's main purpose is to make your application more portable.
Thanks to this function, you can move the application root directory anywhere
under your web root directory without changing anything in your template's
code.

Escaping Variables
------------------

Twig is configured to automatically escapes all output by default. Read Twig
`documentation`_ to learn more about output escaping and the Escaper
extension.

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
and that's exactly the topic of the :doc:`next part of this tutorial<the_controller>`.
Ready for another 10 minutes with Symfony2?

.. _Twig:          http://twig.sensiolabs.org/
.. _documentation: http://twig.sensiolabs.org/documentation
