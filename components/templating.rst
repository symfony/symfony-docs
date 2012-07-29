.. index::
   single: Templating
   single: Components; Templating

The Templating Component
========================

    Templating provides all the tools needed to build any kind of template
    system.

    It provides an infrastructure to load template files and optionally monitor
    them for changes. It also provides a concrete template engine implementation
    using PHP with additional tools for escaping and separating templates into
    blocks and layouts.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Templating);
* Install it via PEAR (`pear.symfony.com/Templating`);
* Install it via Composer (`symfony/templating` on Packagist).

Usage
-----

The :class:`Symfony\\Component\\Templating\\PhpEngine` class is the entry point
of the component. It needs a template name parser
(:class:`Symfony\\Component\\Templating\\TemplateNameParserInterface`) to
convert a template name to a template reference and template loader
(:class:`Symfony\\Component\\Templating\\Loader\\LoaderInterface`) to find the
template associated to a reference::

    use Symfony\Component\Templating\PhpEngine;
    use Symfony\Component\Templating\TemplateNameParser;
    use Symfony\Component\Templating\Loader\FilesystemLoader;

    $loader = new FilesystemLoader(__DIR__ . '/views/%name%');

    $view = new PhpEngine(new TemplateNameParser(), $loader);

    echo $view->render('hello.php', array('firstname' => 'Fabien'));

The :method:`Symfony\\Component\\Templating\\PhpEngine::render` method executes
the file `views/hello.php` and returns the output text.

.. code-block:: html+php

    <!-- views/hello.php -->
    Hello, <?php echo $firstname ?>!

Template Inheritance with Slots
-------------------------------

The template inheritance is designed to share layouts with many templates.

.. code-block:: html+php

    <!-- views/layout.php -->
    <html>
        <head>
            <title><?php $view['slots']->output('title', 'Default title') ?></title>
        </head>
        <body>
            <?php $view['slots']->output('_content') ?>
        </body>
    </html>

The :method:`Symfony\\Component\\Templating\\PhpEngine::extend` method is called in the
sub-template to set its parent template.

.. code-block:: html+php

    <!-- views/page.php -->
    <?php $view->extend('layout.php') ?>

    <?php $view['slots']->set('title', $page->title) ?>

    <h1>
        <?php echo $page->title ?>
    </h1>
    <p>
        <?php echo $page->body ?>
    </p>

To use template inheritance, the :class:`Symfony\\Component\\Templating\\Helper\\SlotsHelper`
helper must be registered::

    use Symfony\Templating\Helper\SlotsHelper;

    $view->set(new SlotsHelper());

    // Retrieve page object
    $page = ...;

    echo $view->render('page.php', array('page' => $page));

.. note::

    Multiple levels of inheritance is possible: a layout can extend an other
    layout.

Output Escaping
---------------

This documentation is still being written.

The Asset Helper
----------------

This documentation is still being written.
