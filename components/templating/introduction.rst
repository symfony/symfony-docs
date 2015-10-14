.. index::
   single: Templating
   single: Components; Templating

The Templating Component
========================

    The Templating component provides all the tools needed to build any kind
    of template system.

    It provides an infrastructure to load template files and optionally
    monitor them for changes. It also provides a concrete template engine
    implementation using PHP with additional tools for escaping and separating
    templates into blocks and layouts.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/templating`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/templating).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The :class:`Symfony\\Component\\Templating\\PhpEngine` class is the entry point
of the component. It needs a
template name parser (:class:`Symfony\\Component\\Templating\\TemplateNameParserInterface`)
to convert a template name to a
template reference (:class:`Symfony\\Component\\Templating\\TemplateReferenceInterface`).
It also needs a template loader (:class:`Symfony\\Component\\Templating\\Loader\\LoaderInterface`)
which uses the template reference to actually find and load the template::

    use Symfony\Component\Templating\PhpEngine;
    use Symfony\Component\Templating\TemplateNameParser;
    use Symfony\Component\Templating\Loader\FilesystemLoader;

    $loader = new FilesystemLoader(__DIR__.'/views/%name%');

    $templating = new PhpEngine(new TemplateNameParser(), $loader);

    echo $templating->render('hello.php', array('firstname' => 'Fabien'));

.. code-block:: html+php

    <!-- views/hello.php -->
    Hello, <?php echo $firstname ?>!

The :method:`Symfony\\Component\\Templating\\PhpEngine::render` method parses
the ``views/hello.php`` file and returns the output text. The second argument
of ``render`` is an array of variables to use in the template. In this
example, the result will be ``Hello, Fabien!``.

.. note::

    Templates will be cached in the memory of the engine. This means that if
    you render the same template multiple times in the same request, the
    template will only be loaded once from the file system.

The ``$view`` Variable
----------------------

In all templates parsed by the ``PhpEngine``, you get access to a mysterious
variable called ``$view``. That variable holds the current ``PhpEngine``
instance. That means you get access to a bunch of methods that make your life
easier.

Including Templates
-------------------

The best way to share a snippet of template code is to create a template that
can then be included by other templates. As the ``$view`` variable is an
instance of ``PhpEngine``, you can use the ``render`` method (which was used
to render the template originally) inside the template to render another template::

    <?php $names = array('Fabien', ...) ?>
    <?php foreach ($names as $name) : ?>
        <?php echo $view->render('hello.php', array('firstname' => $name)) ?>
    <?php endforeach ?>

Global Variables
----------------

Sometimes, you need to set a variable which is available in all templates
rendered by an engine (like the ``$app`` variable when using the Symfony
Framework). These variables can be set by using the
:method:`Symfony\\Component\\Templating\\PhpEngine::addGlobal` method and they
can be accessed in the template as normal variables::

    $templating->addGlobal('ga_tracking', 'UA-xxxxx-x');

In a template:

.. code-block:: html+php

    <p>The google tracking code is: <?php echo $ga_tracking ?></p>

.. caution::

    The global variables cannot be called ``this`` or ``view``, since they are
    already used by the PHP engine.

.. note::

    The global variables can be overridden by a local variable in the template
    with the same name.

Output Escaping
---------------

When you render variables, you should probably escape them so that HTML or
JavaScript code isn't written out to your page. This will prevent things like
XSS attacks. To do this, use the
:method:`Symfony\\Component\\Templating\\PhpEngine::escape` method::

    <?php echo $view->escape($firstname) ?>

By default, the ``escape()`` method assumes that the variable is outputted
within an HTML context. The second argument lets you change the context. For
example, to output something inside JavaScript, use the ``js`` context::

    <?php echo $view->escape($var, 'js') ?>

The component comes with an HTML and JS escaper. You can register your own
escaper using the
:method:`Symfony\\Component\\Templating\\PhpEngine::setEscaper` method::

    $templating->setEscaper('css', function ($value) {
        // ... all CSS escaping

        return $escapedValue;
    });

Helpers
-------

The Templating component can be easily extended via helpers. Helpers are PHP objects that
provide features useful in a template context. The component has
2 built-in helpers:

* :doc:`/components/templating/helpers/assetshelper`
* :doc:`/components/templating/helpers/slotshelper`

Before you can use these helpers, you need to register them using
:method:`Symfony\\Component\\Templating\\PhpEngine::set`::

    use Symfony\Component\Templating\Helper\AssetsHelper;
    // ...

    $templating->set(new AssetsHelper());

Custom Helpers
~~~~~~~~~~~~~~

You can create your own helpers by creating a class which implements
:class:`Symfony\\Component\\Templating\\Helper\\HelperInterface`. However,
most of the time you'll extend
:class:`Symfony\\Component\\Templating\\Helper\\Helper`.

The ``Helper`` has one required method:
:method:`Symfony\\Component\\Templating\\Helper\\HelperInterface::getName`.
This is the name that is used to get the helper from the ``$view`` object.

Creating a Custom Engine
------------------------

Besides providing a PHP templating engine, you can also create your own engine
using the Templating component. To do that, create a new class which
implements the :class:`Symfony\\Component\\Templating\\EngineInterface`. This
requires 3 method:

* :method:`render($name, array $parameters = array()) <Symfony\\Component\\Templating\\EngineInterface::render>`
  - Renders a template
* :method:`exists($name) <Symfony\\Component\\Templating\\EngineInterface::exists>`
  - Checks if the template exists
* :method:`supports($name) <Symfony\\Component\\Templating\\EngineInterface::supports>`
  - Checks if the given template can be handled by this engine.

Using Multiple Engines
----------------------

It is possible to use multiple engines at the same time using the
:class:`Symfony\\Component\\Templating\\DelegatingEngine` class. This class
takes a list of engines and acts just like a normal templating engine. The
only difference is that it delegates the calls to one of the other engines. To
choose which one to use for the template, the
:method:`EngineInterface::supports() <Symfony\\Component\\Templating\\EngineInterface::supports>`
method is used.

.. code-block:: php

    use Acme\Templating\CustomEngine;
    use Symfony\Component\Templating\PhpEngine;
    use Symfony\Component\Templating\DelegatingEngine;

    $templating = new DelegatingEngine(array(
        new PhpEngine(...),
        new CustomEngine(...),
    ));

.. _Packagist: https://packagist.org/packages/symfony/templating
