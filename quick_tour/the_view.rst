The View
========

After reading the first part of this tutorial, you have decided that Symfony2
was worth another 10 minutes. Good for you. In this second part, you will
learn more about the Symfony2 template system. As seen before, Symfony2 uses
PHP as its default template engine but adds some nice features on top of if to
make it more powerful.

Instead of PHP, you can also use `Twig`_ (it makes your templates more concise
and more friendly for web designers). If you prefer to use `Twig`, read the
alternative :doc:`View with Twig <the_view_with_twig>` chapter.

.. index::
  single: Templating; Layout
  single: Layout

Decorating Templates
--------------------

More often than not, templates in a project share common elements, like the
well-know header and footer. In Symfony, we like to think about this problem
differently: a template can be decorated by another one.

The ``index`` template is decorated by ``layout.php``, thanks to the
``extend()`` call:

.. code-block:: html+php

    <!-- src/Application/HelloBundle/Resources/views/Hello/index.php -->
    <?php $view->extend('HelloBundle::layout') ?>

    Hello <?php echo $name ?>!

The ``HelloBundle::layout`` notation sounds familiar, doesn't it? It is the same
notation as for referencing a template. The ``::`` part simply means that the
controller element is empty, so the corresponding file is directly stored under
``views/``.

Now, let's have a look at the ``layout.php`` file:

.. code-block:: html+php

    <!-- src/Application/HelloBundle/Resources/views/layout.php -->
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        </head>
        <body>
            <?php $view->slots->output('_content') ?>
        </body>
    </html>

The ``$view->slots->output('_content')`` expression is replaced by the content
of the child template, ``index.php`` (more on slots in the next section).

As you can see, Symfony provides methods on a mysterious ``$view`` object. In a
template, the ``$view`` variable is always available and refers to a special
object that provides a bunch of methods and properties that make the template
engine tick.

.. tip::
   Symfony also supports multiple decoration levels: a layout can itself be
   decorated by another one. This technique is really useful for large
   projects and is made even more powerful when used in combination with
   slots.

.. index::
   single: Templating; Slot
   single: Slot

Slots
-----

A slot is a snippet of code, defined in a template, and reusable in any layout
decorating the template. In the index template, define a ``title`` slot:

.. code-block:: html+php

    <!-- src/Application/HelloBundle/Resources/views/Hello/index.php -->
    <?php $view->extend('HelloBundle::layout') ?>

    <?php $view->slots->set('title', 'Hello World app') ?>

    Hello <?php echo $name ?>!

And change the layout to output the title in the header:

.. code-block:: html+php

    <!-- src/Application/HelloBundle/Resources/views/layout.php -->
    <html>
        <head>
            <title><?php $view->slots->output('title', 'Default Title') ?></title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        </head>
        <body>
            <?php $view->slots->output('_content') ?>
        </body>
    </html>

The ``output()`` method inserts the content of a slot and optionally takes a
default value if the slot is not defined. And ``_content`` is just a special
slot that contains the rendered child template.

For large slots, there is also an extended syntax:

.. code-block:: html+php

    <?php $view->slots->start('title') ?>
        Some large amount of HTML
    <?php $view->slots->stop() ?>

.. index::
   single: Templating; Include

Include other Templates
-----------------------

The best way to share a snippet of code between several distinct templates is
to define a template that can then be included into another one.

Create a ``hello.php`` template:

.. code-block:: html+php

    <!-- src/Application/HelloBundle/Resources/views/Hello/hello.php -->
    Hello <?php echo $name ?>!

And change the ``index.php`` template to include it:

.. code-block:: html+php

    <!-- src/Application/HelloBundle/Resources/views/Hello/index.php -->
    <?php $view->extend('HelloBundle::layout') ?>

    <?php echo $view->render('HelloBundle:Hello:hello', array('name' => $name)) ?>

The ``render()`` method evaluates and returns the content of another template
(this is the exact same method as the one used in the controller).

