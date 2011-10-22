.. index::
   single: PHP Templates

How to use PHP instead of Twig for Templates
============================================

Even if Symfony2 defaults to Twig for its template engine, you can still use
plain PHP code if you want. Both templating engines are supported equally in
Symfony2. Symfony2 adds some nice features on top of PHP to make writing
templates with PHP more powerful.

Rendering PHP Templates
-----------------------

If you want to use the PHP templating engine, first, make sure to enable it in
your application configuration file:

.. configuration-block::

    .. code-block:: yaml
    
        # app/config/config.yml
        framework:
            # ...
            templating:    { engines: ['twig', 'php'] }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <framework:config ... >
            <!-- ... -->
            <framework:templating ... >
                <framework:engine id="twig" />
                <framework:engine id="php" />
            </framework:templating>
        </framework:config>

    .. code-block:: php

        $container->loadFromExtension('framework', array(
            // ...
            'templating'      => array(
                'engines' => array('twig', 'php'),
            ),
        )); 

You can now render a PHP template instead of a Twig one simply by using the
``.php`` extension in the template name instead of ``.twig``. The controller
below renders the ``index.html.php`` template::

    // src/Acme/HelloBundle/Controller/HelloController.php

    public function indexAction($name)
    {
        return $this->render('AcmeHelloBundle:Hello:index.html.php', array('name' => $name));
    }

.. index::
  single: Templating; Layout
  single: Layout

Decorating Templates
--------------------

More often than not, templates in a project share common elements, like the
well-known header and footer. In Symfony2, we like to think about this problem
differently: a template can be decorated by another one.

The ``index.html.php`` template is decorated by ``layout.html.php``, thanks to
the ``extend()`` call:

.. code-block:: html+php

    <!-- src/Acme/HelloBundle/Resources/views/Hello/index.html.php -->
    <?php $view->extend('AcmeHelloBundle::layout.html.php') ?>

    Hello <?php echo $name ?>!

The ``AcmeHelloBundle::layout.html.php`` notation sounds familiar, doesn't it? It
is the same notation used to reference a template. The ``::`` part simply
means that the controller element is empty, so the corresponding file is
directly stored under ``views/``.

Now, let's have a look at the ``layout.html.php`` file:

.. code-block:: html+php

    <!-- src/Acme/HelloBundle/Resources/views/layout.html.php -->
    <?php $view->extend('::base.html.php') ?>

    <h1>Hello Application</h1>

    <?php $view['slots']->output('_content') ?>

The layout is itself decorated by another one (``::base.html.php``). Symfony2
supports multiple decoration levels: a layout can itself be decorated by
another one. When the bundle part of the template name is empty, views are
looked for in the ``app/Resources/views/`` directory. This directory store
global views for your entire project:

.. code-block:: html+php

    <!-- app/Resources/views/base.html.php -->
    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php $view['slots']->output('title', 'Hello Application') ?></title>
        </head>
        <body>
            <?php $view['slots']->output('_content') ?>
        </body>
    </html>

For both layouts, the ``$view['slots']->output('_content')`` expression is
replaced by the content of the child template, ``index.html.php`` and
``layout.html.php`` respectively (more on slots in the next section).

As you can see, Symfony2 provides methods on a mysterious ``$view`` object. In
a template, the ``$view`` variable is always available and refers to a special
object that provides a bunch of methods that makes the template engine tick.

.. index::
   single: Templating; Slot
   single: Slot

Working with Slots
------------------

A slot is a snippet of code, defined in a template, and reusable in any layout
decorating the template. In the ``index.html.php`` template, define a
``title`` slot:

.. code-block:: html+php

    <!-- src/Acme/HelloBundle/Resources/views/Hello/index.html.php -->
    <?php $view->extend('AcmeHelloBundle::layout.html.php') ?>

    <?php $view['slots']->set('title', 'Hello World Application') ?>

    Hello <?php echo $name ?>!

The base layout already has the code to output the title in the header:

.. code-block:: html+php

    <!-- app/Resources/views/base.html.php -->
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php $view['slots']->output('title', 'Hello Application') ?></title>
    </head>

The ``output()`` method inserts the content of a slot and optionally takes a
default value if the slot is not defined. And ``_content`` is just a special
slot that contains the rendered child template.