.. index::
   single: Templating; Embedding Pages

Embed other Actions
-------------------

And what if you want to embed the result of another action in a template?
That's very useful when working with Ajax, or when the embedded template needs
some variable not available in the main template.

If you create a ``fancy`` action, and want to include it into the ``index``
template, simply use the following code:

.. code-block:: html+php

    <!-- src/Application/HelloBundle/Resources/views/Hello/index.php -->
    <?php $view->actions->output('HelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green')) ?>

Here, the ``HelloBundle:Hello:fancy`` string refers to the ``fancy`` action of the
``Hello`` controller::

    // src/Application/HelloBundle/Controller/HelloController.php

    class HelloController extends Controller
    {
        public function fancyAction($name, $color)
        {
            // create some object, based on the $color variable
            $object = ...;

            return $this->render('HelloBundle:Hello:fancy', array('name' => $name, 'object' => $object));
        }

        // ...
    }

But where is the ``$view->actions`` property defined? Like ``$view->slots``, it's
called a template helper, and the next section tells you more about those.

.. index::
   single: Templating; Helpers

Template Helpers
----------------

The Symfony templating system can be easily extended via helpers. Helpers are
PHP objects that provide features useful in a template context. ``actions`` and
``slots`` are two of the built-in Symfony helpers.

Links between Pages
~~~~~~~~~~~~~~~~~~~

Speaking of web applications, creating links between different pages is a
must. Instead of hardcoding URLs in templates, the ``router`` helper knows how
to generate URLs based on the routing configuration. That way, all your URLs
can be easily updated by changing the configuration:

.. code-block:: html+php

    <a href="<?php echo $view->router->generate('hello', array('name' => 'Thomas')) ?>">
        Greet Thomas!
    </a>

The ``generate()`` method takes the route name and an array of values as
arguments. The route name is the main key under which routes are referenced
and the values are the route pattern placeholder values:

.. code-block:: yaml

    # src/Application/HelloBundle/Resources/config/routing.yml
    hello: # The route name
        pattern:  /hello/:name
        defaults: { _bundle: HelloBundle, _controller: Hello, _action: index }

Using Assets: images, JavaScripts, and stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What would the Internet be without images, JavaScripts, and stylesheets?
Symfony provides three helpers to deal with them easily: ``assets``,
``javascripts``, and ``stylesheets``:

.. code-block:: html+php

    <link href="<?php echo $view->assets->getUrl('css/blog.css') ?>" rel="stylesheet" type="text/css" />

    <img src="<?php echo $view->assets->getUrl('images/logo.png') ?>" />

The ``assets`` helper's main purpose is to make your application more portable.
Thanks to this helper, you can move the application root directory anywhere under your
web root directory without changing anything in your templates' code.

Similarly, you can manage your stylesheets and JavaScripts with the
``stylesheets`` and ``JavaScripts`` helpers:

.. code-block:: html+php

    <?php $view->javascripts->add('js/product.js') ?>
    <?php $view->stylesheets->add('css/product.css') ?>

The ``add()`` method defines dependencies. To actually output these assets, you
need to also add the following code in your main layout:

.. code-block:: html+php

    <?php echo $view->javascripts ?>
    <?php echo $view->stylesheets ?>

Final Thoughts
--------------

The Symfony templating system is simple yet powerful. Thanks to layouts,
slots, templating and action inclusions, it is very easy to organize your
templates in a logical and extensible way.

You have only been working with Symfony for about 20 minutes, and you can
already do pretty amazing stuff with it. That's the power of Symfony. Learning
the basics is easy, and you will soon learn that this simplicity is hidden
under a very flexible architecture.

But I get ahead of myself. First, you need to learn more about the controller
and that's exactly the topic of the next part of this tutorial. Ready for
another 10 minutes with Symfony?

.. _Twig: http://www.twig-project.org/