For large slots, there is also an extended syntax:

.. code-block:: html+php

    <?php $view['slots']->start('title') ?>
        Some large amount of HTML
    <?php $view['slots']->stop() ?>

.. index::
   single: Templating; Include

Including other Templates
-------------------------

The best way to share a snippet of template code is to define a template that
can then be included into other templates.

Create a ``hello.html.php`` template:

.. code-block:: html+php

    <!-- src/Acme/HelloBundle/Resources/views/Hello/hello.html.php -->
    Hello <?php echo $name ?>!

And change the ``index.html.php`` template to include it:

.. code-block:: html+php

    <!-- src/Acme/HelloBundle/Resources/views/Hello/index.html.php -->
    <?php $view->extend('AcmeHelloBundle::layout.html.php') ?>

    <?php echo $view->render('AcmeHelloBundle:Hello:hello.html.php', array('name' => $name)) ?>

The ``render()`` method evaluates and returns the content of another template
(this is the exact same method as the one used in the controller).

.. index::
   single: Templating; Embedding Pages

Embedding other Controllers
---------------------------

And what if you want to embed the result of another controller in a template?
That's very useful when working with Ajax, or when the embedded template needs
some variable not available in the main template.

If you create a ``fancy`` action, and want to include it into the
``index.html.php`` template, simply use the following code:

.. code-block:: html+php

    <!-- src/Acme/HelloBundle/Resources/views/Hello/index.html.php -->
    <?php echo $view['actions']->render('AcmeHelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green')) ?>

Here, the ``AcmeHelloBundle:Hello:fancy`` string refers to the ``fancy`` action of the
``Hello`` controller::

    // src/Acme/HelloBundle/Controller/HelloController.php

    class HelloController extends Controller
    {
        public function fancyAction($name, $color)
        {
            // create some object, based on the $color variable
            $object = ...;

            return $this->render('AcmeHelloBundle:Hello:fancy.html.php', array('name' => $name, 'object' => $object));
        }

        // ...
    }

But where is the ``$view['actions']`` array element defined? Like
``$view['slots']``, it's called a template helper, and the next section tells
you more about those.

.. index::
   single: Templating; Helpers

Using Template Helpers
----------------------

The Symfony2 templating system can be easily extended via helpers. Helpers are
PHP objects that provide features useful in a template context. ``actions`` and
``slots`` are two of the built-in Symfony2 helpers.

Creating Links between Pages
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Speaking of web applications, creating links between pages is a must. Instead
of hardcoding URLs in templates, the ``router`` helper knows how to generate
URLs based on the routing configuration. That way, all your URLs can be easily
updated by changing the configuration:

.. code-block:: html+php

    <a href="<?php echo $view['router']->generate('hello', array('name' => 'Thomas')) ?>">
        Greet Thomas!
    </a>

The ``generate()`` method takes the route name and an array of parameters as
arguments. The route name is the main key under which routes are referenced
and the parameters are the values of the placeholders defined in the route
pattern:

.. code-block:: yaml

    # src/Acme/HelloBundle/Resources/config/routing.yml
    hello: # The route name
        pattern:  /hello/{name}
        defaults: { _controller: AcmeHelloBundle:Hello:index }

Using Assets: images, JavaScripts, and stylesheets
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

What would the Internet be without images, JavaScripts, and stylesheets?
Symfony2 provides the ``assets`` tag to deal with them easily:

.. code-block:: html+php

    <link href="<?php echo $view['assets']->getUrl('css/blog.css') ?>" rel="stylesheet" type="text/css" />

    <img src="<?php echo $view['assets']->getUrl('images/logo.png') ?>" />

The ``assets`` helper's main purpose is to make your application more
portable. Thanks to this helper, you can move the application root directory
anywhere under your web root directory without changing anything in your
template's code.

Output Escaping
---------------

When using PHP templates, escape variables whenever they are displayed to the
user::

    <?php echo $view->escape($var) ?>

By default, the ``escape()`` method assumes that the variable is outputted
within an HTML context. The second argument lets you change the context. For
instance, to output something in a JavaScript script, use the ``js`` context::

    <?php echo $view->escape($var, 'js') ?>
